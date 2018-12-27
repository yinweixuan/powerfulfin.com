<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/19
 * Time: 5:15 PM
 */

namespace App\Http\Controllers\App\V1;


use App\Components\AliyunOSSUtil;
use App\Components\OutputUtil;
use App\Components\PFException;
use App\Http\Controllers\App\AppController;
use App\Models\ActiveRecord\ARPFUsersAuthLog;
use App\Models\ActiveRecord\ARPFUsersPhonebook;
use App\Models\DataBus;
use App\Models\Server\BU\BUAppMobile;
use App\Models\Server\BU\BUUserInfo;
use Illuminate\Support\Facades\Input;

class UserController extends AppController
{
    private static $user = null;

    public function __construct()
    {
        parent::__construct();
        $this->checkLogin();
        self::$user = DataBus::get('user');
        if (empty(self::$user)) {
            throw new PFException("暂未获取用户登录信息，请重新登录", ERR_NOLOGIN);
        }
    }

    /**
     * 获取用户信息配置资料
     */
    public function uconfig()
    {
        try {
            $part = Input::get('part');
            if (!in_array($part, array(1, 2, 3, 4, 5, 6))) {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
            $methodType = strtolower($_SERVER['REQUEST_METHOD']);
            $data = $methodType == 'post' ? $_POST : $_GET;
            $result = BUUserInfo::getUConfig(self::$user, $data, $part);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $result);
        } catch (PFException $e) {
            OutputUtil::err($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 用户实名认证
     */
    public function userReal()
    {
        try {
            $data = Input::get();
            BUUserInfo::userReal($data, self::$user);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

    /**
     * 用户联系信息
     */
    public function userContact()
    {
        try {
            $data = Input::get();
            BUUserInfo::userContact($data, self::$user);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

    /**
     * 用户工作&学历信息
     */
    public function userWork()
    {
        try {
            $data = Input::get();
            BUUserInfo::userWork($data, self::$user);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }

    public function userLocation()
    {
        try {
            $lat = Input::get('lat');
            $lng = Input::get('lng');
            $oid = Input::get('oid');
            if (empty($lat) || empty($lng)) {
                throw new PFException(ERR_GPS_CONTENT, ERR_GPS);
            }
            $result = BUUserInfo::userLocation(DataBus::get('uid'), $lng, $lat, $oid);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $result);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode() ? $exception->getCode() : ERR_SYS_PARAM);
        }
    }


    /**
     * 获取通讯录
     */
    public function phonebook()
    {
        try {
            $mobiles = Input::get('phonebook');
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
                'uid' => self::$user['id'],
                'phonebook_count' => count($rows),
                'phonebook' => OutputUtil::json_encode($rows),
                'phone_type' => $phone_type,
                'phone_id' => BUAppMobile::getPhoneID()
            ];

            ARPFUsersPhonebook::addUserPhoneBook($info);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK);
        } catch (PFException $e) {
            OutputUtil::err(ERR_LOAN_COLLECT_MOBILE_CONTENT, ERR_LOAN_COLLECT_MOBILE);
        }
    }

    public function getUserRealInfo()
    {
        try {
            $result = BUUserInfo::getUserRealInfo(DataBus::get('uid'));
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $result);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode());
        }
    }

    public function getUserContact()
    {
        try {
            $result = BUUserInfo::getUserContact(DataBus::get('uid'));
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $result);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode());
        }
    }

    public function getUserWork()
    {
        try {
            $result = BUUserInfo::getUserWork(DataBus::get('uid'));
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $result);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode());
        }
    }


    public function idcardpic()
    {
        try {
            $orderId = Input::get('order');
            if (empty($orderId)) {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
            $img = [
                'idcard_information_pic_url' => '',
                'idcard_information_pic' => '',
                'idcard_national_pic_url' => '',
                'idcard_national_pic' => ''];
            $data = ARPFUsersAuthLog::getInfoTrueByOrder($orderId);
            if ($data['uid'] != DataBus::get('uid')) {
                throw new PFException(ERR_UPLOAD_CONTENT . ":请求文件非当前用户所有", ERR_UPLOAD);
            }
            if ($data) {
                $img['idcard_information_pic'] = $data['front_card'];
                $img['idcard_information_pic_url'] = AliyunOSSUtil::getAccessUrl(AliyunOSSUtil::getLoanBucket(), $data['front_card']);;
                $img['idcard_national_pic'] = $data['back_card'];;
                $img['idcard_national_pic_url'] = AliyunOSSUtil::getAccessUrl(AliyunOSSUtil::getLoanBucket(), $data['back_card']);;
            }

            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $img);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode());
        }
    }

    public function userstatus()
    {
        try {
            $info = BUUserInfo::getUserStatus(DataBus::get('uid'));
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $info);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode());
        }
    }
}
