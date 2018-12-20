<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/20
 * Time: 4:52 PM
 */

namespace App\Http\Controllers\App\V1;


use App\Components\OutputUtil;
use App\Components\PFException;
use App\Http\Controllers\App\AppController;
use App\Models\DataBus;
use App\Models\Server\BU\BUUserBank;
use Illuminate\Support\Facades\Input;

class BankController extends AppController
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->isLogin()) {
            OutputUtil::err(ERR_NOLOGIN_CONTENT, ERR_NOLOGIN);
        }
    }

    public function banks()
    {
        try {
            $user = DataBus::get("user");
            $info = BUUserBank::getUserBanks($user['id']);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $info);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

    /**
     * 获取短信验证码
     */
    public function sms()
    {
        try {
            $bank_account = Input::get("bank_account");
            $bank_code = Input::get("bank_code");
            $phone = Input::get("phone");
            $user = DataBus::get("user");
            $result = BUUserBank::bindSmsBaofu($user['id'], $phone, $bank_account, $bank_code);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $result);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

    /**
     * 绑卡
     */
    public function bind()
    {
        try {
            $bank_account = Input::get("bank_account");
            $bank_code = Input::get("bank_code");
            $phone = Input::get("phone");
            $vcode = Input::get("vcode");
            $serialNumber = Input::get("serialnumber");
            $user = DataBus::get("user");
            $result = BUUserBank::bindBaofu($user['uid'], $phone, $bank_account, $bank_code, $vcode, $serialNumber);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $result);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

    public function change()
    {
        try {
            $bank_account = Input::get("bank_account");
            $user = DataBus::get("user");
            $result = BUUserBank::changeRepayCard($user['uid'], $bank_account);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $result);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }
}
