<?php

namespace App\Models\Fcs;

use App\Models\Fcs\FcsCommon;
use App\Models\Fcs\FcsContract;
use App\Models\Fcs\FcsField;
use App\Models\Fcs\FcsFtp;
use App\Models\Fcs\FcsHttp;
use App\Models\Fcs\FcsLoan;
use App\Models\Fcs\FcsQueue;
use App\Models\Fcs\FcsSoap;

class FcsLoan {

    const CHANNEL = 'KeZhan';
    const CODE_KZ_REFUSE = 31;

    /**
     * 申请贷款
     * 
     * 所有抛异常会在外部记录log并重试。<br>
     * 1、查询无贷款：静默结束；<br>
     * 2、查询无人脸识别：记录log，结束；<br>
     * 3、生成合同失败：记录db，抛异常；<br>
     * 4、上传文件失败：抛异常；<br>
     */
    public static function apply($lid) {
        //贷款基本信息
        $fields = 'stu.idcard_name,stu.idcard,stu.idcard_start,stu.idcard_expire,'
                . 'stu.sex,stu.phone,stu.work_type,stu.idcard_person_pic,'
                . 'stu.bank_account_pic,stu.contact1_relation,stu.contact1,'
                . 'stu.contact1_phone,stu.contact2_relation,stu.contact2,'
                . 'stu.contact2_phone,stu.degree,stu.work_desc,stu.home_province,'
                . 'stu.home_city,stu.home_area,stu.home_address,stu.phoneid,'
                . 'stu.bank_id,stu.bank_account,stu.work_name,stu.work_province,'
                . 'stu.work_city,stu.work_area,stu.work_address,stu.position,'
                . 'stu.monthly_income,stu.university,stu.university_address,'
                . 'stu.university_contact,stu.entrance_time,stu.wechat,stu.jd_id,'
                . 'stu.tmall_id,stu.qq,stu.email,stu.uid,stu.course_period,'
                . 'stu.course_open_time,stu.school_pic,stu.lng,stu.lat,stu.house_status,'
                . 'stu.marriage_status,stu.entry_date,stu.work_phone,'
                . 'stu.training_contract,stu.pic_education,stu.idcard_pic,'
                . 'stu.idcard_pic_back,stu.education_system,stu.major,'
                . 'l.money_apply,l.repay_need,l.cid,l.money_school,l.status,l.id,'
                . 'l.sid,l.resource,l.loan_product,l.loan_type,l.no_jrd,l.sbid,l.ip,'
                . 'c.name as course_name,c.tuition,c.course_period,c.course_period_property,'
                . 'c.teach_type,c.busi_type as course_busi_type,'
                . 's.full_name as school_name,s.bank_account_name as school_bank_account_name,'
                . 's.bank_name_detail as school_bank_name,s.bank_account as school_bank_account,'
                . 's.reg_address,s.contact_mobile,s.busi_type,'
                . 'ss.name as branch_school_name,ss.lng as s_lng,ss.lat as s_lat,'
                . 'u.name as nickname,u.add_time';
        $loan = Yii::app()->db->createCommand()
                ->select($fields)
                ->from(ARLoan::TABLE_NAME . ' l')
                ->leftJoin(ARStuAddition::TABLE_NAME . ' stu', 'stu.lid=l.id')
                ->leftJoin(ARPayCourse::TABLE_NAME . ' c', 'c.id=l.cid')
                ->leftJoin(ARSchoolBusi::TABLE_NAME . ' s', 's.sid=l.sbid')
                ->leftJoin(ARSchool::TABLE_NAME . ' ss', 'ss.id=l.sid')
                ->leftJoin(ARKzUser::TABLE_NAME . ' u', 'u.id=l.uid')
                ->where('l.id=:id and l.resource in(' . ARPayLoanType::RESOURCE_FCS . ',' . ARPayLoanType::RESOURCE_FCS_SC . ') '
                        . 'and (l.fcs_loanid="" or l.fcs_loanid is null) ', array(':id' => $lid))
                ->queryRow();
        if (!$loan) {
            //贷款不存在
            return;
        }
        //云慧眼信息
        $auth_fields = 'birthday,result_auth,front_card,back_card,be_idcard,'
                . 'photo_get,photo_grid,photo_living';
        $uid = $loan['uid'];
        $sql2 = 'select ' . $auth_fields . ' '
                . 'from pay_user_auth '
                . 'where uid="' . $uid . '" and result_auth=2 and be_idcard>0.8 '
                . 'order by update_time desc '
                . 'limit 1';
        $auth = Yii::app()->db->createCommand($sql2)->queryRow();
        if (!$auth) {
            //资料不全
            $ex_update = array();
            $ex_update['status'] = LOAN_41_P2P_REFUSE;
            $ex_update['remark'] = '请完成云慧眼认证';
            $ex_update['update_time'] = date('Y-m-d H:i:s');
            Yii::app()->db->createCommand()
                    ->update(ARLoan::TABLE_NAME, $ex_update, 'id=:id and (fcs_loanid="" or fcs_loanid is null)', array(':id' => $lid));
            \Yii::log('lid:' . $lid . '，uid:' . $uid . '，提交失败，缺少云慧眼信息。' . PHP_EOL, 'fcs.op');
            return;
        }
        $data = array_merge($loan, $auth);
        //风险提示信息
        $data['otherRiskInfo'] = '无';
        //历史拒绝次数
        $reject_count = Yii::app()->db->createCommand()
                ->select('count(*) as c')
                ->from(ARLoan::TABLE_NAME)
                ->where('uid = :uid', array(':uid' => $uid))
                ->queryRow();
        $data['historyRejectNum'] = $reject_count['c'] - 1 < 0 ? 0 : $reject_count['c'] - 1;
        try {
            //生成征信授权书
            $data['contract_credit'] = FcsContract::signCredit($lid);
            //获取富登的参数
            $params = FcsField::getApplyParams($data);
        } catch (Exception $ex) {
            $ex_update = array();
            $ex_update['remark'] = $ex->getMessage();
            $ex_update['update_time'] = date('Y-m-d H:i:s');
            \Yii::log('lid:' . $lid . '，uid:' . $uid . '，提交失败：' . $ex->getMessage() . PHP_EOL, 'fcs.op');
            if ($ex->getCode() == self::CODE_KZ_REFUSE) {
                //资料不合规
                $ex_update['status'] = LOAN_31_KZ_REFUSE;
                Yii::app()->db->createCommand()
                        ->update(ARLoan::TABLE_NAME, $ex_update, 'id=:id and (fcs_loanid="" or fcs_loanid is null)', array(':id' => $lid));
                //添加贷款日志
                ARLoanLog::insertLog($data, $ex_update['status'], $ex->getMessage());
                return;
            } else {
                $ex_update['status'] = LOAN_34_KZ_CREDIT_SEND_INFO;
                $ex_update['remark'] = '';
                Yii::app()->db->createCommand()
                        ->update(ARLoan::TABLE_NAME, $ex_update, 'id=:id and (fcs_loanid="" or fcs_loanid is null)', array(':id' => $lid));
                //添加贷款日志
                ARLoanLog::insertLog($data, $ex_update['status'], $ex->getMessage());
                throw $ex;
            }
        }
        $wsdl_name = 'http___www.siebel.com_FCSKeZhanLoanCreatedWS_FCS_KeZhan_Loan_Created_WS.WSDL';
        //多余的参数会在FcsSoap里滤掉
        $result = FcsSoap::call($wsdl_name, 'KZCreateLoan', $params);
        //处理结果
        if (is_array($result) && array_key_exists('code', $result)) {
            $update = array();
            if ($result['code'] == 0) {
                //提交成功
                $update['fcs_loanid'] = $result['loanId'];
                $update['fcs_custid'] = $result['custId'];
                $update['status'] = LOAN_DATA_42_DATA_P2P_SEND;
                $update['remark'] = '';
                $reason = '提交贷款信息至富登成功';
            } elseif ($result['code'] == 3) {
                //提交成功，但是对于富登来说是重复提交
                $update['fcs_loanid'] = $result['loanId'];
                $update['fcs_custid'] = $result['custId'];
                $update['status'] = LOAN_DATA_42_DATA_P2P_SEND;
                $update['remark'] = '';
                $reason = '提交贷款信息至富登成功';
            } elseif ($result['code'] == 2) {
                //提交成功但是被拒，不符合对方风控，再次提交也不会通过
                $update['status'] = LOAN_140_FOREVER_REFUSE;
                $update['remark'] = $result['message'];
                $reason = '提交贷款信息至富登被拒';
            } else {
                //提交成功但是资料有问题,也可能是接口报错,code:1
                if (strpos($result['message'], '方法执行异常') !== false || strpos($result['message'], '发生一个错误') !== false) {
                    //接口问题
                    $ex_update = array();
                    $ex_update['remark'] = '';
                    $ex_update['update_time'] = date('Y-m-d H:i:s');
                    $ex_update['status'] = LOAN_34_KZ_CREDIT_SEND_INFO;
                    Yii::app()->db->createCommand()
                            ->update(ARLoan::TABLE_NAME, $ex_update, 'id=:id and (fcs_loanid="" or fcs_loanid is null)', array(':id' => $lid));
                    //添加贷款日志
                    ARLoanLog::insertLog($data, $ex_update['status'], $result['message']);
                    \Yii::log('lid:' . $lid . '，uid:' . $uid . '，提交失败：' . $result['message'] . PHP_EOL, 'fcs.op');
                    throw new Exception($result['message']);
                } else {
                    //资料问题
                    $update['status'] = LOAN_41_P2P_REFUSE;
                    $update['remark'] = '资金方系统自动拒绝';
                    $reason = '提交贷款信息至富登失败（' . $result['message'] . '）';
                }
            }
            $update['update_time'] = date('Y-m-d H:i:s');
            Yii::app()->db->createCommand()
                    ->update(
                            ARLoan::TABLE_NAME
                            , $update
                            , 'id=:id and (fcs_loanid="" or fcs_loanid is null)'
                            , array(':id' => $lid)
            );
            //添加贷款日志
            ARLoanLog::insertLog($data, $update['status'], $reason);
            //上传通信录，放队列（富登会报错）
            if ($result['code'] == 0 && $update['fcs_loanid']) {
                FcsQueue::pushPhonebook($uid, $result['loanId']);
            }
            return $result['loanId'];
        } else {
            throw new Exception('创建贷款接口返回数据格式异常');
        }
    }

    /**
     * 上传通讯录
     */
    public static function uploadPhoneBook($uid, $loanId) {
        if (!$uid || !$loanId) {
            return;
        }
        $row = Yii::app()->db->createCommand()
                ->select()
                ->from(ARMobile::TABLE_NAME)
                ->where('uid=:uid', array(':uid' => $uid))
                ->queryRow();
        $phone_book = json_decode($row['list'], true);
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
        $params['channel'] = self::CHANNEL;
        $params['loanId'] = $loanId;
        $params['KZPhoneBookInfo'] = array('ListOfPhoneBookInfo' => $sorted_phone_book);
        $wsdl_name = 'http___www.siebel.com_FCSKeZhanLoanPhoneBookSynWS_FCS_KeZhan_Loan_PhoneBookSyn_WS.WSDL';
        $result = FcsSoap::call($wsdl_name, 'KZPhoneBookSyn', $params);
        if (is_array($result) && array_key_exists('code', $result)) {
            if ($result['code'] == 0) {
                //上传成功
            } else {
                //上传失败
            }
        } else {
            throw new Exception('上传通讯录接口返回数据格式异常');
        }
    }

    /**
     * 同步贷款状态至富登
     */
    public static function pushStatus($lid) {
        $loan = Yii::app()->db->createCommand()
                ->select()
                ->from(ARLoan::TABLE_NAME)
                ->where('id=:id and resource in(' . ARPayLoanType::RESOURCE_FCS . ',' . ARPayLoanType::RESOURCE_FCS_SC . ') '
                        . 'and fcs_loanid!="" ', array(':id' => $lid))
                ->queryRow();
        $params = array();
        $params['channel'] = self::CHANNEL;
        $params['loanId'] = $loan['fcs_loanid'];
        $cancelarr = array(
            LOAN_51_SCHOOL_REFUSE,
            LOAN_52_SCHOOL_STOP,
        );
        if ($loan['status'] == LOAN_50_SCHOOL_BEGIN) {
            $params['loanStatue'] = 'confirm';
            try {
                //代扣协议文件
                $params['attachment1'] = FcsContract::signWithhold($lid);
                //贷款合同文件
                $params['attachment2'] = FcsContract::signLoan($lid);
            } catch (Exception $ex) {
                $ex_update = array();
                $ex_update['remark'] = '';
                $ex_update['update_time'] = date('Y-m-d H:i:s');
                Yii::app()->db->createCommand()
                        ->update(ARLoan::TABLE_NAME, $ex_update, 'id=:id', array(':id' => $lid));
                ARLoanLog::insertLog($loan, LOAN_50_SCHOOL_BEGIN, $ex->getMessage());
                throw $ex;
            }
            $file_list = array($params['attachment1'], $params['attachment2']);
            $params['attachment1'] = FcsFtp::getFile($lid, $params['attachment1']);
            $params['attachment2'] = FcsFtp::getFile($lid, $params['attachment2']);
            FcsFtp::upload($lid, $file_list);
        } elseif (in_array($loan['status'], $cancelarr)) {
            $params['loanStatue'] = 'cancel';
        } else {
            //贷款状态不在可操作范围内
            return;
        }
        $wsdl_name = 'http___www.siebel.com_FCSKeZhanLoanUpdatedWS_FCS_KeZhan_Loan_Updated_WS.WSDL';
        $result = FcsSoap::call($wsdl_name, 'KZUpdateLoanStatue', $params);
        if (is_array($result) && array_key_exists('code', $result)) {
            if ($result['code'] == 0) {
                //提交成功
                if ($params['loanStatue'] == 'confirm') {
                    $update = array();
                    $update['status'] = LOAN_DATA_60_NOTICE_MONEY;
                    $update['remark'] = '';
                    Yii::app()->db->createCommand()
                            ->update(ARLoan::TABLE_NAME, $update, 'id=:id', array(':id' => $lid));
                    //添加贷款日志
                    ARLoanLog::insertLog($loan, $update['status'], '同步上课确认给富登成功');
                } elseif ($params['loanStatue'] == 'cancel') {
                    //添加贷款日志
                    ARLoanLog::insertLog($loan, $loan['status'], '同步取消放款给富登成功');
                }
            } else {
                //提交成功但是有问题
                throw new Exception('同步贷款状态失败');
            }
        } else {
            throw new Exception('同步贷款状态接口返回数据格式异常');
        }
    }

    /**
     * 从富登查询贷款状态
     */
    public static function query($lid, $update = false) {
        $loan = Yii::app()->db->createCommand()
                ->select()
                ->from(ARLoan::TABLE_NAME)
                ->where('id=:id', array(':id' => $lid))
                ->queryRow();
        $params = array();
        $params['channel'] = self::CHANNEL;
        $params['loanId'] = $loan['fcs_loanid'];
        $wsdl_name = 'http___www.siebel.com_FCSKeZhanLoanQueryWS_FCS_KeZhan_Loan_Query_WS.WSDL';
        $result = FcsSoap::call($wsdl_name, 'KZQueryLoanStatue', $params);
        if (is_array($result) && array_key_exists('code', $result)) {
            if ($result['code'] == 0) {
                //查询成功
                if ($update) {
                    //目前返回状态和推送不一致
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
                    //self::updateStatus($loan['fcs_loanid'], $result['loanStatue']);
                }
            } else {
                //查询失败
            }
        } else {
            throw new Exception('查询贷款状态接口返回数据格式异常');
        }
        return $result;
    }

    /**
     * 富登贷款状态更新至课栈
     */
    public static function syncStatus($data) {
        //兼容秒，毫秒
        if (strlen($data['time']) > 10) {
            $data['time'] = substr($data['time'], 0, 10);
        }
        if (!$data['fcs_loan_id']) {
            throw new Exception('贷款id不能为空');
        }
        if (!$data['time']) {
            throw new Exception('操作时间不能为空');
        }
        self::updateStatus($data['fcs_loan_id'], $data['status'], $data['time'], $data['remark']);
    }

    /**
     * 更新贷款状态
     * @param type $fcs_loan_id 富登贷款id
     * @param type $status_str 贷款状态描述
     * @param type $time 操作时间
     * @param type $remark remark
     */
    public static function updateStatus($fcs_loan_id, $status_str, $time = null, $remark = '') {
        $update = array();
        if (!$time) {
            $time = time();
        }
        $update['update_time'] = date('Y-m-d H:i:s', $time);
        $loan = self::getKzLoan($fcs_loan_id);
        if ($status_str == 'audit_pass') {
            $update['status'] = LOAN_40_P2P_CONFIRM;
            $reason = '富登审核通过';
        } elseif ($status_str == 'audit_refus') {
            $update['status'] = LOAN_41_P2P_REFUSE;
            $reason = '富登审核拒绝';
        } elseif ($status_str == 'audit_kill') {
            $update['status'] = LOAN_140_FOREVER_REFUSE;
            $reason = '富登审核永久拒绝';
        } elseif ($status_str == 'money_pass') {
            $update['status'] = LOAN_100_REPAY;
            $update['pay_time'] = date('Y-m-d 00:00:00', $time);
            $reason = '富登放款成功';
        } elseif ($status_str == 'money_refuse') {
            $update['status'] = LOAN_101_REFUSE;
            $reason = '富登放款拒绝';
        } else {
            throw new Exception('操作状态不在正确范围内');
        }
        if ($update['status'] == $loan['status']) {
            //重复的通知，忽略
            return;
        } elseif ($loan['status'] >= 100) {
            return;
        }
        $update['remark'] = substr($remark, 0, 1000);
        //更新贷款状态
        Yii::app()->db->createCommand()
                ->update(
                        ARLoan::TABLE_NAME
                        , $update
                        , 'resource in(' . ARPayLoanType::RESOURCE_FCS . ',' . ARPayLoanType::RESOURCE_FCS_SC . ') and fcs_loanid=:loanid'
                        , array(':loanid' => $fcs_loan_id)
        );
        //添加贷款日志
        ARLoanLog::insertLog($loan, $update['status'], $reason);
        if ($status_str == 'money_pass') {
            //生成还款计划表
            FcsCommon::genLoanBill($loan['id']);
            //短信通知
            if (!config('app.env') == 'local') {
                $stu = ARStuAddition::getByLid($loan['id']);
                $phone = $stu['phone'];
                $content = '尊敬的客户，您通过课栈网向富登小额贷#款有限公司'
                        . '申请的' . $loan['money_apply'] . '元培训借#款额*度，'
                        . '已经成功发放至培训方。'
                        . '详情请访问www.kezhanwang.cn或下载课栈app。';
                try {
                    SmsUtil::send($phone, $content);
                } catch (Exception $ex) {
                    $log = '发送放款成功短信失败，lid：' . $loan['id'] . '('
                            . $ex->getMessage() . ')' . PHP_EOL;
                    \Yii::log($log, 'fcs.op');
                }
            }
        }
    }

    /**
     * 获取合同
     */
    public static function getContract($data, $loan = array()) {
        if (!$data['fcs_loan_id'] && !$loan['id']) {
            throw new Exception('贷款id不能为空');
        }
        if (empty($data['filelist'])) {
            throw new Exception('合同文件列表不能为空');
        }
        if (!$loan['id']) {
            $loan = self::getKzLoan($data['fcs_loan_id']);
        }
        $file_list = array();
        //合同地址
        $filePath = PATH_AUDIT . '/upload/kz_student/FCS/' . (floor($loan['id'] / 10000)) . '/' . $loan['id'];
        if (!file_exists($filePath)) {
            mkdir($filePath, 0755, true);
        }
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
    }

    /**
     * 根据富登订单号查询课栈贷款信息
     */
    public static function getKzLoan($loanId) {
        $loan = Yii::app()->db->createCommand()
                ->select()
                ->from(ARLoan::TABLE_NAME)
                ->where('fcs_loanid=:fcs_loanid', array(':fcs_loanid' => $loanId))
                ->queryRow();
        if (!$loan && !config('app.env') == 'local') {
            throw new Exception('没有查到对应的贷款');
        }
        return $loan;
    }

    /**
     * 生成贷款合同编号<br>
     * 1、如果redis挂了不能保证高并发时编号唯一；<br>
     * 2、如果redis挂了或被清，同时最大单号放款被拒，转资金方后单号被新资金方覆盖，
     * 编号不唯一，但有效的编号唯一；
     * @param int $lid 课栈订单id
     */
    public static function getContractNo($lid) {
        //检查是否已有合同编号
        $loan = Yii::app()->db->createCommand()
                ->select('contract_order,resource')
                ->from(ARLoan::TABLE_NAME)
                ->where('id=:id', array(':id' => $lid))
                ->queryRow();
        //四川：川;重庆：渝
        if ($loan['resource'] == ARPayLoanType::RESOURCE_FCS) {
            $no = '富登渝资服';
        } elseif ($loan['resource'] == ARPayLoanType::RESOURCE_FCS_SC) {
            $no = '富登川资服';
        } else {
            return '';
        }
        if ($loan['contract_order'] && strpos($loan['contract_order'], $no) !== false) {
            return $loan['contract_order'];
        }
        //避免查询到其他渠道编号
        $row = Yii::app()->db->createCommand()
                ->select('contract_order')
                ->from(ARLoan::TABLE_NAME)
                ->where('contract_order like "' . $no . '%"')
                ->order('contract_order desc')
                ->limit(1)
                ->queryRow();
        if ($row['contract_order']) {
            $i_db = substr($row['contract_order'], strpos($row['contract_order'], '03') + 2, 7);
            $i_db++;
        } else {
            $i_db = 1;
        }
        try {
            //不抛异常
            $redis = RedisUtil::getInstance();
        } catch (Exception $ex) {
            
        }
        if ($redis) {
            $key = 'FCS_CONTRACT_NO_' . $loan['resource'];
            $i_r = $redis->incr($key);
            if ($i_r >= $i_db) {
                $i = $i_r;
            } else {
                $i = $i_db;
                $redis->set($key, $i);
            }
        } else {
            $i = $i_db;
        }
        $index = str_pad($i, 7, '0', STR_PAD_LEFT);
        //合作方编号，课栈：03
        $no .= '（' . date('Y') . '）字第03' . $index . '号';
        $update = array();
        $update['contract_order'] = $no;
        $update['update_time'] = date('Y-m-d H:i:s');
        Yii::app()->db->createCommand()
                ->update(ARLoan::TABLE_NAME, $update, 'id=:id', array(':id' => $lid));
        return $no;
    }

    /**
     * 计算逾期天数
     */
//    public static function getOverdueDays($bill_date) {
//        $repayday_time = strtotime($bill_date . '15');
//        $overdue_days = floor((time() - $repayday_time) / 86400);
//        return $overdue_days > 0 ? $overdue_days : 0;
//    }
}
