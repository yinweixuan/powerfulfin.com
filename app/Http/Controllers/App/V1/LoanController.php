<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/20
 * Time: 3:46 PM
 */

namespace App\Http\Controllers\App\V1;


use App\Components\OutputUtil;
use App\Components\PFException;
use App\Http\Controllers\App\AppController;
use App\Http\Controllers\App\Models\Loan;
use App\Models\ActiveRecord\ARPFLoanProduct;
use App\Models\DataBus;
use App\Models\Server\BU\BULoanApply;
use App\Models\Server\BU\BULoanProduct;
use Illuminate\Support\Facades\Input;

class LoanController extends AppController
{
    public function __construct()
    {
        $this->checkLogin(false);
    }

    public function loanBill()
    {
        try {
            $lid = Input::get("lid");
            $user = DataBus::get("user");
            $info = Loan::getLoanBill($lid, $user['id']);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, ['loan_bill' => $info]);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

    public function loanInfo()
    {
        try {
            $lid = Input::get("lid");
            $user = DataBus::get("user");
            $info = Loan::getLoanInfo($lid, $user['id']);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $info);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

    public function loanList()
    {
        try {
            $user = DataBus::get("user");
            $info = Loan::getLoanList($user['id']);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, ['list' => $info]);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

    public function loanConfig()
    {
        try {
            $oid = Input::get("oid");
            $data = Loan::getLoanConfig($oid, DataBus::getUid());
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $data);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

    public function loanSubmit()
    {
        try {
            $data = !empty($_POST) ? $_POST : $_GET;
            $result = Loan::submitLoan($data, DataBus::getUid());
            $info = OutputUtil::json_decode($result['supply_info']);
            $bank = [
                'bank_account' => $info['bank']['bank_account'],
                'bank_name' => $info['bank']['bank_name']
            ];
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, [
                'lid' => $result['id'],
                'resource_company' => ARPFLoanProduct::$resourceCompany[$result['resource']],
                'repay_info' => $this->getCalc($result['borrow_money'], $result['loan_product']),
                'bank_info' => $bank,
            ]);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

    public function calc()
    {
        try {

            $borrow_money = Input::get('borrow_money');
            $loan_product = Input::get('loan_product');
            $info = $this->getCalc($borrow_money, $loan_product);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $info);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

    private function getCalc($borrow_money, $loan_product)
    {
        $info = [
            'title' => '每月15日为还款日',
            'content_one' => [
                'content' => '',
                'money' => '',
            ],
            'content_two' => [
                'content' => '',
                'money' => '',
            ],
        ];
        if (empty($loan_product) || empty($borrow_money)) {
            OutputUtil::err(ERR_OK_CONTENT, ERR_OK, $info);
        }

        $loanProducts = BULoanProduct::getLoanTypeByIds([$loan_product]);
        if (empty($loanProducts)) {
            throw new PFException("金融产品异常，请稍后重试", ERR_SYS_PARAM);
        }
        $loanProduct = array_shift($loanProducts);
        if ($loanProduct['loan_type'] == ARPFLoanProduct::LOAN_TYPE_XY) {
            $content_one = '前' . $loanProduct['rate_time_x'] . '期每期还款';
            $money_one = '￥' . OutputUtil::echoMoney($borrow_money * $loanProduct['rate_x']);
            $content_two = '后' . $loanProduct['rate_time_y'] . '期每期还款';
            $money_two = '￥' . OutputUtil::echoMoney(($borrow_money * $loanProduct['rate_y'] + $borrow_money / $loanProduct['rate_time_y']));
        } else if ($loanProduct['loan_type'] == ARPFLoanProduct::LOAN_TYPE_DISCOUNT) {
            $content_one = '共' . $loanProduct['rate_time_x'] . '期每期还款';
            $money_one = '￥' . OutputUtil::echoMoney(($borrow_money / $loanProduct['rate_time_x']));
            $content_two = '';
            $money_two = '';
        } else if ($loanProduct['loan_type'] == ARPFLoanProduct::LOAN_TYPE_EQUAL) {
            $content_one = '共' . $loanProduct['rate_time_y'] . '期每期还款';
            $money_one = '￥' . OutputUtil::echoMoney(($borrow_money / $loanProduct['rate_time_y'] + $borrow_money * $loanProduct['rate_y']));
            $content_two = '';
            $money_two = '';
        } else {
            throw new PFException("试算失败，亲稍后重试", ERR_SYS_PARAM);
        }
        $info['content_one']['content'] = $content_one;
        $info['content_one']['money'] = $money_one;
        $info['content_two']['content'] = $content_two;
        $info['content_two']['money'] = $money_two;
        return $info;
    }

}
