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

class FcsCommon {

    const THE_LID = 206697;

    /**
     * 计算是否可以主动还款
     */
    public static function canRepayBySelf($loan) {
        return false;
    }

    /**
     * 获取合同
     */
    public static function getContract($lid, $contract_type) {
        
    }

    /**
     * 提前还款计算
     */
    public static function prepayment($lid, $timestamp) {
        $data = self::cancelLoan($lid, 2, $timestamp);
        return $data;
    }

    /**
     * 退课计算
     */
    public static function withdrawal($lid, $timestamp) {
        $data = self::cancelLoan($lid, 1, $timestamp);
        return $data;
    }

    /**
     * 退课，提前还款
     * @param int $type 1：退课；2：提前还款
     */
    public static function cancelLoan($lid, $type, $timestamp) {
        if ($timestamp) {
            $time = $timestamp + 1;
        } else {
            $time = time();
        }
        $loan = Yii::app()->db->createCommand()
                ->select()
                ->from(ARLoan::TABLE_NAME)
                ->where('resource in (5,8) and status in (100,111) and id=:id', array(':id' => $lid))
                ->queryRow();
        if (!$loan) {
            throw new Exception('不满足计算条件（富登）');
        }
        //判断贷款类型
        if (strpos($loan['loan_product'], 'KZTAN') !== false) {
            $loan_type = 'kztan';
        } elseif (strpos($loan['loan_product'], 'KZTX') !== false) {
            $loan_type = 'kztx';
        } elseif (strpos($loan['loan_product'], 'KZDE') !== false) {
            $loan_type = 'kzde';
        }
        //计算下一期的bill_date
        if (date('j', $time) > 15) {
            $bill_date = date('Ym', $time + 86400 * 20);
        } else {
            $bill_date = date('Ym', $time);
        }
        $bill = Yii::app()->db->createCommand()
                ->select()
                ->from(ARPayLoanBill::TABLE_NAME)
                ->where('status in (0,2) and lid=:lid', array(':lid' => $lid))
                ->queryAll();
        $data = array();
        $data['principal_left'] = 0;
        $data['miss_principal'] = 0;
        $data['miss_interest'] = 0;
        $data['fine_interest'] = 0;
        $data['overdue_fees'] = 0;
        $data['next_payment'] = 0;
        foreach ($bill as $row) {
            if ($row['bill_date'] > $bill_date) {
                $data['principal_left'] += $row['miss_principal'];
            } elseif ($row['bill_date'] == $bill_date) {
                $data['next_payment_principal'] = $row['miss_principal'];
                $data['next_payment_interest'] = $row['miss_interest'];
            } else {
                $data['miss_principal'] += $row['miss_principal'];
                $data['miss_interest'] += $row['miss_interest'];
                if ($row['status'] == ARPayLoanBill::STATUS_OVERDUE) {
                    $data['fine_interest'] += self::calFineInterest($row, $time, true);
                } else {
                    $data['fine_interest'] += self::calFineInterest($row, $time);
                }
                $data['overdue_fees'] += $row['miss_overdue_fees'];
            }
        }
        if ($type == 1) {
            //退课
            $days = ceil(($time - strtotime(date('Y-m-d', strtotime($loan['pay_time'])))) / 86400);
            if ($days <= 15) {
                $rate = 0;
                $tiexi_rate = 1;
                if ($loan_type == 'kztan') {
                    $data['next_payment_principal'] = 0;
                }
                $data['next_payment_interest'] = 0;
            } elseif ($days <= 30) {
                $rate = 0.01;
                $tiexi_rate = 1;
            } elseif ($days <= 60) {
                $rate = 0.03;
                $tiexi_rate = 0.7;
            } elseif ($days <= 90) {
                $rate = 0.03;
                $tiexi_rate = 0.3;
            } else {
                $rate = 0.03;
                $tiexi_rate = 0;
            }
        } elseif ($type == 2) {
            //提前还款
            $rate = 0.02;
            $tiexi_rate = 0;
        }
        if ($loan_type == 'kztx' && $type == 1) {
            $data['fees'] = round($rate * ($data['principal_left'] + $data['next_payment_principal'] + $data['miss_principal']), 2);
        } else {
            $tiexi_rate = 0;
            $data['fees'] = round($rate * $data['principal_left'], 2);
        }
        $data['refund_tiexi'] = round(($loan['money_apply'] - $loan['money_school']) * $tiexi_rate, 2);
        $data['total'] = $data['miss_principal'];
        $data['total'] += $data['miss_interest'];
        $data['total'] += $data['fine_interest'];
        $data['total'] += $data['overdue_fees'];
        $data['total'] += $data['principal_left'];
        $data['total'] += $data['next_payment_principal'];
        $data['total'] += $data['next_payment_interest'];
        $data['total'] += $data['fees'];
        $data['total'] -= $data['refund_tiexi'];
        foreach ($data as &$v) {
            $v = round($v, 2);
        }
        $data['money_apply'] = $loan['money_apply'];
        return $data;
    }

    /**
     * 本地计算逾期天数
     */
    public static function calOverdueDays($billorid, $timestamp, $is_overdue = false) {
        $overdue_days = 0;
        if (!$timestamp) {
            $timestamp = time();
        }
        if (is_numeric($billorid)) {
            $bill = ARPayLoanBill::getBillById($billorid);
        } elseif (is_array($billorid)) {
            $bill = $billorid;
        }
        if ($is_overdue || self::isOverdue($bill['bill_date'])) {
            $should_pay_time = strtotime($bill['should_repay_date']);
            $overdue_days = floor(($timestamp - $should_pay_time) / 86400);
        }
        return $overdue_days > 0 ? $overdue_days : 0;
    }

    /**
     * 本地计算罚息
     */
    public static function calFineInterest($billorid, $timestamp, $is_overdue = false) {
        $fine_interest = 0;
        if (!$timestamp) {
            $timestamp = time();
        }
        if (is_numeric($billorid)) {
            $bill = ARPayLoanBill::getBillById($billorid);
        } elseif (is_array($billorid)) {
            $bill = $billorid;
        }
        if ($is_overdue || self::isOverdue($bill['bill_date'])) {
            $overdue_days = self::calOverdueDays($bill, $timestamp, $is_overdue);
            if ($bill['lid'] > self::THE_LID) {
                $fine_interest_rate = 0.0001;
            } else {
                $fine_interest_rate = 0.001;
            }
            $fine_interest = round(($bill['principal'] + $bill['interest']) * $fine_interest_rate, 2) * $overdue_days;
        }
        return $fine_interest > 0 ? $fine_interest : 0;
    }

    /**
     * 获得单期滞纳金金额
     */
    public static function getOverdueFee($lid) {
        $overdue_fee = 0;
        if ($lid > self::THE_LID) {
            $overdue_fee = 1;
        } else {
            $overdue_fee = 20;
        }
        return $overdue_fee;
    }

    /**
     * 本地计算是否逾期
     */
    public static function isOverdue($bill_date) {
        $overdue = false;
        $overdue_map = array();
        $overdue_map['201809'] = '20180919';
        $overdue_map['201810'] = '20181017';
        $overdue_map['201811'] = '20181117';
        $overdue_map['201812'] = '20181219';
        $overdue_map['201901'] = '20190117';
        $overdue_map['201902'] = '20190219';
        $overdue_map['201903'] = '20190319';
        $overdue_map['201904'] = '20190417';
        $overdue_map['201905'] = '20190517';
        $overdue_map['201906'] = '20190619';
        $overdue_map['201907'] = '20190717';
        $overdue_map['201908'] = '20190817';
        $overdue_map['201909'] = '20190918';
        $today = date('Ymd');
        $overdue_day = $overdue_map[$bill_date];
        if ($overdue_day) {
            if ($today >= $overdue_day) {
                $overdue = true;
            }
        } elseif ($today >= $bill_date . '25') {
            $overdue = true;
        }
        return $overdue;
    }

    /**
     * 本地生成还款计划表
     */
    public static function genLoanBill($lid, $overwrite = false) {
        $loan = Yii::app()->db->createCommand()
                ->select()
                ->from(ARLoan::TABLE_NAME)
                ->where('id=:id', array(':id' => $lid))
                ->queryRow();
        if (!$loan['pay_time'] || $loan['pay_time'] == '0000-00-00 00:00:00') {
            return;
        }
        $loantype_info = Yii::app()->db->createCommand()
                ->select()
                ->from(ARPayLoanType::TABLE_NAME)
                ->where('rateid=:rateid', array(':rateid' => $loan['loan_type']))
                ->queryRow();
        if (empty($loantype_info)) {
            return;
        }
        $old_loan_bill = ARPayLoanBill::getLoanBillByLid($lid);
        if ($old_loan_bill) {
            if ($overwrite) {
                Yii::app()->db->createCommand()
                        ->delete(ARPayLoanBill::TABLE_NAME, 'lid=:lid', array(':lid' => $lid));
            } else {
                return;
            }
        }
        $bill_date = date('Ym', strtotime($loan['pay_time']));
        if ($loan['rate_type'] == 1) {
            //弹性
            $loan_bill = self::getGpEmiBill($loan['money_apply'], $bill_date, $loantype_info['ratex'], $loantype_info['ratetimex'], $loantype_info['ratey'], $loantype_info['ratetimey']);
        } elseif ($loan['rate_type'] == 2) {
            //贴息
            $loan_bill = self::getTiexiBill($loan['money_apply'], $bill_date, $loantype_info['ratetimex']);
        } elseif ($loan['rate_type'] == 3) {
            //等额本息
            $loan_bill = self::getEmiBill($loan['money_apply'], $bill_date, $loantype_info['ratey'], $loantype_info['ratetimey']);
        }
        $i = 1;
        foreach ($loan_bill as $item) {
            $insert = array(
                'lid' => $loan['id'],
                'order_id' => $loan['id'],
                'uid' => $loan['uid'],
                'status' => 0,
                'bill_date' => $item['bill_date'],
                'installment' => $loan['repay_need'],
                'installment_plan' => $i++,
                'principal' => $item['principal'],
                'miss_principal' => $item['principal'],
                'interest' => $item['interest'],
                'miss_interest' => $item['interest'],
                'total' => $item['total'],
                'miss_total' => $item['total'],
                'should_repay_date' => date('Y-m', strtotime($item['bill_date'] . '01')) . '-15',
                'create_time' => date('Y-m-d H:i:s'),
                'rate_type' => $loan['rate_type'],
                'xy' => $item['xy'],
                'resource' => $loan['resource'],
            );
            ARPayLoanBill::insertData($insert);
        }
    }

    /**
     * 还款计划表：等额本金（贴息）
     */
    public static function getTiexiBill($amount, $bill_date, $repay_need) {
        $loan_bill = array();
        $total_principal = 0;
        $principal = round($amount / $repay_need, 2);
        for ($i = 0; $i < $repay_need; $i++) {
            $item = array();
            $item['total'] = $principal;
            $item['principal'] = $principal;
            $item['interest'] = 0;
            $bill_date = date('Ym', strtotime($bill_date . '25') + 864000);
            $item['bill_date'] = $bill_date;
            $total_principal += $item['principal'];
            $item['xy'] = 0;
            if ($i == $repay_need - 1) {
                $item['total'] += $amount - $total_principal;
                $item['principal'] += $amount - $total_principal;
            }
            $loan_bill[] = $item;
        }
        return $loan_bill;
    }

    /**
     * 还款计划表：弹性x+y
     */
    public static function getGpEmiBill($amount, $bill_date, $rate_x, $repay_need_x, $rate_y, $repay_need_y) {
        $loan_bill = array();
        $interest = round($amount * $rate_x, 2);
        for ($i = 0; $i < $repay_need_x; $i++) {
            $item = array();
            $item['total'] = $interest;
            $item['principal'] = 0;
            $item['interest'] = $interest;
            $bill_date = date('Ym', strtotime($bill_date . '25') + 864000);
            $item['bill_date'] = $bill_date;
            $item['xy'] = 1;
            $loan_bill[] = $item;
        }
        $emi_bill = self::getEmiBill($amount, $bill_date, $rate_y, $repay_need_y);
        foreach ($emi_bill as $item) {
            $item['xy'] = 2;
            $loan_bill[] = $item;
        }
        return $loan_bill;
    }

    /**
     * 还款计划表：等额本息
     */
    public static function getEmiBill($amount, $bill_date, $rate, $repay_need) {
        $loan_bill = array();
        $precision = 3;
        $total = round(($amount * $rate * $repay_need + $amount) / $repay_need, $precision);
        $n = 2 * $rate * $repay_need / ($repay_need + 1) - $rate;
        $n1 = 0;
        $n2 = 1 / $repay_need;
        $real_rate = $rate + $n;
        $flag = true;
        while ($flag) {
            $cal_total = round($amount * $real_rate * pow(1 + $real_rate, $repay_need) / (pow(1 + $real_rate, $repay_need) - 1), $precision);
            if ($cal_total < $total) {
                $n1 = $n;
                $n = ($n + $n2) / 2;
                $real_rate = $rate + $n;
            } elseif ($cal_total > $total) {
                $n2 = $n;
                $n = ($n * 2 + $n1) / 3;
                $real_rate = $rate + $n;
            } else {
                $flag = false;
            }
        }
        $base = $amount * $real_rate / (pow(1 + $real_rate, $repay_need) - 1);
        $total_principal = 0;
        $repay_total = round(($amount * $rate * $repay_need + $amount) / $repay_need, 2);
        for ($i = 0; $i < $repay_need; $i++) {
            $item = array();
            $item['total'] = $repay_total;
            $item['principal'] = round($base * pow(1 + $real_rate, $i), 2);
            $item['interest'] = $item['total'] - $item['principal'];
            $bill_date = date('Ym', strtotime($bill_date . '25') + 864000);
            $item['bill_date'] = $bill_date;
            $item['xy'] = 0;
            $total_principal += $item['principal'];
            if ($i == $repay_need - 1) {
                $item['total'] += $amount - $total_principal;
                $item['principal'] += $amount - $total_principal;
            }
            $loan_bill[] = $item;
        }
        return $loan_bill;
    }

}
