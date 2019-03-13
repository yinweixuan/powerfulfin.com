<?php

namespace App\Models\Fcs;

use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFLoanBill;
use App\Models\ActiveRecord\ARPFLoanProduct;

class FcsUtil {

    /**
     * 日志
     */
    public static function log($content) {
        pflog('fcs', $content);
    }

    /**
     * 计算当前账期
     */
    public static function getCurrentBillDate($time = null) {
        if (!$time) {
            $time = time();
        }
        if (date('j', $time) >= config('fcs.repay_day')) {
            $bill_date = date('Ym', $time);
        } else {
            $bill_date = date('Ym', strtotime(date('Ym01', $time)) - 86400);
        }
        return $bill_date;
    }

    /**
     * 计算下一个账期
     */
    public static function getNextBillDate($time = null) {
        if (!$time) {
            $time = time();
        }
        if (date('j', $time) < config('fcs.repay_day')) {
            $bill_date = date('Ym', $time);
        } else {
            $bill_date = date('Ym', strtotime(date('Ym01', $time)) + 86400 * date('t', $time) + 86400);
        }
        return $bill_date;
    }

    /**
     * 计算逾期天数
     */
    public static function calOverdueDays($bill_or_id, $timestamp = null, $is_overdue = false) {
        $overdue_days = 0;
        if (!$timestamp) {
            $timestamp = time();
        }
        if (is_numeric($bill_or_id)) {
            $bill = ARPFLoanBill::getLoanBillByLid($bill_or_id);
        } elseif (is_array($bill_or_id)) {
            $bill = $bill_or_id;
        }
        if (!empty($bill)) {
            if ($is_overdue || self::isOverdue($bill['bill_date'])) {
                $should_pay_time = strtotime($bill['should_repay_date']);
                $overdue_days = floor(($timestamp - $should_pay_time) / 86400);
            }
        }
        return $overdue_days > 0 ? $overdue_days : 0;
    }

    /**
     * 计算罚息
     */
    public static function calFineInterest($bill_or_id, $timestamp, $is_overdue = false) {
        $fine_interest = 0;
        if (!$timestamp) {
            $timestamp = time();
        }
        if (is_numeric($bill_or_id)) {
            $bill = ARPFLoanBill::getLoanBillByLid($bill_or_id);
        } elseif (is_array($bill_or_id)) {
            $bill = $bill_or_id;
        }
        if (!empty($bill)) {
            if ($is_overdue || self::isOverdue($bill['bill_date'])) {
                $overdue_days = self::calOverdueDays($bill, $timestamp, $is_overdue);
                if ($bill['lid'] > config('fcs.the_lid')) {
                    $fine_interest_rate = 0.0001;
                } else {
                    $fine_interest_rate = 0.001;
                }
                $fine_interest = round(($bill['principal'] + $bill['interest']) * $fine_interest_rate, 2) * $overdue_days;
            }
        }
        return $fine_interest > 0 ? $fine_interest : 0;
    }

    /**
     * 获得单期滞纳金金额
     */
    public static function getOverdueFee($lid) {
        if ($lid > config('fcs.the_lid')) {
            $overdue_fee = 1;
        } else {
            $overdue_fee = 20;
        }
        return $overdue_fee;
    }

    /**
     * 计算是否逾期
     */
    public static function isOverdue($bill_date) {
        $overdue = false;
        $overdue_map = config('fcs.overdue_date');
        $today = date('Ymd');
        $overdue_day = $overdue_map[$bill_date];
        if (!$overdue_day) {
            //没有明确指定逾期日期的情况只考虑周末
            $repay_time = strtotime($bill_date . config('fcs.repay_day'));
            $index = date('N', $repay_time);
            $day = config('fcs.repay_day') + 2 + 2 * floor($index / 5) - floor($index / 7);
            $overdue_day = $bill_date . $day;
        }
        if ($today >= $overdue_day) {
            $overdue = true;
        }
        return $overdue;
    }

    /**
     * 生成还款计划表
     */
    public static function genLoanBill($lid, $overwrite = false) {
        $loan = ARPFLoan::getLoanById($lid);
        if (!$loan['loan_time'] || $loan['loan_time'] == '0000-00-00 00:00:00') {
            return;
        }
        $loan_product = ARPFLoanProduct::getLoanProductByProduct($loan['loan_product']);
        if (empty($loan_product)) {
            return;
        }
        $old_loan_bill = ARPFLoanBill::getLoanBillByLid($lid);
        if ($old_loan_bill) {
            if ($overwrite) {
                FcsDB::deleteLoanBill($lid);
            } else {
                return;
            }
        }
        $bill_date = date('Ym', strtotime($loan['loan_time']));
        if ($loan_product['loan_type'] == ARPFLoanProduct::LOAN_TYPE_XY) {
            //弹性
            $loan_bill = self::getGpEmiBill($loan['borrow_money'], $bill_date, $loan_product['rate_x'], $loan_product['rate_time_x'], $loan_product['rate_y'], $loan_product['rate_time_y']);
        } elseif ($loan_product['loan_type'] == ARPFLoanProduct::LOAN_TYPE_DISCOUNT) {
            //贴息
            $loan_bill = self::getTiexiBill($loan['borrow_money'], $bill_date, $loan_product['rate_time_x']);
        } elseif ($loan_product['loan_type'] == ARPFLoanProduct::LOAN_TYPE_EQUAL) {
            //等额本息
            $loan_bill = self::getEmiBill($loan['borrow_money'], $bill_date, $loan_product['rate_y'], $loan_product['rate_time_y']);
        } else {
            $loan_bill = [];
        }
        $i = 1;
        foreach ($loan_bill as $item) {
            $insert = array(
                'lid' => $loan['id'],
                'uid' => $loan['uid'],
                'status' => ARPFLoanBill::STATUS_NO_REPAY,
                'bill_date' => $item['bill_date'],
                'installment' => $loan_product['rate_time_x'] + $loan_product['rate_time_y'],
                'installment_plan' => $i++,
                'principal' => $item['principal'],
                'miss_principal' => $item['principal'],
                'interest' => $item['interest'],
                'miss_interest' => $item['interest'],
                'total' => $item['total'],
                'miss_total' => $item['total'],
                'should_repay_date' => date('Y-m', strtotime($item['bill_date'] . '01')) . '-15',
                'create_time' => date('Y-m-d H:i:s'),
                'update_time' => date('Y-m-d H:i:s'),
                'loan_type' => $loan_product['loan_type'],
                'xy' => $item['xy'],
                'resource' => $loan['resource'],
                'remark' => '',
            );
            ARPFLoanBill::insertData($insert);
        }
    }

    /**
     * 还款计划表：等额本金（贴息）
     */
    public static function getTiexiBill($amount, $bill_date, $loan_term) {
        $loan_bill = array();
        $total_principal = 0;
        $principal = round($amount / $loan_term, 2);
        for ($i = 0; $i < $loan_term; $i++) {
            $item = array();
            $item['total'] = $principal;
            $item['principal'] = $principal;
            $item['interest'] = 0;
            $bill_date = date('Ym', strtotime($bill_date . '25') + 864000);
            $item['bill_date'] = $bill_date;
            $total_principal += $item['principal'];
            $item['xy'] = 0;
            if ($i == $loan_term - 1) {
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
    public static function getGpEmiBill($amount, $bill_date, $rate_x, $loan_term_x, $rate_y, $loan_term_y) {
        $loan_bill = array();
        $interest = round($amount * $rate_x, 2);
        for ($i = 0; $i < $loan_term_x; $i++) {
            $item = array();
            $item['total'] = $interest;
            $item['principal'] = 0;
            $item['interest'] = $interest;
            $bill_date = date('Ym', strtotime($bill_date . '25') + 864000);
            $item['bill_date'] = $bill_date;
            $item['xy'] = 1;
            $loan_bill[] = $item;
        }
        $emi_bill = self::getEmiBill($amount, $bill_date, $rate_y, $loan_term_y);
        foreach ($emi_bill as $item) {
            $item['xy'] = 2;
            $loan_bill[] = $item;
        }
        return $loan_bill;
    }

    /**
     * 还款计划表：等额本息
     */
    public static function getEmiBill($amount, $bill_date, $rate, $loan_term) {
        $loan_bill = array();
        $precision = 3;
        $total = round(($amount * $rate * $loan_term + $amount) / $loan_term, $precision);
        $n = 2 * $rate * $loan_term / ($loan_term + 1) - $rate;
        $n1 = 0;
        $n2 = 1 / $loan_term;
        $real_rate = $rate + $n;
        $flag = true;
        while ($flag) {
            $cal_total = round($amount * $real_rate * pow(1 + $real_rate, $loan_term) / (pow(1 + $real_rate, $loan_term) - 1), $precision);
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
        $base = $amount * $real_rate / (pow(1 + $real_rate, $loan_term) - 1);
        $total_principal = 0;
        $repay_total = round(($amount * $rate * $loan_term + $amount) / $loan_term, 2);
        for ($i = 0; $i < $loan_term; $i++) {
            $item = array();
            $item['total'] = $repay_total;
            $item['principal'] = round($base * pow(1 + $real_rate, $i), 2);
            $item['interest'] = $item['total'] - $item['principal'];
            $bill_date = date('Ym', strtotime($bill_date . '25') + 864000);
            $item['bill_date'] = $bill_date;
            $item['xy'] = 0;
            $total_principal += $item['principal'];
            if ($i == $loan_term - 1) {
                $item['total'] += $amount - $total_principal;
                $item['principal'] += $amount - $total_principal;
            }
            $loan_bill[] = $item;
        }
        return $loan_bill;
    }

    /**
     * 计算还款周期
     */
    public static function getLoanTerm($loan_product) {
        if ($loan_product['loan_type'] == ARPFLoanProduct::LOAN_TYPE_DISCOUNT) {
            //贴息
            $loan_term = $loan_product['rate_time_x'];
        } elseif ($loan_product['loan_type'] == ARPFLoanProduct::LOAN_TYPE_EQUAL) {
            //等额
            $loan_term = $loan_product['rate_time_y'];
        } elseif ($loan_product['loan_type'] == ARPFLoanProduct::LOAN_TYPE_XY) {
            //弹性
            $loan_term = $loan_product['rate_time_x'] + $loan_product['rate_time_y'];
        } else {
            //未知新类型
            $loan_term = $loan_product['rate_time_x'] + $loan_product['rate_time_y'];
        }
        return $loan_term;
    }

}
