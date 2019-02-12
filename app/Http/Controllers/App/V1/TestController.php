<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/1/8
 * Time: 11:58 AM
 */

namespace App\Http\Controllers\App\V1;


use App\Components\OutputUtil;
use App\Components\PFException;
use App\Http\Controllers\App\AppController;
use App\Http\Controllers\App\Models\Loan;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFLoanBill;
use App\Models\ActiveRecord\ARPFLoanProduct;
use App\Models\Calc\CalcLoanBill;
use App\Models\DataBus;
use App\Models\Server\BU\BULoanBill;
use App\Models\Server\BU\BULoanProduct;
use App\Models\Server\BU\BUUserInfo;
use Illuminate\Support\Facades\Input;

class TestController extends AppController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
//        $lid = Input::get('lid');
//        $loan = ARPFLoan::getLoanById($lid);
//        $loan['loan_time'] = date('Y-m-d H:i:s');
//        $loanBill = CalcLoanBill::createLoanBill($loan['loan_product'], $loan['loan_time'], $loan['borrow_money']);
//        BULoanBill::createLoanBill($lid);

//        $uid = '1000017';
//        $result = BUUserInfo::getUserStatus($uid);
//        OutputUtil::info(0, 0, $result);


//        $user['id'] = 1000000;
//        $data=[
//            'full_name' => '姓名',
//            'identity_number' => '131102199105270218',
//            'start_date' => '2018-02-23',
//            'end_date' => '2038-02-23',
//            'address' => '河北衡水xxxx',
//            'idcard_information_pic' => 'simg/xxxxxxxx',
//            'idcard_national_pic' => 'simg/xxxxxxxx',
//            'nationality' => '回',
//            'issuing_authority' => '衡水市桃城区'
//        ];
//        BUUserInfo::userReal($data, $user);

//        $lid = 1000009;
////        $info = Loan::getLoanBill($lid, 1000008);
////        var_dump($info);
////        OutputU$uidtil::info(0, 0, $info);
//        $uid = 1000008;
//        $loanBills = ARPFLoanBill::getLoanBillByLidAndUid($lid, $uid);
//        if (empty($loanBills)) {
//            return [];
//        }
//        $lists = [];
//        foreach ($loanBills as $loanBill) {
//            $tmp = [
//                'bill_id' => $loanBill['id'],
//                'lid' => $lid,
//                'uid' => $uid,
//                'status' => $loanBill['status'],
//                'status_desp' => BULoanBill::getLoanBillStatusDesp($loanBill['status']),
//                'installment' => $loanBill['installment_plan'] . '/' . $loanBill['installment'] . '期',
//                'should_repay_date' => $loanBill['should_repay_date'],
//                'repay_date' => $loanBill['repay_date'],
//                'repay_need' => $loanBill['total'],
//                'repaid' => $loanBill['repay_total'],
//                'repay_way' => '系统划扣',
//                'repay_bank_account' => '',
//                'repay_bank_name' => '',
//                'repay_button' => 0,
//                'resource' => $loanBill['resource'],
//                'resource_company' => ARPFLoanProduct::$resourceCompany[$loanBill['resource']],
//            ];
//            $lists[] = $tmp;
//        }
//        OutputUtil::info(0, 0, $lists);

//        throw new PFException(111, 111);

        $redis = app('redis.connection');
        var_dump($redis);

    }
}
