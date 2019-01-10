<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/1/8
 * Time: 11:58 AM
 */

namespace App\Http\Controllers\App\V1;


use App\Components\OutputUtil;
use App\Http\Controllers\App\AppController;
use App\Http\Controllers\App\Models\Loan;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\Calc\CalcLoanBill;
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

        $uid = '1000008';
        $result = BUUserInfo::getUserWork($uid);
        OutputUtil::info(0, 0, $result);
    }
}
