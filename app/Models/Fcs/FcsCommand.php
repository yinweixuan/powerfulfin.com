<?php

/**
 * 富登相关命令
 */

namespace App\Models\Fcs;

use App\Models\ActiveRecord\ARPFLoan;
use App\Models\Message\MsgSMS;
use Illuminate\Console\Command;

class FcsCommand extends Command {

    protected $signature = 'fcs {args?*}';
    protected $description = 'Commands for FCS';

    public function runcmd($args) {
        if ($args[0] == 'apply') {
            //申请贷款（自动）
            $this->runPerSec('apply');
        } elseif ($args[0] == 'updaterepay') {
            //更新还款计划表（自动）
            $this->runPerSec('updateRepayment');
        } elseif ($args[0] == 'update') {
            //同步贷款状态至富登（自动）
            $this->runPerSec('updateLoan');
        } elseif ($args[0] == 'phonebook') {
            //上传通信录（自动）
            $this->runPerSec('uploadPhonebook');
        } elseif ($args[0] == 'nightaudit') {
            //富登夜间审核结果轮询（自动）,参数1：开始时间，参数2：结束时间。格式：时分（例9:30应填0930）
            $this->nightAudit($args[1], $args[2]);
        } elseif ($args[0] == 'createbill') {
            //生成还款计划（定时）
            $this->createLoanBill();
        } elseif ($args[0] == 'getrepay') {
            //获取当前账期未完成还款，推送到更新还款信息队列（定时）
            $this->getRepayment();
        } elseif ($args[0] == 'push') {
            //人工推送n单到各自的队列，参数1：lids
            $this->batchOP($args[1], 'loanOp', 'push');
        } elseif ($args[0] == 'do') {
            //人工推送n单，并立即执行相应的方法，参数1：lids
            $this->batchOP($args[1], 'loanOp', 'exec');
        } elseif ($args[0] == 'pushphonebook') {
            //人工推送n单上传通讯录到队列，参数1：lids
            $this->batchOP($args[1], 'pushPhonebook');
        } elseif ($args[0] == 'query') {
            //查询订单状态，参数1：lids，参数2：是否更新（默认否）（目前不支持更新）
            $this->batchOP($args[1], 'query', $args[2]);
        } elseif ($args[0] == 'keepalive') {
            //测试，保持连接（自动）
            $this->keepAlive($args);
        } elseif ($args[0] == 'q') {
            //退出
            exit('--exit--' . PHP_EOL);
        } else {
            return 'nomatch';
        }
        return 'done';
    }

    public function getManual() {
        $this_file = file_get_contents(__FILE__);
        $start = strpos($this_file, 'public function runcmd($args)') + 30;
        $end = strpos($this_file, 'return \'nomatch\';');
        $str_arr = explode(PHP_EOL, str_replace(' ', '', substr($this_file, $start, $end - $start)));
        $manual = PHP_EOL . '使用说明：';
        foreach ($str_arr as $str) {
            if (strpos($str, '$args[0]==') !== false) {
                $start1 = strpos($str, '\'') + 1;
                $end1 = strrpos($str, '\'');
                $manual .= PHP_EOL . PHP_EOL . '    ' . substr($str, $start1, $end1 - $start1);
            } elseif (strpos($str, '//') !== false) {
                $manual .= ' ' . $str;
            }
        }
        $manual .= PHP_EOL . PHP_EOL;
        return $manual;
    }

    public function handle() {
        set_time_limit(0);
        $arguments = $this->arguments();
        $args = $arguments['args'];
        $result = $this->runcmd($args);
        if ($result == 'nomatch') {
            echo $this->getManual();
            do {
                $input = $this->ask('Please enter your command');
                $args = explode(' ', $input);
                $result = $this->runcmd($args);
            } while ($result == 'nomatch');
        }
    }

    public function keepAlive($args = array()) {
        $params = array();
        $params['channel'] = config('fcs.channel');
        $params['loanId'] = '1-60H8GA';
        $wsdl_name = 'http___www.siebel.com_FCSKeZhanLoanQueryWS_FCS_KeZhan_Loan_Query_WS.WSDL';
        $func = 'KZQueryLoanStatue';
        if (ENV_DEBUG) {
            //开发环境
            $wsdl = dirname(__FILE__) . '/wsdl_dev/' . $wsdl_name;
        } else {
            //正式环境
            $wsdl = dirname(__FILE__) . '/wsdl_prod/' . $wsdl_name;
        }
        try {
            $xmlobj = simplexml_load_string(file_get_contents($wsdl));
            $xmlarr = json_decode(json_encode($xmlobj), true);
            $sorted_params = array();
            foreach ($xmlarr['message'][0]['part'] as $v) {
                $k = $v['@attributes']['name'];
                if (array_key_exists($k, $params)) {
                    $sorted_params[$k] = $params[$k];
                } else {
                    $sorted_params[$k] = '--';
                }
            }
            $client = new \SOAPClient($wsdl, array());
            $r = $client->__soapCall($func, $sorted_params);
            if ($r) {
                FcsUtil::log('keepalive:' . date('Y-m-d H:i:s') . PHP_EOL);
            }
            echo 0;
            exit(0);
        } catch (\Exception $sf) {
            $error = $sf->getMessage();
            if (count($args) > 1) {
                //有参数，则进行报警
                MsgSMS::sendSMS('13426386028', "vpn异常：{$error}");
            }
            echo 1;
            exit(1);
        }
    }

    public function runPerSec($func) {
        set_time_limit(0);
        $t1 = time();
//        $pause_start = strtotime('2018-05-11 00:00:00');
//        $pause_end = strtotime('2018-05-11 01:00:00');
//        //'apply','updateLoan'
//        $pause_funcs = array('apply', 'updateLoan');
//        if (in_array($func, $pause_funcs) && $t1 > $pause_start && $t1 < $pause_end) {
//            return;
//        }
        do {
            $this->$func();
            $t2 = time();
            if ($t2 - $t1 > 56) {
                return;
            }
            sleep(2);
        } while ($t2 - $t1 < 57);
        return;
    }

    public function getInputArr($input, $sanitize_int = true) {
        if (!is_array($input)) {
            $input_str = str_replace(array('，', ' ', '+', '-'), array(',', '', '', ''), $input);
            $input_arr = explode(',', $input_str);
        } else {
            $input_arr = $input;
        }
        if ($sanitize_int) {
            $input_arr = filter_var_array($input_arr, FILTER_SANITIZE_NUMBER_INT);
        }
        $result_arr = array_unique(array_filter($input_arr));
        return array_values($result_arr);
    }

    public function batchOP($lids, $func, $args = null) {
        $lid_arr = $this->getInputArr($lids);
        foreach ($lid_arr as $lid) {
            try {
                $loan = ARPFLoan::getLoanById($lid);
                if (!in_array($loan['resource'], [RESOURCE_FCS, RESOURCE_FCS_SC])) {
                    echo 'do nothing id:' . $lid . PHP_EOL;
                    continue;
                }
                $this->$func($loan, $args);
            } catch (\Exception $ex) {
                echo $ex->getMessage() . PHP_EOL;
            }
        }
        echo 'all done' . PHP_EOL;
    }

    public function loanOp($loan, $op) {
        $lid = $loan['id'];
        if (!$loan['resource_loan_id']) {
            echo 'apply loan id:' . $lid . PHP_EOL;
            if ($op == 'exec') {
                $fcs_loanid = FcsLoan::apply($lid);
                echo 'done:' . $lid . '->' . $fcs_loanid . PHP_EOL;
            } else {
                FcsQueue::pushApply($lid);
            }
        } elseif (in_array($loan['status'], [LOAN_5000_SCHOOL_BEGIN, LOAN_5100_SCHOOL_REFUSE, LOAN_5200_SCHOOL_STOP])) {
            echo 'push loan status to fcs id:' . $lid . PHP_EOL;
            if ($op == 'exec') {
                FcsLoan::pushStatus($lid);
                echo 'done' . PHP_EOL;
            } else {
                FcsQueue::pushUpdate($lid);
            }
        } elseif (in_array($loan['status'], [LOAN_10000_REPAY, LOAN_11100_OVERDUE])) {
            echo 'update repayment id:' . $lid . PHP_EOL;
            if ($op == 'exec') {
                FcsRepayment::updateRepaymentSchedule($lid);
                echo 'done' . PHP_EOL;
            } else {
                FcsQueue::pushRepay($lid);
            }
        } else {
            echo 'do nothing id:' . $lid . PHP_EOL;
        }
    }

    /**
     * 上传通讯录（推送至队列）
     */
    public function pushPhonebook($loan) {
        FcsQueue::pushPhonebook($loan['uid'], $loan['fcs_loanid']);
        echo 'lid : ' . $loan['id'] . ' : done' . PHP_EOL;
    }

    /**
     * 申请贷款
     */
    public function apply() {
        $queue_data = FcsQueue::pullApply();
        $lid = $queue_data['data'];
        if ($lid) {
            try {
                FcsLoan::apply($lid);
            } catch (\Exception $ex) {
                $log = $lid . '_apply_error:' . $ex->getMessage() . PHP_EOL;
                FcsUtil::log($log);
                $queue_data['error_msg'][] = $ex->getMessage();
                FcsQueue::pushBack($queue_data);
            }
        }
    }

    /**
     * 同步还款计划明细，若未生成则生成还款计划
     */
    public function createLoanBill() {
        $loan_list = FcsDB::getNewLoanList();
        foreach ($loan_list as $loan) {
            try {
                FcsRepayment::updateRepaymentSchedule($loan['id']);
            } catch (\Exception $ex) {
                $log = $loan['id'] . '_' . $loan['resource_loan_id'] . '_createLoanBill_error:' . $ex->getMessage() . PHP_EOL;
                FcsUtil::log($log);
            }
        }
    }

    /**
     * 获取待更新还款计划表的贷款
     */
    public function getRepayment() {
        $repay_list = FcsDB::getUpdateBillList();
        foreach ($repay_list as $item) {
            FcsQueue::pushRepay($item['id']);
        }
    }

    /**
     * 更新贷款计划表
     */
    public function updateRepayment() {
        $queue_data = FcsQueue::pullRepay();
        $data = $queue_data['data'];
        if ($data['lid']) {
            try {
                FcsRepayment::updateRepaymentSchedule($data['lid']);
            } catch (\Exception $ex) {
                $log = $data['lid'] . '_updaterepayment_error:' . $ex->getMessage() . PHP_EOL;
                FcsUtil::log($log);
            }
        }
    }

    /**
     * 同步贷款状态至富登
     */
    public function updateLoan() {
        $queue_data = FcsQueue::pullUpdate();
        $lid = $queue_data['data'];
        if ($lid) {
            try {
                FcsLoan::pushStatus($lid);
            } catch (\Exception $ex) {
                $log = $lid . '_updateloan_error:' . $ex->getMessage() . PHP_EOL;
                FcsUtil::log($log);
                $queue_data['error_msg'][] = $ex->getMessage();
                FcsQueue::pushBack($queue_data);
            }
        }
    }

    /**
     * 上传通信录
     */
    public function uploadPhonebook() {
        $queue_data = FcsQueue::pullPhonebook();
        $data = $queue_data['data'];
        if ($data['uid']) {
            try {
                FcsLoan::uploadPhoneBook($data['uid'], $data['loanId']);
            } catch (\Exception $ex) {
                $log = $data['loanId'] . '_uploadPhonebook_error:' . $ex->getMessage() . PHP_EOL;
                FcsUtil::log($log);
                $queue_data['error_msg'][] = $ex->getMessage();
                FcsQueue::pushBack($queue_data);
            }
        }
    }

    /**
     * 查询订单状态
     */
    public function query($loan, $update = false) {
        $r = FcsLoan::query($loan['id'], $update);
        print_r($r);
    }

    /**
     * 夜间自动审核结果查询
     */
    public function nightAudit($begin_time = null, $end_time = null) {
        if ($begin_time && $end_time) {
            $now = date('Hi');
            if ($begin_time > $end_time && $now < $begin_time && $now > $end_time) {
                return;
            }
            if ($begin_time <= $end_time && !($now >= $begin_time && $now <= $end_time)) {
                return;
            }
        }
        $list = FcsDB::getNightAuditList();
        foreach ($list as $item) {
            if ($item['fcs_loanid']) {
                $result = FcsLoan::query($item['id'], false);
                if ($result['DecisionReason'] == '信用评估夜间自动通过') {
                    FcsDB::updateLoan($item['id'], ['status' => LOAN_4400_P2P_AUTO_PASS], $item, '信用评估夜间自动通过');
                }
            }
        }
    }

}
