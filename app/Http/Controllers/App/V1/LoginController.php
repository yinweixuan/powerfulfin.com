<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 3:38 PM
 */

namespace App\Http\Controllers\App\V1;


use App\Components\CheckUtil;
use App\Components\OutputUtil;
use App\Components\PfException;
use App\Http\Controllers\App\AppController;
use App\Models\ActiveRecord\ARPfUsers;
use App\Models\DataBus;
use App\Models\Server\VerifyCode;
use Illuminate\Support\Facades\Input;

class LoginController extends AppController
{
    public function login()
    {
        try {
            $phone = Input::get("phone");
            $userInfo = ARPfUsers::getUserInfoByPhone($phone);
            if (empty($userInfo)) {
                $info = [
                    'phone' => $phone,
                    'username' => substr($phone, 0, 3) . '****' . substr($phone, -4, 4)
                ];
                $result = ARPfUsers::addUserInfo($info);
                if ($result) {
                    $userInfo = ARPfUsers::getUserInfoByPhone($phone);
                } else {
                    throw new PfException(ERR_REGISTER_CONTENT, ERR_REGISTER);
                }
            }
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $userInfo);
        } catch (PfException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode());
        }
    }

    public function verifycode()
    {
        try {
            $phone = Input::get("phone");
            if (!CheckUtil::checkPhone($phone)) {
                throw new PfException(ERR_PHONE_FORMAT_CONTENT, ERR_PHONE_FORMAT);
            }
            $ip = DataBus::get('ip');
            VerifyCode::sendVerifyCode($phone, $ip);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK);
        } catch (PfException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode());
        }
    }
}
