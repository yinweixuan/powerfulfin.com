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

class LoginController extends AppController {

    public function login() {
        try {
            $phone = Input::get('phone');
            $vcode = Input::get('vcode');
            $password = Input::get('password');
            $ip = DataBus::get('ip');
            if (!CheckUtil::checkPhone($phone)) {
                throw new PFException(ERR_PHONE_FORMAT_CONTENT, ERR_PHONE_FORMAT);
            }
            if ($vcode && $ip) {
//                if (!VerifyCode::checkVerifyCode($phone, $ip, $vcode)) {
//                    throw new PFException(ERR_VCODE_CHECK_CONTENT, ERR_VCODE_CHECK);
//                }
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
            } elseif ($password) {
                $userInfo = ARPfUsers::getUserInfoByPhone($phone);
                if (!$userInfo) {
                    throw new PFException(ERR_USER_EXIST_CONTENT, ERR_USER_EXIST);
                }
                $encrypted_password = $this->getEncryptedPassword($password);
                if ($encrypted_password != $userInfo['password']) {
                    throw new PFException(ERR_LOGIN_CONTENT, ERR_LOGIN);
                }
            } else {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
            $login_log = [];
            $login_log['uid'] = $userInfo['id'];
            $login_log['login_time'] = date('Y-m-d H:i:s');
            $login_log['ip'] = $ip;
            $login_log['device'] = '';
            if (DataBus::get('plat') == 1) {
                $login_log['phone_type'] = 'IOS';
            } elseif (DataBus::get('plat') == 2) {
                $login_log['phone_type'] = 'Android';
            } else {
                $login_log['phone_type'] = 'unknown';
            }
            \App\Models\ActiveRecord\ARPFUsersLogin::add($login_log);
            $data = [];
            $data['uid'] = $userInfo['id'];
            $data['phone'] = $phone;
            $data['name'] = $userInfo['username'];
            $data['has_password'] = $userInfo['password'] ? '1' : '0';
            $cookie = self::getCookie($userInfo);
            CookieUtil::setCookie(CookieUtil::db_cookiepre . '_' . DataBus::COOKIE_KEY, $cookie[CookieUtil::db_cookiepre . '_' . DataBus::COOKIE_KEY], 86400 * 365);
            OutputUtil::out($data);
        } catch (PFException $exception) {
            OutputUtil::out($exception);
        }
    }

    public function verifycode() {
        try {
            $phone = Input::get('phone');
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

    private function getCookie(array $userInfo) {
        if (empty($userInfo)) {
            $cookie = [];
        } else {
            $strCode = $userInfo['id'] . '|' . $userInfo['username'] . '|' . $userInfo['phone'] . '|' . CookieUtil::createSafecv();
            $cookie = [CookieUtil::db_cookiepre . '_' . DataBus::COOKIE_KEY => CookieUtil::strCode($strCode)];
        }
        return $cookie;
    }

    public function logout() {
        try {
            $uid = DataBus::getUid();
            if (!$uid) {
                throw new PFException(ERR_NOLOGIN_CONTENT, ERR_NOLOGIN);
            }
            $login_log = [];
            $login_log['uid'] = $uid;
            $login_log['logout_time'] = date('Y-m-d H:i:s');
            $login_log['ip'] = DataBus::get('ip');
            $login_log['device'] = '';
            if (DataBus::get('plat') == 1) {
                $login_log['phone_type'] = 'IOS';
            } elseif (DataBus::get('plat') == 2) {
                $login_log['phone_type'] = 'Android';
            } else {
                $login_log['phone_type'] = 'unknown';
            }
            \App\Models\ActiveRecord\ARPFUsersLogin::add($login_log);
            CookieUtil::setCookie(CookieUtil::db_cookiepre . '_' . DataBus::COOKIE_KEY, '');
            OutputUtil::out();
        } catch (PFException $exception) {
            OutputUtil::out($exception);
        }
    }

    public function setPassword() {
        try {
            $uid = DataBus::getUid();
            if (!$uid) {
                throw new PFException(ERR_NOLOGIN_CONTENT, ERR_NOLOGIN);
            }
            $password_old = Input::get('old_password');
            $password_new = Input::get('new_password');
            if (!$password_new) {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
            if (strlen($password_new) < 8 || strlen($password_new) > 20) {
                throw new PFException(ERR_PASSWORD_FORMAT_CONTENT, ERR_PASSWORD_FORMAT);
            }
            $user = ARPfUsers::getUserInfoByID($uid);
            if ($user['password']) {
                if (!$password_old || $user['password'] != $this->getEncryptedPassword($password_old)) {
                    throw new PFException(ERR_PASSWORD_CONTENT, ERR_PASSWORD);
                }
            }
            $update = [];
            $update['password'] = $this->getEncryptedPassword($password_new);
            ARPfUsers::updateUserInfo($uid, $update);
            $data = [];
            $data['uid'] = $user['id'];
            $data['phone'] = $user['phone'];
            $data['name'] = $user['username'];
            $data['has_password'] = '1';
            $cookie = self::getCookie($user);
            CookieUtil::setCookie(CookieUtil::db_cookiepre . '_' . DataBus::COOKIE_KEY, $cookie[CookieUtil::db_cookiepre . '_' . DataBus::COOKIE_KEY], 86400 * 365);
            OutputUtil::out($data);
        } catch (PFException $exception) {
            OutputUtil::out($exception);
        }
    }

    public function getEncryptedPassword($password) {
        return strtolower(sha1(env('PASSWORD_SALT') . $password));
    }

}
