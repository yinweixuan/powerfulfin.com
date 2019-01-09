<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/1/8
 * Time: 11:58 AM
 */

namespace App\Http\Controllers\App\V1;


use App\Http\Controllers\App\AppController;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\Calc\CalcLoanBill;
use App\Models\Server\BU\BULoanBill;
use App\Models\Server\BU\BULoanProduct;
use Illuminate\Support\Facades\Input;

class TestController extends AppController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $lid = Input::get('lid');
        $loan = ARPFLoan::getLoanById($lid);
        $loan['loan_time'] = date('Y-m-d H:i:s');
        $loanBill = CalcLoanBill::createLoanBill($loan['loan_product'], $loan['loan_time'], $loan['borrow_money']);
        BULoanBill::createLoanBill($lid);
    }
}
