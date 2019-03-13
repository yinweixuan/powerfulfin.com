<?php

/**
 * 富登供模块外部调用的方法
 */

namespace App\Models\Fcs;

use App\Models\ActiveRecord\ARPFLoanBill;

class FcsCommon {

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
     * $type 1：退课；2：提前还款
     */
    public static function cancelLoan($lid, $type, $timestamp) {
        if ($timestamp) {
            $time = $timestamp + 1;
        } else {
            $time = time();
        }
        $loan = FcsDB::getLoanByLid($lid);
        if (!$loan) {
            throw new \Exception('不满足计算条件（富登）');
        }
        //判断贷款类型
        if (strpos($loan['loan_product'], 'KZTAN') !== false) {
            $loan_type = 'kztan';
        } elseif (strpos($loan['loan_product'], 'KZTX') !== false) {
            $loan_type = 'kztx';
        } elseif (strpos($loan['loan_product'], 'KZDE') !== false) {
            $loan_type = 'kzde';
        } else {
            $loan_type = '';
        }
        $bill_date = FcsUtil::getNextBillDate($time);
        $bill = ARPFLoanBill::getLoanBillByLid($lid);
        $data = array();
        $data['principal_left'] = 0;
        $data['miss_principal'] = 0;
        $data['miss_interest'] = 0;
        $data['fine_interest'] = 0;
        $data['overdue_fees'] = 0;
        $data['next_payment'] = 0;
        foreach ($bill as $row) {
            if (in_array($row['status'], [ARPFLoanBill::STATUS_NO_REPAY, ARPFLoanBill::STATUS_OVERDUE])) {
                if ($row['bill_date'] > $bill_date) {
                    $data['principal_left'] += $row['miss_principal'];
                } elseif ($row['bill_date'] == $bill_date) {
                    $data['next_payment_principal'] = $row['miss_principal'];
                    $data['next_payment_interest'] = $row['miss_interest'];
                } else {
                    $data['miss_principal'] += $row['miss_principal'];
                    $data['miss_interest'] += $row['miss_interest'];
                    if ($row['status'] == ARPFLoanBill::STATUS_OVERDUE) {
                        $data['fine_interest'] += FcsUtil::calFineInterest($row, $time, true);
                    } else {
                        $data['fine_interest'] += FcsUtil::calFineInterest($row, $time);
                    }
                    $data['overdue_fees'] += $row['miss_overdue_fees'];
                }
            }
        }
        if ($type == 1) {
            //退课
            $days = ceil(($time - strtotime(date('Y-m-d', strtotime($loan['loan_time'])))) / 86400);
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
        } else {
            $rate = 0;
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


}
