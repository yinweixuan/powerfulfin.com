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
use App\Models\DataBus;
use Illuminate\Support\Facades\Input;

class LoanController extends AppController
{
    public function __construct()
    {
        $this->checkLogin(false);
    }

    public function loanbill()
    {
        try {
            $lid = Input::get("lid");
            $user = DataBus::get("user");
            $info = Loan::getLoanBill($lid, $user['uid']);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, ['laon_bill' => $info]);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

    public function loaninfo()
    {
        try {
            $lid = Input::get("lid");
            $user = DataBus::get("user");
            $info = [];
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $info);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

}
