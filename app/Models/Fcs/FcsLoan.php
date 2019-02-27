<?php

namespace App\Models\Fcs;

use App\Components\AliyunOSSUtil;
use App\Models\ActiveRecord\ARPfUsers;
use App\Models\ActiveRecord\ARPFUsersPhonebook;
use App\Models\Message\MsgSMS;
use Illuminate\Support\Facades\Redis;

class FcsLoan {


    /**
     * 申请贷款
     *
     * 所有抛异常会在外部记录log并重试。<br>
     * 1、查询无贷款：静默结束；<br>
     * 2、资料不合规：记录log，结束；<br>
     * 3、生成合同失败：记录db，抛异常；<br>
     * 4、上传文件失败：抛异常；<br>
     */
    public static function apply($lid) {
        //申请贷款基本信息
        $data_raw = FcsDB::getApplyData($lid);
        if (!$data_raw) {
            //贷款不存在
            return false;
        }
        try {
            $data = FcsField::checkApplyParams($data_raw);
        } catch (\Exception $ex) {
            //资料不符合要求
            $ex_update = array();
            $ex_update['status'] = LOAN_3100_PF_REFUSE;
            $ex_update['audit_opinion'] = $ex->getMessage();
            FcsDB::updateLoan($lid, $ex_update, $data_raw, '不符合进件要求');
            return false;
        }
        try {
            //生成征信授权书
            $data['contract_credit'] = FcsFtp::upload($lid, FcsContract::signCredit($lid));
            //获取富登的参数
            $params = FcsField::getApplyParams($data);
        } catch (\Exception $ex) {
            $ex_update = array();
            $ex_update['audit_opinion'] = $ex->getMessage();
            $ex_update['status'] = LOAN_3400_PF_CREDIT_SEND_INFO;
            FcsDB::updateLoan($lid, $ex_update, $data, '');
            throw $ex;
        }
        $wsdl_name = 'http___www.siebel.com_FCSKeZhanLoanCreatedWS_FCS_KeZhan_Loan_Created_WS.WSDL';
        //多余的参数会在FcsSoap里滤掉
        $result = FcsSoap::call($wsdl_name, 'KZCreateLoan', $params, $lid);
        //处理结果
        if (is_array($result) && array_key_exists('code', $result)) {
            $update = array();
            if ($result['code'] == 0 && $result['loanId']) {
                //提交成功
                $update['resource_loan_id'] = $result['loanId'];
                $update['status'] = LOAN_4200_DATA_P2P_SEND;
                $update['audit_opinion'] = '';
                $reason = '提交贷款信息至富登成功';
            } elseif ($result['code'] == 3 && $result['loanId']) {
                //提交成功，但是对于富登来说是重复提交
                $update['resource_loan_id'] = $result['loanId'];
                $update['status'] = LOAN_4200_DATA_P2P_SEND;
                $update['audit_opinion'] = '';
                $reason = '提交贷款信息至富登成功';
            } elseif ($result['code'] == 2) {
                //提交成功但是被拒，不符合对方风控，再次提交也不会通过
                $update['status'] = LOAN_14000_FOREVER_REFUSE;
                $update['audit_opinion'] = $result['message'];
                $reason = '提交贷款信息至富登被拒';
            } else {
                //提交成功但是资料有问题,也可能是接口报错,code:1
                $update['status'] = LOAN_4100_P2P_REFUSE;
                $update['audit_opinion'] = '资金方系统自动拒绝';
                $reason = '提交贷款信息至富登失败（' . $result['message'] . '）';
            }
            FcsDB::updateLoan($lid, $update, $data, $reason);
            //上传通信录，放队列（富登会报错）
            if ($result['loanId']) {
                FcsQueue::pushPhonebook($data['uid'], $result['loanId']);
            }
            return $result['loanId'];
        } else {
            throw new \Exception('创建贷款接口返回数据格式异常');
        }
    }

    /**
     * 上传通讯录
     */
    public static function uploadPhoneBook($uid, $loan_id) {
        if (!$uid || !$loan_id) {
            return;
        }
        $row = ARPFUsersPhonebook::getPhoneBookLastOneByUid($uid);
        $phone_book = json_decode($row['phonebook'], true);
        $phone_book_sort = array(
            'lastname', 'firstname', 'email', 'mobile1',
            'mobile2', 'mobile3', 'YLZD01', 'YLZD02',
            'YLZD03', 'YLZD04', 'YLZD05'
        );
        $sorted_phone_book = array();
        foreach ($phone_book as $item) {
            $new_item = array();
            foreach ($phone_book_sort as $k) {
                if (array_key_exists($k, $item)) {
                    $new_item[$k] = $item[$k];
                } else {
                    $new_item[$k] = '';
                }
            }
            $sorted_phone_book[] = $new_item;
        }
        $params = array();
        $params['channel'] = config('fcs.channel');
        $params['loanId'] = $loan_id;
        $params['KZPhoneBookInfo'] = array('ListOfPhoneBookInfo' => $sorted_phone_book);
        $wsdl_name = 'http___www.siebel.com_FCSKeZhanLoanPhoneBookSynWS_FCS_KeZhan_Loan_PhoneBookSyn_WS.WSDL';
        $result = FcsSoap::call($wsdl_name, 'KZPhoneBookSyn', $params);
        if (is_array($result) && array_key_exists('code', $result)) {
//            if ($result['code'] == 0) {
//                //上传成功
//            } else {
//                //上传失败
//            }
        } else {
            throw new \Exception('上传通讯录接口返回数据格式异常');
        }
    }

    /**
     * 同步贷款状态至富登
     */
    public static function pushStatus($lid) {
        $loan = FcsDB::getLoanByLid($lid);
        $params = array();
        $params['channel'] = config('fcs.channel');
        $params['loanId'] = $loan['resource_loan_id'];
        $cancelarr = array(
            LOAN_5100_SCHOOL_REFUSE,
            LOAN_5200_SCHOOL_STOP,
        );
        if ($loan['status'] == LOAN_5000_SCHOOL_BEGIN) {
            $params['loanStatue'] = 'confirm';
            try {
                //代扣协议文件
                $params['attachment1'] = FcsContract::signWithhold($lid);
                //贷款合同文件
                $params['attachment2'] = FcsContract::signLoan($lid);
            } catch (\Exception $ex) {
                $ex_update = array();
                $ex_update['audit_opinion'] = '';
                FcsDB::updateLoan($lid, $ex_update, $loan, $ex->getMessage());
                throw $ex;
            }
            $params['attachment1'] = FcsFtp::upload($lid, $params['attachment1']);
            $params['attachment2'] = FcsFtp::upload($lid, $params['attachment2']);
        } elseif (in_array($loan['status'], $cancelarr)) {
            $params['loanStatue'] = 'cancel';
        } else {
            //贷款状态不在可操作范围内
            return;
        }
        $wsdl_name = 'http___www.siebel.com_FCSKeZhanLoanUpdatedWS_FCS_KeZhan_Loan_Updated_WS.WSDL';
        $result = FcsSoap::call($wsdl_name, 'KZUpdateLoanStatue', $params, $lid);
        if (is_array($result) && array_key_exists('code', $result)) {
            if ($result['code'] == 0) {
                //提交成功
                $update = array();
                $update['audit_opinion'] = '';
                if ($params['loanStatue'] == 'confirm') {
                    $update['status'] = LOAN_6000_NOTICE_MONEY;
                    $reason = '同步上课确认给富登成功';
                } elseif ($params['loanStatue'] == 'cancel') {
                    $reason = '同步取消放款给富登成功';
                }
                FcsDB::updateLoan($lid, $update, $loan, $reason);
            } else {
                //提交成功但是有问题
                throw new \Exception('同步贷款状态失败');
            }
        } else {
            throw new \Exception('同步贷款状态接口返回数据格式异常');
        }
    }

    /**
     * 从富登查询贷款状态
     */
    public static function query($lid, $update = false) {
        $loan = FcsDB::getLoanByLid($lid);
        $params = array();
        $params['channel'] = config('fcs.channel');
        $params['loanId'] = $loan['resource_loan_id'];
        $wsdl_name = 'http___www.siebel.com_FCSKeZhanLoanQueryWS_FCS_KeZhan_Loan_Query_WS.WSDL';
        $result = FcsSoap::call($wsdl_name, 'KZQueryLoanStatue', $params, $lid);
//        if (is_array($result) && array_key_exists('code', $result)) {
//            if ($result['code'] == 0) {
//                //查询成功
//                if ($update) {
//                    //目前返回状态和推送不一致
//                    $fcs_kz_code = array(
//                        'RO提交' => 42,
//                        'BPO提交' => 42,
//                        '审批中' => 42,
//                        '审批未通过' => 41,
//                        '预筛选拒绝' => 41,
//                        '审批拒绝' => 41,
//                        'TV审核通过' => 40,
//                        '审批通过' => 60,
//                        '正常' => 100,
//                        '逾期' => 111,
//                        '全部提前结清' => 120,//130
//                    );
//                    //self::updateStatus($loan['fcs_loanid'], $result['loanStatue']);
//                }
//            } else {
//                //查询失败
//            }
//        } else {
//            throw new \Exception('查询贷款状态接口返回数据格式异常');
//        }
        return $result;
    }

    /**
     * 富登贷款状态更新至课栈
     */
    public static function pullStatus($data) {
        //兼容秒，毫秒
        if (strlen($data['time']) > 10) {
            $data['time'] = substr($data['time'], 0, 10);
        }
        if (!$data['fcs_loan_id']) {
            throw new \Exception('贷款id不能为空');
        }
        if (!$data['time']) {
            throw new \Exception('操作时间不能为空');
        }
        $lid = self::updateStatus($data['fcs_loan_id'], $data['status'], $data['time'], $data['remark']);
        return $lid;
    }

    /**
     * 更新贷款状态
     */
    public static function updateStatus($fcs_loan_id, $status_str, $time = null, $remark = '') {
        $update = array();
        if (!$time) {
            $time = time();
        }
        $loan = FcsDB::getLoanByFcsLoanId($fcs_loan_id);
        if ($status_str == 'audit_pass') {
            $update['status'] = LOAN_4000_P2P_CONFIRM;
            $reason = '富登审核通过';
        } elseif ($status_str == 'audit_refus') {
            $update['status'] = LOAN_4100_P2P_REFUSE;
            $reason = '富登审核拒绝';
        } elseif ($status_str == 'audit_kill') {
            $update['status'] = LOAN_14000_FOREVER_REFUSE;
            $reason = '富登审核永久拒绝';
        } elseif ($status_str == 'money_pass') {
            $update['status'] = LOAN_10000_REPAY;
            $update['loan_time'] = date('Y-m-d 00:00:00', $time);
            $reason = '富登放款成功';
        } elseif ($status_str == 'money_refuse') {
            $update['status'] = LOAN_10100_REFUSE;
            $reason = '富登放款拒绝';
        } else {
            throw new \Exception('操作状态不在正确范围内');
        }
        if ($update['status'] == $loan['status']) {
            //重复的通知，忽略
            return $loan['id'];
        } elseif ($loan['status'] >= LOAN_10000_REPAY) {
            //推送可能是已经过时的状态
            return $loan['id'];
        }
        //更新贷款状态
        $update['audit_opinion'] = $remark;
        FcsDB::updateLoan($loan['id'], $update, $loan, $reason);
        if ($status_str == 'money_pass') {
            //生成还款计划表
            FcsUtil::genLoanBill($loan['id']);
            //短信通知
            if (!config('app.env') == 'local') {
                $user = ARPfUsers::getUserInfoByID($loan['uid']);
                $content = '尊敬的客户，您通过课栈网向富登小额贷#款有限公司'
                    . '申请的' . $loan['borrow_money'] . '元培训借#款额*度，'
                    . '已经成功发放至培训方。'
                    . '详情请访问http://www.powerfulfin.com或下载大圣分期app。';
                try {
                    MsgSMS::sendSMS($user['phone'], $content);
                } catch (\Exception $ex) {
                    $log = '发送放款成功短信失败，lid：' . $loan['id'] . '('
                        . $ex->getMessage() . ')' . PHP_EOL;
                    FcsUtil::log($log);
                }
            }
        }
        return $loan['id'];
    }

    /**
     * 获取合同
     */
    public static function getContract($data, $loan = array()) {
        if (!$data['fcs_loan_id'] && !$loan['id']) {
            throw new \Exception('贷款id不能为空');
        }
        if (empty($data['filelist'])) {
            throw new \Exception('合同文件列表不能为空');
        }
        if (!$loan['id']) {
            $loan = FcsDB::getLoanByFcsLoanId($data['fcs_loan_id']);
        }
        $file_list = array();
        //合同地址
        $filePath = FcsFtp::getLocalFileDir($loan['id']);
        foreach ($data['filelist'] as $item) {
            $arr = array();
            $arr[0] = $filePath . '/signed_loan_contract.' . pathinfo($item['file'], PATHINFO_EXTENSION);
            $arr[1] = $item['file'];
            $file_list[] = $arr;
            //目前只有一种合同回传：签章后的借款合同
            //所以不判断类型
            break;
        }
        FcsFtp::download($file_list);
        foreach ($file_list as $arr) {
            AliyunOSSUtil::uploadLoanContract($loan['resource'], $loan['hid'], $loan['id'], $arr[0]);
        }
        return $loan['id'];
    }


    /**
     * 生成贷款合同编号,如果redis挂了不能保证高并发时编号唯一
     */
    public static function getContractNo($lid) {
        //检查是否已有合同编号
        $loan = FcsDB::getLoanByLid($lid);
        //四川：川;重庆：渝
        if ($loan['resource'] == RESOURCE_FCS) {
            $no = '富登渝资服';
        } elseif ($loan['resource'] == RESOURCE_FCS_SC) {
            $no = '富登川资服';
        } else {
            return '';
        }
        if ($loan['resource_contract_id'] && strpos($loan['resource_contract_id'], $no) !== false) {
            return $loan['resource_contract_id'];
        }
        //取数据库中最大的号码
        $i_db = FcsDB::getLargestContractNo();
        $i_db++;
        try {
            $key = 'PF_FCS_CONTRACT_NO_' . $loan['resource'];
            $i_r = Redis::incr($key);
            if ($i_r >= $i_db) {
                $i = $i_r;
            } else {
                $i = $i_db;
                Redis::set($key, $i);
            }
        } catch (\Exception $ex) {
            $i = $i_db;
        }
        $index = str_pad($i, 7, '0', STR_PAD_LEFT);
        $no .= '（' . date('Y') . '）字第' . config('fcs.partner_no') . $index . '号';
        $update = array();
        $update['resource_contract_id'] = $no;
        FcsDB::updateLoan($lid, $update);
        return $no;
    }

}
