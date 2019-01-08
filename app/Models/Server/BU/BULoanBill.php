<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/21
 * Time: 11:05 AM
 */

namespace App\Models\Server\BU;


use App\Components\PFException;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFLoanBill;
use App\Models\ActiveRecord\ARPFLoanProduct;
use App\Models\Calc\CalcLoanBill;

class BULoanBill
{
    /**
     * 创建还款计划表
     * @param $lid
     * @throws PFException
     */
    public static function createLoanBill($lid)
    {
        if (is_null($lid) || !is_numeric($lid)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        $checkLoanBillIsExist = ARPFLoanBill::getLoanBillByLid($lid);
        if ($checkLoanBillIsExist) {
            throw new PFException("此单号已经创建账单，请勿重复创建", ERR_SYS_PARAM);
        }

        $loan = ARPFLoan::getLoanById($lid);
        if (empty($loan)) {
            throw new PFException('暂未查询到订单信息，请稍后重试', ERR_SYS_PARAM);
        }

        if (empty($loan['loan_time'])) {
            $loan_time = date('Y-m-d H:i:s');
        } else {
            $loan_time = $loan['loan_time'];
        }

        $loanBill = CalcLoanBill::createLoanBill($loan['loan_product'], $loan_time, $loan['borrow_money']);

        $installment = count($loanBill['repay']);
        $i = 1;
        $loanProduct = ARPFLoanProduct::getLoanProductByProduct($loan['loan_product']);
        foreach ($loanBill['repay'] as $item) {
            $insert = array(
                'lid' => $loan['id'],
                'uid' => $loan['uid'],
                'status' => ARPFLoanBill::STATUS_NO_REPAY,
                'bill_date' => substr($item['repay'], 0, 6),
                'installment' => $installment,
                'installment_plan' => $i++,
                'principal' => $item['principal'],
                'miss_principal' => $item['principal'],
                'interest' => $item['interest'],
                'miss_interest' => $item['interest'],
                'total' => $item['total'],
                'miss_total' => $item['total'],
                'should_repay_date' => date('Y-m-d', strtotime($item['repay'])),
                'create_time' => date('Y-m-d H:i:s'),
                'loan_type' => $loanProduct['loan_type'],
                'xy' => 0,
                'resource' => $loan['resource'],
                'remark' => '创建还款计划'
            );
            if ($loanProduct['loan_type'] == ARPFLoanProduct::LOAN_TYPE_XY) {
                if ($loanProduct['rate_time_x'] >= $insert['installment_plan']) {
                    $insert['xy'] = 1;
                } else {
                    $insert['xy'] = 2;
                }
            }

            if ($insert['installment'] == $insert['installment_plan'] && $loan['resource'] == RESOURCE_JCFC) {
                $payDay = date('d', strtotime($loan['pay_time']));
                if ($payDay < FN_REPAY_DAY) {
                    $insert['should_repay_date'] = date('Y-m', strtotime($item['repay'])) . '-' . $payDay;
                }
            }

            try {
                ARPFLoanBill::insertData($insert);
            } catch (PFException $e) {
                continue;
            }
        }
    }

    public static function getLoanBillStatusDesp($status)
    {
        switch ($status) {
            case ARPFLoanBill::STATUS_NO_REPAY:
                $status_desp = '待还款';
                break;
            case ARPFLoanBill::STATUS_REPAY:
                $status_desp = '已结清';
                break;
            case ARPFLoanBill::STATUS_OVERDUE:
                $status_desp = '已逾期';
                break;
            case ARPFLoanBill::STATUS_ADVANCE_REPAY:
                $status_desp = '提前还款';
                break;
            case ARPFLoanBill::STATUS_WITHDRAW:
                $status_desp = '已退课';
                break;
            default:
                $status_desp = '未知';
                break;
        }
        return $status_desp;
    }
}
