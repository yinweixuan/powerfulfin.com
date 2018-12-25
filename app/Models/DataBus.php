<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 2:55 PM
 */

namespace App\Models;

require PATH_VENDOR . '/autoload.php';

use App\Components\AreaUtil;
use App\Components\CookieUtil;
use App\Models\ActiveRecord\ARPfUsers;
use Illuminate\Support\Facades\Cookie;
use Mobile_Detect;

class DataBus
{
    protected static $data = [];
    const COOKIE_KEY = 'powerfulfin_user';

    protected static function init()
    {
        self::$data['ua'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        self::$data['request'] = $_REQUEST;
        self::$data['ctime'] = date('Y-m-d H:i:s');
        self::$data['date'] = date('Y-m-d');
        self::$data['curtime'] = time();
        self::$data['ip_addr'] = AreaUtil::getIp();
        self::$data['ip'] = ip2long(self::$data['ip_addr']);
        if (!is_int(self::$data['ip'])) {
            self::$data['ip'] = ip2long('127.0.0.1');
        }
        self::$data['isLogin'] = self::isLogin();
        $detect = new Mobile_Detect();
        if ($detect->isAndroidOS()) {
            self::$data['plat'] = 2;
        } else if ($detect->isIOS()) {
            self::$data['plat'] = 1;
        } else if ($detect->isMobile()) {
            self::$data['plat'] = 3;
        } else {
            self::$data['plat'] = 0;
        }
        if (isset($_SERVER['HTTP_KZUA'])) {
            self::$data['http_kzua'] = $_SERVER['HTTP_KZUA'];
        }
        self::$data['cookie'] = $_COOKIE;
        self::$data['isMobile'] = $detect->isMobile();
        $checkCookie = self::checkCookie();
        self::$data['uid'] = $checkCookie['uid'];
        self::$data['phone'] = $checkCookie['phone'];
        self::$data['username'] = $checkCookie['username'];
        self::$data['user'] = self::getUserInfo();
    }

    public static function get($key = null)
    {
        if (empty(self::$data)) {
            self::init();
        }

        if (array_key_exists($key, self::$data)) {
            return self::$data[$key];
        } else if ($key === null) {
            return self::$data;
        } else {
            return null;
        }
    }

    public static function getUserInfo()
    {
        $uid = self::get('uid');
        if ($uid < 1) {
            return false;
        }
        return ARPfUsers::getUserInfoByID($uid);
    }

    public static function getUid()
    {
        if (!self::get('uid')) {
            return 0;
        } else {
            return self::get('uid');
        }
    }


    public static function isLogin()
    {
        return self::getUid() ? true : false;
    }

    public static function checkCookie()
    {
        $cookieValue = CookieUtil::getCookie(self::COOKIE_KEY);
        if (empty($cookieValue)) {
            return ['uid' => 0, 'phone' => '', 'username' => ''];
        }
        $cookieValue = str_replace(' ', '+', $cookieValue);
        $userInfo = CookieUtil::strCode($cookieValue, 'DECODE');
        list($uid, $username, $phone, $safecv) = explode('|', $userInfo);
        return ['uid' => $uid, 'phone' => $phone, 'username' => $username];
    }

    public static function orderid()
    {
        //订单号码主体（YYYYMMDDHHIISSNNNNNNNN）
        $order_id_main = date('YmdHis') . rand(10000000, 99999999);
        //订单号码主体长度
        $order_id_len = strlen($order_id_main);
        $order_id_sum = 0;
        for ($i = 0; $i < $order_id_len; $i++) {
            $order_id_sum += (int)(substr($order_id_main, $i, 1));
        }
        //唯一订单号码（YYYYMMDDHHIISSNNNNNNNNCC）
        $order_id = $order_id_main . str_pad((100 - $order_id_sum % 100) % 100, 2, '0', STR_PAD_LEFT);

        return $order_id;
    }
}
