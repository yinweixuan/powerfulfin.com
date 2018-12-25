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

class FcsRepayment {

    /**
     * 生成、更新还款计划表
     * 正确性依赖：次月还款，还款账期连续，还款日为每月15日。
     */
    public static function updateRepaymentSchedule($lid) {
        //获取还款计划表
        $loan_bill = ARPayLoanBill::getLoanBillByLid($lid);
        if (!$loan_bill) {
            FcsCommon::genLoanBill($lid);
            $loan_bill = ARPayLoanBill::getLoanBillByLid($lid);
        }
        //代偿的订单，不更新还款信息
        $ignore_lids = array(
            202757, 204478, 206809, 208919, 214849, 214896, 212816, 216512,
            216008, 217807, 210592, 209278, 221176, 208462, 208941, 222406,
            210464, 210592, 212816, 217807, 208941, 221176, 208462, 209278,
            216008, 216512, 218317, 221308, 217076, 220789, 217371, 213204,
            217567, 226187, 225773, 223346, 213893, 211141, 210520, 226365,
            215480, 226649, 226032, 222651, 226353, 228117, 228636, 210498,
            210771, 211997, 212013, 213335, 216770, 217869, 223015, 223549,
            226035, 228216, 228428, 228839, 230878, 231371, 232160, 232625
        );
        if (in_array($lid, $ignore_lids)) {
            return;
        }
        //忽略不更新的单条还款记录
        $exceptions = array(1397016, 1401817, 1401818, 1420638, 1420639);

        $loan = Yii::app()->db->createCommand()
                ->select()
                ->from(ARLoan::TABLE_NAME)
                ->where('id=:id', array(':id' => $lid))
                ->queryRow();
        //不更新其他状态订单
        if (!in_array($loan['status'], array(LOAN_100_REPAY, LOAN_111_OVERDUE_KZ))) {
            return;
        }
        $params = array();
        $params['channel'] = self::CHANNEL;
        $params['loanId'] = $loan['fcs_loanid'];
        $wsdl_name = 'http___www.siebel.com_FCSKeZhanLoanRepaymentscheduleWS_FCS_KeZhan_Loan_Repayment_schedule_WS.WSDL';
        $result = FcsSoap::call($wsdl_name, 'KZRepaymentSchedule', $params);
        if (is_array($result) && array_key_exists('code', $result) && $result['code'] == 0) {
            //查询成功
            //====================== 富登返回的数据 start ======================
            //基本信息（准确放款时间loanDate一定要有，此信息无法从其他途径获得，富登的放款推送时间可能不准）
            $baseinfo = $result['KZRepaySchInfo']->ListOfScheduleInfo->PostLoanEntry;
            //还款计划（可能会出现空列表，多数据，日期错误等情况，贴息类型金额错误）
            $repay_list = $baseinfo->ListOfScheduleEntry->ScheduleEntry;
            if (is_object($repay_list)) {
                $repay_list = array($repay_list);
            }
            //还款记录（可能会出现一对多，多对一，空记录等情况）
            $repay_history = $baseinfo->ListOfPaymentHistoryEntry->PaymentHistoryEntry;
            if (is_object($repay_history)) {
                $repay_history = array($repay_history);
            }
            //逾期记录（目前这个记录富登没有每日更新，不能使用）
            $overdue_list = $baseinfo->ListOfOverdueEntry->OverdueEntry;
            if (is_object($overdue_list)) {
                $overdue_list = array($overdue_list);
            }
            //总体罚息（可能和逾期天数不匹配）
            $fcs_total_overdue_interest = $result['PenaltyPayable'] ? $result['PenaltyPayable'] : 0;
            //逾期天数（可能和总罚息不匹配）
            $fcs_overdue_days = $result['OverdueDays'] ? $result['OverdueDays'] : 0;
            //滞纳金（不正确，已弃用）
            $fcs_total_overdue_fees = $result['DeferredPayable'] ? $result['DeferredPayable'] : 0;
            //======================= 富登返回的数据 end =======================
            //还款信息更新
            $repay_update = array();
            //贷款信息更新
            $update = array();
            //检查还款计划表起始账期
            if ($baseinfo->loanDate) {
                $update['pay_time'] = date('Y-m-d 00:00:00', strtotime($baseinfo->loanDate));
                $old_pay_date = date('Ym', $loan['pay_time']);
                $new_pay_date = date('Ym', $update['pay_time']);
                if ($old_pay_date != $new_pay_date) {
                    FcsCommon::genLoanBill($lid, true);
                    $loan_bill = ARPayLoanBill::getLoanBillByLid($lid);
                }
            }
            //更新还款计划
            $kz_sign = strpos($loan['loan_product'], 'KZTX');
            if (is_array($repay_list) && count($repay_list) == $loan['repay_need']) {
                foreach ($repay_list as $item) {
                    foreach ($loan_bill as $row) {
                        if ($row['installment_plan'] == $item->Periods) {
                            if ($baseinfo->repaymentMethod != '等额本金' && $kz_sign === false) {
                                $repay_update[$row['id']]['principal'] = $item->principalPayable;
                                $repay_update[$row['id']]['interest'] = $item->interestPayable;
                                $repay_update[$row['id']]['total'] = $item->totalAmountPayable;
                                $repay_update[$row['id']]['fcs_service'] = $item->serviceChargePayable;
                            }
                        }
                    }
                }
            }
            //更新已还款信息
            if (is_array($repay_history)) {
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
//                        array_multisort($sort_arr, SORT_DESC, $repay_arr);
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
                                $repay_update[$row['id']]['status'] = ARPayLoanBill::STATUS_REPAY;
                            }
                            //如果还款了，把逾期数据对上
//                            $repay_update[$row['id']]['overdue_fine_interest'] = $repay_update[$row['id']]['repay_overdue_fine_interest'];
//                            $repay_update[$row['id']]['overdue_fees'] = $repay_update[$row['id']]['repay_overdue_fees'];
//                            $repay_update[$row['id']]['total'] = round($row['principal'] + $row['interest'] + $repay_update[$row['id']]['overdue_fine_interest'] + $repay_update[$row['id']]['overdue_fees'], 2);
                        }
                    }
                }
            }
            //更新逾期信息（富登数据不可靠，本地计算数据）
            $not_overdue_status = array(
                ARPayLoanBill::STATUS_REPAY,
                ARPayLoanBill::STATUS_ADVANCE_REPAY,
                ARPayLoanBill::STATUS_WITHDRAW,
                ARPayLoanBill::STATUS_REPAYING
            );
            $now = time();
            $now_date = date('d') > 15 ? date('Ym') : date('Ym', time() - 86400 * 20);
            $overdue_flag = false;
            foreach ($loan_bill as $row) {
                if (!in_array($repay_update[$row['id']]['status'], $not_overdue_status)) {
                    //富登没有上期逾期本期逾期的规则，上期逾期本期仍会有宽限期
                    //($overdue_flag && $row['bill_date'] == $now_date) || 
                    if (FcsCommon::isOverdue($row['bill_date'])) {
                        $overdue_flag = true;
                        $repay_update[$row['id']]['status'] = ARPayLoanBill::STATUS_OVERDUE;
                        $repay_update[$row['id']]['overdue_fees'] = FcsCommon::getOverdueFee($lid);
                        $repay_update[$row['id']]['overdue_days'] = FcsCommon::calOverdueDays($row, $now, true);
                        $repay_update[$row['id']]['overdue_fine_interest'] = FcsCommon::calFineInterest($row, $now, true);
                        $repay_update[$row['id']]['total'] = $row['principal'] + $row['interest'] + $repay_update[$row['id']]['overdue_fees'] + $repay_update[$row['id']]['overdue_fine_interest'];
                    }
                }
            }
            //贷款信息更新，补全还款计划表中其他信息
            $repay_field_list = array('total', 'principal', 'interest', 'overdue_fees', 'overdue_fine_interest');
            $update['status'] = LOAN_100_REPAY;
            $update['fcs_sequence'] = $baseinfo->loanSequence;
            $update['fcs_service'] = $baseinfo->serviceReceivable;
            foreach ($loan_bill as $row) {
                foreach ($repay_update as $id => $item) {
                    if ($row['id'] == $id && !in_array($id, $exceptions)) {
                        $row = array_merge($row, $item);
                    }
                }
                foreach ($repay_field_list as $key) {
                    $repay_update[$row['id']]['miss_' . $key] = $row[$key] - $row['repay_' . $key];
                    if ($repay_update[$row['id']]['miss_' . $key] < 0 || $repay_update[$row['id']]['status'] == ARPayLoanBill::STATUS_REPAY) {
                        $repay_update[$row['id']]['miss_' . $key] = 0;
                    }
                }
                $update['money_pay_principal'] += $row['repay_principal'];
                $update['money_pay_interest'] += $row['repay_interest'];
                if ($row['status'] == ARPayLoanBill::STATUS_REPAY) {
                    $update['repay_success'] ++;
                    if (!$update['last_update_repay'] || $row['bill_date'] > $update['last_update_repay']) {
                        $update['last_update_repay'] = $row['bill_date'];
                    }
                } elseif ($row['status'] == ARPayLoanBill::STATUS_OVERDUE) {
                    $update['status'] = LOAN_111_OVERDUE_KZ;
                    $update['money_overdue_interest'] += $row['miss_overdue_fine_interest'];
                    $update['money_overdue_fee'] += $row['miss_overdue_fees'];
                }
            }
            if ($update['repay_success'] == $loan['repay_need']) {
                $update['status'] = LOAN_110_FINISH;
            }
            //记录db                    
            foreach ($repay_update as $id => $rupdate) {
                if (!in_array($id, $exceptions)) {
                    ARPayLoanBill::updateData($id, $rupdate);
                }
            }
            Yii::app()->db->createCommand()
                    ->update(ARLoan::TABLE_NAME, $update, 'id=:id', array(':id' => $lid));
        } else {
            throw new Exception('贷款还款计划表查询接口返回数据格式异常或查询失败');
        }
    }

}
