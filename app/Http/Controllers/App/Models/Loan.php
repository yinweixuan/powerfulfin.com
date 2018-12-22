<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/21
 * Time: 11:05 AM
 */

namespace App\Http\Controllers\App\Models;


use App\Components\PFException;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFLoanBill;
use App\Models\ActiveRecord\ARPFLoanProduct;
use App\Models\ActiveRecord\ARPFOrg;

class Loan
{
    public static function getLoanBill($lid, $uid)
    {
        $loanBills = ARPFLoanBill::getLoanBillByLidAndUid($lid, $uid);
        if (empty($loanBills)) {
            return [];
        }

        foreach ($loanBills as $loanBill) {
            $tmp = [
                'bill_id' => $loanBill['id'],
                'lid' => $lid,
                'uid' => $uid,
                'status' => $loanBill['status'],
                'status_desp' => '',
                'installment' => $loanBill['installment_plan'] . '/' . $loanBill['installment'] . '期',
                'should_repay_date' => $loanBill['should_repay_date'],
                'repay_date' => $loanBill['repay_date'],
                'repay_need' => $loanBill['total'],
                'repaid' => $loanBill['repay_total'],
                'repay_way' => '系统划扣',
                'repay_bank_account' => '',
                'repay_bank_name' => '',
                'repay_button' => 1,
                'resource' => $loanBill['resource']
            ];
            $lists[] = $tmp;
        }
    }

    public static function getLoanInfo($lid, $uid)
    {
        if (!is_numeric($lid) || !is_numeric($uid)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        $loanInfo = ARPFLoan::getLoanById($lid);
        if (empty($loanInfo)) {
            throw new PFException("暂未查询到订单信息，请稍后再试！", ERR_SYS_PARAM);
        }

        if ($loanInfo['uid'] != $uid) {
            throw new PFException("请求订单信息非当前登录用户所有，请稍后重试！", ERR_SYS_PARAM);
        }


        $loanProduct = ARPFLoanProduct::getLoanProductByProduct($loanInfo['loan_product']);
        if (empty($loanProduct)) {
            throw new PFException("金融产品异常，请稍后再试！", ERR_SYS_PARAM);
        }

        $loanBill = self::getLoanBill($lid, $uid);
        $org = ARPFOrg::getOrgById($loanInfo['oid']);
        $info = [
            'lid' => $loanInfo['id'],   //订单号
            'create_time' => $loanInfo['create_time'],  //申请时间
            'resource' => $loanInfo['resource'],    //资金方
            'borrow_money' => $loanInfo['borrow_money'],    //借款金额
            'repay_need' => count($loanBill),   //总期数
            'org' => $org['org_name'],  //机构名称
            'oid' => $loanInfo['oid'],  //机构ID
        ];

        return $info;
    }
}
