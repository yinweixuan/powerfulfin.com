<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/21
 * Time: 11:05 AM
 */

namespace App\Http\Controllers\App\Models;


use App\Models\ActiveRecord\ARPFLoanBill;

class Loan
{
    public static function getLoanBill($lid, $uid)
    {
        $loanBills = ARPFLoanBill::getLoanBillByLidAndUid($lid, $uid);
//        if (empty($loanBills)) {
//            return [];
//        }

        $lists = [
            ['bill_id' => 1,
                'lid' => $lid,
                'uid' => $uid,
                'status' => 1,
                'status_desp' => '',
                'installment' =>  '1/12期',
                'should_repay_date' => '2019-01-15',
                'repay_date' => '',
                'repay_need' => '10000.00',
                'repaid' => '0.00',
                'repay_way' => '系统划扣',
                'repay_bank_account' => '622 ******* 220',
                'repay_bank_name' => '中国工商银行',
                'repay_button' => 1,
                'resource' => 3,],
            ['bill_id' => 1,
                'lid' => $lid,
                'uid' => $uid,
                'status' => 1,
                'status_desp' => '',
                'installment' =>  '2/12期',
                'should_repay_date' => '2019-01-15',
                'repay_date' => '',
                'repay_need' => '10000.00',
                'repaid' => '0.00',
                'repay_way' => '系统划扣',
                'repay_bank_account' => '622 ******* 220',
                'repay_bank_name' => '中国工商银行',
                'repay_button' => 1,
                'resource' => 3,],

        ];
        return $lists;

//        foreach ($loanBills as $loanBill) {
//            $tmp = [
//                'bill_id' => $loanBill['id'],
//                'lid' => $lid,
//                'uid' => $uid,
//                'status' => $loanBill['status'],
//                'status_desp' => '',
//                'installment' => $loanBill['installment_plan'] . '/' . $loanBill['installment'] . '期',
//                'should_repay_date' => $loanBill['should_repay_date'],
//                'repay_date' => $loanBill['repay_date'],
//                'repay_need' => $loanBill['total'],
//                'repaid' => $loanBill['repay_total'],
//                'repay_way' => '系统划扣',
//                'repay_bank_account' => '',
//                'repay_bank_name' => '',
//                'repay_button' => 1,
//                'resource' => $loanBill['resource']
//            ];
//            $lists[] = $tmp;
//        }
    }
}
