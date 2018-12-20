<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 3:38 PM
 */

namespace App\Http\Controllers\App\V1;


use App\Components\CheckUtil;
use App\Components\CookieUtil;
use App\Components\OutputUtil;
use App\Components\PFException;
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
            $vcode = Input::get("vcode");
            $ip = DataBus::get('ip');
            if (!VerifyCode::checkVerifyCode($phone, $ip, $vcode)) {
                throw new PFException(ERR_VCODE_CHECK_CONTENT, ERR_VCODE_CHECK);
            }

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
                    throw new PFException(ERR_REGISTER_CONTENT, ERR_REGISTER);
                }
            }
            $cookie = self::getCookie($userInfo);
            CookieUtil::Cookie(DataBus::COOKIE_KEY, $cookie[CookieUtil::db_cookiepre . '_' . DataBus::COOKIE_KEY]);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, [CookieUtil::db_cookiepre . '_' . DataBus::COOKIE_KEY => $_COOKIE['dw8zh_powerfulfin_user']]);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode());
        }
    }

    public function verifycode()
    {
        try {
            $phone = Input::get("phone");
            if (!CheckUtil::checkPhone($phone)) {
                throw new PFException(ERR_PHONE_FORMAT_CONTENT, ERR_PHONE_FORMAT);
            }
            $ip = DataBus::get('ip');
            VerifyCode::sendVerifyCode($phone, $ip);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode());
        }
    }

    private function getCookie(array $userInfo)
    {
        if (empty($userInfo)) {
            $cookie = [];
        } else {
            $strCode = $userInfo['id'] . "|" . $userInfo['username'] . "|" . $userInfo['phone'] . '|' . CookieUtil::createSafecv();
            $cookie = [CookieUtil::db_cookiepre . '_' . DataBus::COOKIE_KEY => CookieUtil::strCode($strCode)];
        }
        return $cookie;
    }
}
