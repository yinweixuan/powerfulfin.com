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
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, ['laon_bill' => $info]);
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
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, ['lid' => $result['id'], 'resource_company' => ARPFLoanProduct::$resourceCompany[$result['resource']]]);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

}
