<?php

namespace App\Models\Fcs;

use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFLoanBill;
use App\Models\ActiveRecord\ARPFLoanProduct;

class FcsRepayment {

    /**
     * 生成、更新还款计划表
     * 正确性依赖：次月还款，还款账期连续，还款日为每月15日。
     */
    public static function updateRepaymentSchedule($lid) {
        //获取还款计划表
        $loan_bill = self::getLoanBill($lid);
        //获取订单信息
        $loan = ARPFLoan::getLoanById($lid);
        //不更新其他状态订单
        if (!in_array($loan['status'], array(LOAN_10000_REPAY, LOAN_11100_OVERDUE))) {
            return;
        }
        //忽略不更新的单条还款记录
        $ignored_bill_ids = config('fcs.ignored_bill_ids');
        //还款信息更新
        $repay_update = array();
        //贷款信息更新
        $update = array();
        //富登提供的信息
        $fcs_data = array();
        //代偿的订单，不更新还款信息
        $wepay_lids = config('fcs.wepay_lids');
        //非代偿更新数据
        if (!in_array($lid, $wepay_lids)) {
            $fcs_data = self::getFcsRepaymentInfo($loan['resource_loan_id']);
            //检查还款计划表起始账期
            if ($fcs_data['baseinfo']->loanDate) {
                $update['loan_time'] = date('Y-m-d 00:00:00', strtotime($fcs_data['baseinfo']->loanDate));
                $old_pay_date = date('Ym', $loan['loan_time']);
                $new_pay_date = date('Ym', $update['loan_time']);
                if ($old_pay_date != $new_pay_date) {
                    $loan_bill = self::getLoanBill($lid, true);
                }
            }
            //更新还款计划（富登贴息类型数据错误，不更新）
            $repay_update = self::getRepayListUpdate($loan, $fcs_data, $loan_bill, $repay_update);
            //更新已还款信息
            $repay_update = self::getRepayHistoryUpdate($loan, $fcs_data, $loan_bill, $repay_update);
        }
        //更新逾期信息（富登数据不可靠，本地计算数据）
        $repay_update = self::getRepayOverdueUpdate($loan, $fcs_data, $loan_bill, $repay_update, $wepay_lids);
        //补全还款计划表中其他信息
        $repay_update = self::fillRepayUpdate($loan_bill, $repay_update, $ignored_bill_ids);
        //贷款信息更新
        $update = self::getLoanUpdate($loan, $fcs_data, $loan_bill, $repay_update, $update, $ignored_bill_ids);
        //更新db
        foreach ($repay_update as $id => $row_update) {
            if (!in_array($id, $ignored_bill_ids)) {
                ARPFLoanBill::_update($id, $row_update);
            }
        }
        ARPFLoan::_update($lid, $update);
    }

    /**
     * 获取本地还款计划表，没有则创建
     */
    public static function getLoanBill($lid, $rebuild = false) {
        if (!$rebuild) {
            $loan_bill = ARPFLoanBill::getLoanBillByLid($lid);
        }
        if (empty($loan_bill)) {
            FcsUtil::genLoanBill($lid);
            $loan_bill = ARPFLoanBill::getLoanBillByLid($lid);
        }
        return $loan_bill;
    }

    /**
     * 获取富登返回信息
     */
    public static function getFcsRepaymentInfo($fcs_loanid) {
        $params = array();
        $params['channel'] = config('fcs.channel');
        $params['loanId'] = $fcs_loanid;
        $wsdl_name = 'http___www.siebel.com_FCSKeZhanLoanRepaymentscheduleWS_FCS_KeZhan_Loan_Repayment_schedule_WS.WSDL';
        $result = FcsSoap::call($wsdl_name, 'KZRepaymentSchedule', $params);
        $fcs_data = [];
        if (is_array($result) && array_key_exists('code', $result) && $result['code'] == 0) {
            //基本信息（准确放款时间loanDate一定要有，此信息无法从其他途径获得，富登的放款推送时间可能不准）
            $fcs_data['baseinfo'] = $result['KZRepaySchInfo']->ListOfScheduleInfo->PostLoanEntry;
            //还款计划（可能会出现空列表，多数据，日期错误等情况，贴息类型金额错误）
            $fcs_data['repay_list'] = $fcs_data['baseinfo']->ListOfScheduleEntry->ScheduleEntry;
            if (is_object($fcs_data['repay_list'])) {
                $fcs_data['repay_list'] = array($fcs_data['repay_list']);
            }
            //还款记录（可能会出现一对多，多对一，空记录等情况）
            $fcs_data['repay_history'] = $fcs_data['baseinfo']->ListOfPaymentHistoryEntry->PaymentHistoryEntry;
            if (is_object($fcs_data['repay_history'])) {
                $fcs_data['repay_history'] = array($fcs_data['repay_history']);
            }
            //逾期记录（目前这个记录富登没有每日更新，不能使用）
            $fcs_data['overdue_list'] = $fcs_data['baseinfo']->ListOfOverdueEntry->OverdueEntry;
            if (is_object($fcs_data['overdue_list'])) {
                $fcs_data['overdue_list'] = array($fcs_data['overdue_list']);
            }
            //总体罚息（可能和逾期天数不匹配）
            $fcs_data['fcs_total_overdue_interest'] = $result['PenaltyPayable'] ? $result['PenaltyPayable'] : 0;
            //逾期天数（可能和总罚息不匹配）
            $fcs_data['fcs_overdue_days'] = $result['OverdueDays'] ? $result['OverdueDays'] : 0;
            //滞纳金（不正确，已弃用）
            $fcs_data['fcs_total_overdue_fees'] = $result['DeferredPayable'] ? $result['DeferredPayable'] : 0;
        } else {
            throw new \Exception('贷款还款计划表查询接口返回数据格式异常或查询失败');
        }
        return $fcs_data;
    }


    public static function convertRepayHistory($repay_history, $loan_bill) {
        //处理后的还款记录，一期对应一个记录
        $new_repay_history = array();
        //一对多的还款记录
        $temp_repay_history = array();
        //把还款记录对应到每一期，可能一期对应多笔
        foreach ($repay_history as $item) {
            //去掉无效记录（确实有这样的记录返回）
            if ($item->RepayTotal > 0 && (
                    $item->actualPrincipal > 0 ||
                    $item->actualInterest > 0 ||
                    $item->actualPenalty > 0 ||
                    $item->actualService > 0 ||
                    $item->Amount > 0
                )) {
                $bill_date = date('Ym', strtotime($item->actualRepayDate) - 86400 * $item->overdueDaysBeRepay);
                $temp_repay_history[$bill_date][] = $item;
            }
        }
        //记录一对一的还款记录
        foreach ($temp_repay_history as $bill_date => $repay_arr) {
            if (count($repay_arr) == 1) {
                $new_repay_history[$bill_date] = $repay_arr[0];
            }
        }
        //处理一期对应多笔记录的情况
        foreach ($temp_repay_history as $bill_date => $repay_arr) {
            if (count($repay_arr) > 1) {
                //先将记录排序，罚息高的为最早一期
                $sort_arr = array();
                $sum_total = 0;
                foreach ($repay_arr as $repay_item) {
                    $sort_arr[] = $repay_item->actualPenalty;
                    $sum_total += $repay_item->RepayTotal;
                }
//              array_multisort($sort_arr, SORT_DESC, $repay_arr);
                //处理一次还多期和多次还一期的情况，先不考虑多次还多期
                $repay_date_timestamp = strtotime($repay_arr[0]->actualRepayDate);
                $bill_month_days = date('t', $repay_date_timestamp - 86400 * $repay_arr[0]->overdueDaysBeRepay);
                //如果逾期天数大于当期到下一期的天数且还款总金额够多，认为是一次还多期
                $pay_n = false;
                if ($repay_arr[0]->overdueDaysBeRepay >= $bill_month_days) {
                    $bill_date_next = date('Ym', strtotime($bill_date . '20') + 86400 * 20);
                    $total_1 = 0;
                    $total_2 = 0;
                    foreach ($loan_bill as $row) {
                        if ($row['bill_date'] == $bill_date) {
                            $total_1 = $row['total'];
                        } elseif ($row['bill_date'] == $bill_date_next) {
                            $total_2 = $row['total'];
                        }
                    }
                    if ($sum_total > $total_1 + $total_2 * 0.8) {
                        $pay_n = true;
                    }
                }
                if ($pay_n) {
                    //还多期
                    $repay_day = date('j', $repay_date_timestamp);
                    if ($repay_day >= 15) {
                        $last_bill_date = date('Ym', $repay_date_timestamp);
                    } else {
                        $last_bill_date = date('Ym', $repay_date_timestamp - 86400 * ($repay_day + 10));
                    }
                    $bill_date_index = $bill_date;
                    foreach ($repay_arr as $repay_item) {
                        if (!array_key_exists($bill_date_index, $new_repay_history) && $bill_date_index <= $last_bill_date) {
                            $new_repay_history[$bill_date_index] = $repay_item;
                        }
                        //期数+1
                        $bill_date_index = date('Ym', strtotime($bill_date_index . '28') + 864000);
                    }
                } else {
                    //还一期
                    $sum_item = array_shift($repay_arr);
                    foreach ($repay_arr as $repay_item) {
                        $sum_item->actualPrincipal += $repay_item->actualPrincipal;
                        $sum_item->actualInterest += $repay_item->actualInterest;
                        $sum_item->RepayTotal += $repay_item->RepayTotal;
                        $sum_item->actualPenalty += $repay_item->actualPenalty;
                        $sum_item->overdueDaysBeRepay = $sum_item->overdueDaysBeRepay > $repay_item->overdueDaysBeRepay ? $sum_item->overdueDaysBeRepay : $repay_item->overdueDaysBeRepay;
                        $sum_item->actualService += $repay_item->actualService;
                        $sum_item->Amount += $repay_item->Amount;
                    }
                    $new_repay_history[$bill_date] = $sum_item;
                }
            }
        }
        return $new_repay_history;
    }


    public static function getRepayListUpdate($loan, $fcs_data, $loan_bill, $repay_update) {
        if (is_array($fcs_data['repay_list']) && !empty($fcs_data['repay_list'])) {
            $loan_product = ARPFLoanProduct::getLoanProductByProduct($loan['loan_product']);
            $loan_term = FcsUtil::getLoanTerm($loan_product);
            if (count($fcs_data['repay_list']) == $loan_term && $loan_product['loan_type'] != ARPFLoanProduct::LOAN_TYPE_DISCOUNT) {
                foreach ($fcs_data['repay_list'] as $item) {
                    foreach ($loan_bill as $row) {
                        if ($row['installment_plan'] == $item->Periods) {
                            $repay_update[$row['id']]['principal'] = $item->principalPayable;
                            $repay_update[$row['id']]['interest'] = $item->interestPayable;
                            $repay_update[$row['id']]['total'] = $item->totalAmountPayable;
                            $repay_update[$row['id']]['fcs_service'] = $item->serviceChargePayable;
                        }
                    }
                }
            }
        }
        return $repay_update;
    }

    public static function getRepayHistoryUpdate($loan, $fcs_data, $loan_bill, $repay_update) {
        if (is_array($fcs_data['repay_history'])) {
            $new_repay_history = self::convertRepayHistory($fcs_data['repay_history'], $loan_bill);
            foreach ($loan_bill as $row) {
                foreach ($new_repay_history as $bill_date => $item) {
                    if ($row['bill_date'] == $bill_date) {
                        $repay_update[$row['id']]['repay_date'] = date('Y-m-d H:i:s', strtotime($item->actualRepayDate));
                        $repay_update[$row['id']]['repay_principal'] = $item->actualPrincipal;
                        $repay_update[$row['id']]['repay_interest'] = $item->actualInterest;
                        $repay_update[$row['id']]['repay_total'] = $item->RepayTotal;
                        $repay_update[$row['id']]['repay_overdue_fine_interest'] = $item->actualPenalty;
                        $repay_update[$row['id']]['overdue_days'] = $item->overdueDaysBeRepay;
                        $repay_update[$row['id']]['fcs_repay_service'] = $item->actualService;
                        $repay_update[$row['id']]['repay_overdue_fees'] = $item->Amount ? $item->Amount : 0;
                        if (in_array($row['debit_way'], array(0, 2, 3, 4))) {
                            $repay_update[$row['id']]['debit_way'] = 1;
                        }
                        /*
                         * 有记录暂且认为是已还款，应该不会有部分还款的情况，目前有用户多还的情况，
                         * 也有免罚息的情况，如果多还的用户下期逾期且免了罚息，那么是否逾期
                         * 的标记（RepayIsOverdue）是“是”且实还金额会少于应还金额，这样则无法从
                         * 还款信息判断是否正常还款。
                         */
                        if ($item->RepayTotal > $row['total'] * 0.8) {
                            $repay_update[$row['id']]['status'] = ARPFLoanBill::STATUS_REPAY;
                        }
                    }
                }
                if (!$repay_update[$row['id']]['repay_date']) {
                    //没有还款记录的要清空
                    $repay_update[$row['id']]['repay_date'] = '';
                    $repay_update[$row['id']]['repay_principal'] = 0;
                    $repay_update[$row['id']]['repay_interest'] = 0;
                    $repay_update[$row['id']]['repay_total'] = 0;
                    $repay_update[$row['id']]['repay_overdue_fine_interest'] = 0;
                    $repay_update[$row['id']]['overdue_days'] = 0;
                    $repay_update[$row['id']]['fcs_repay_service'] = 0;
                    $repay_update[$row['id']]['repay_overdue_fees'] = 0;
                    $repay_update[$row['id']]['debit_way'] = 0;
                    $repay_update[$row['id']]['status'] = ARPFLoanBill::STATUS_NO_REPAY;
                }
            }
        }
        return $repay_update;
    }

    public static function getRepayOverdueUpdate($loan, $fcs_data, $loan_bill, $repay_update, $wepay_lids) {
        $not_overdue_status = array(
            ARPFLoanBill::STATUS_REPAY,
            ARPFLoanBill::STATUS_ADVANCE_REPAY,
            ARPFLoanBill::STATUS_WITHDRAW,
            ARPFLoanBill::STATUS_REPAYING
        );
        $now = time();
        foreach ($loan_bill as $row) {
            //富登更新还款标记（非代偿订单以接口数据为准）
            $update_repay_flag = in_array($repay_update[$row['id']]['status'], $not_overdue_status) && !in_array($loan['id'], $wepay_lids);
            //代偿还款标记（代偿订单以数据库记录为准）
            $db_repay_flag = in_array($row['status'], $not_overdue_status) && in_array($loan['id'], $wepay_lids);
            if (!$update_repay_flag && !$db_repay_flag) {
                //富登没有上期逾期本期逾期的规则，上期逾期本期仍会有宽限期
                if (FcsUtil::isOverdue($row['bill_date'])) {
                    $repay_update[$row['id']]['status'] = ARPFLoanBill::STATUS_OVERDUE;
                    $repay_update[$row['id']]['overdue_fees'] = FcsUtil::getOverdueFee($loan['id']);
                    $repay_update[$row['id']]['overdue_days'] = FcsUtil::calOverdueDays($row, $now, true);
                    $repay_update[$row['id']]['overdue_fine_interest'] = FcsUtil::calFineInterest($row, $now, true);
//                    $repay_update[$row['id']]['total'] = $row['principal'] + $row['interest'] + $repay_update[$row['id']]['overdue_fees'] + $repay_update[$row['id']]['overdue_fine_interest'];
                }
            }
        }
        return $repay_update;
    }

    public static function fillRepayUpdate($loan_bill, $repay_update, $ignored_bill_ids) {
        $repay_field_list = array('principal', 'interest', 'overdue_fees', 'overdue_fine_interest');
        foreach ($loan_bill as $row) {
            foreach ($repay_update as $id => $item) {
                if ($row['id'] == $id && !in_array($id, $ignored_bill_ids)) {
                    $row = array_merge($row, $item);
                }
            }
            foreach ($repay_field_list as $key) {
                $repay_update[$row['id']]['total'] += $row[$key];
                $repay_update[$row['id']]['repay_total'] += $row['repay_' . $key];
                $repay_update[$row['id']]['miss_' . $key] = $row[$key] - $row['repay_' . $key];
                if ($repay_update[$row['id']]['miss_' . $key] < 0 || $repay_update[$row['id']]['status'] == ARPFLoanBill::STATUS_REPAY) {
                    $repay_update[$row['id']]['miss_' . $key] = 0;
                }
                $repay_update[$row['id']]['miss_total'] += $repay_update[$row['id']]['miss_' . $key];
            }
        }
        return $repay_update;
    }


    public static function getLoanUpdate($loan, $fcs_data, $loan_bill, $repay_update, $update, $ignored_bill_ids) {
        $update['status'] = LOAN_10000_REPAY;
        $update['loan_time'] = date('Y-m-d 00:00:00', strtotime($fcs_data['baseinfo']->loanDate));
        $update['fcs_sequence'] = $fcs_data['baseinfo']->loanSequence;
        $update['fcs_service'] = $fcs_data['baseinfo']->serviceReceivable;
        foreach ($loan_bill as $row) {
            foreach ($repay_update as $id => $item) {
                if ($row['id'] == $id && !in_array($id, $ignored_bill_ids)) {
                    $row = array_merge($row, $item);
                }
            }
            if ($row['status'] == ARPFLoanBill::STATUS_OVERDUE) {
                $update['status'] = LOAN_11100_OVERDUE;
            }
        }
        if (isset($row) && $row['status'] == ARPFLoanBill::STATUS_REPAY) {
            $update['status'] = LOAN_11000_FINISH;
        }
        return $update;
    }
}
