<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/19
 * Time: 5:15 PM
 */

namespace App\Http\Controllers\App\V1;


use App\Components\OutputUtil;
use App\Components\PFException;
use App\Http\Controllers\App\AppController;
use App\Models\ActiveRecord\ARPFUsersPhonebook;
use App\Models\DataBus;
use App\Models\Server\BU\BUAppMobile;
use App\Models\Server\BU\BUUserInfo;
use Illuminate\Support\Facades\Input;

class UserController extends AppController
{
    public function uconfig()
    {
        $this->checkLogin(false);
        $user = DataBus::get('user');
        if (empty($user)) {
            OutputUtil::err("未获取用户信息", ERR_NOLOGIN);
        }

        try {
            $part = Input::get('part');
            if (!in_array($part, array(1, 2, 3, 4, 5, 6))) {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
            $methodType = strtolower($_SERVER['REQUEST_METHOD']);
            $data = $methodType == 'post' ? $_POST : $_GET;
            $result = BUUserInfo::getUConfig($user, $data, $part);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $result);
        } catch (PFException $e) {
            OutputUtil::err($e->getMessage(), $e->getCode());
        }
    }

    public function userReal()
    {
        $this->checkLogin(false);
        $user = DataBus::get('user');
        if (empty($user)) {
            OutputUtil::err("未获取用户信息", ERR_NOLOGIN);
        }
        try {
            $data = Input::get();
            BUUserInfo::userReal($data, DataBus::get("user"));
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

    public function usupply()
    {
        $this->checkLogin();
        $user = DataBus::get('user');
        if (empty($user)) {
            OutputUtil::err("未获取用户信息", ERR_NOLOGIN);
        }

        try {
            $part = Input::get('part');
            if (!in_array($part, array(1, 2, 3, 4, 5, 6))) {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
            $function = 'supplyUserStep' . $part;
            $result = BUUserInfo::$function($_POST, $user);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $result);
        } catch (PFException $e) {
            OutputUtil::err(ERR_SYS_PARAM_CONTENT, $e->getCode());
        }
    }

    /**
     * 获取通讯录
     */
    public function phonebook()
    {
        $mobiles = Input::get('phonebook');
        $this->checkLogin();
        try {
            $res = OutputUtil::json_decode($mobiles);

            $rows = array();
            foreach ($res as $r) {
                $tmp = array(
                    'firstname' => empty($r['FirstName']) ? $r['a'] : $r['FirstName'],
                    'lastname' => empty($r['LastName']) ? $r['b'] : $r['LastName'],
                );
                $tmp['email'] = ((isset($r['Email']) && $r['Email']) ? array_shift($r['Email']) : $r['d']);
                if (empty($r['Phones'])) {
                    $r['Phones'] = $r['c'];
                }
                for ($i = 0; $i < 3; $i++) {
                    $k = 'mobile' . ($i + 1);
                    if (isset($r['Phones']) && $r['Phones'] && array_key_exists($i, $r['Phones'])) {
                        $tmp[$k] = $r['Phones'][$i];
                    } else {
                        $tmp[$k] = '';
                    }
                }
                $rows[] = $tmp;
            }

            if ($this->isAndroid) {
                $phone_type = PHONE_TYPE_ANDROID;
            } elseif ($this->isKZIOS()) {
                $phone_type = PHONE_TYPE_IOS;
            } else {
                $phone_type = 'Other';
            }

            $info = [
                'uid' => DataBus::get('uid'),
                'phonebook_count' => count($rows),
                'phonebook' => OutputUtil::json_encode($rows),
                'phone_type' => $phone_type,
                'phone_id' => BUAppMobile::getPhoneID()
            ];

            ARPFUsersPhonebook::addUserPhoneBook($info);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK);
        } catch (PFException $e) {
            OutputUtil::err(ERR_LOAN_COLLECT_MOBILE, $e->getMessage());
        }
    }
}
