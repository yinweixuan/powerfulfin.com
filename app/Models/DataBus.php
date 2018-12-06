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
use Mobile_Detect;

class DataBus
{
    private static $data = [];

    private static function init()
    {
        self::$data['ua'] = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        self::$data['request'] = $_REQUEST;
        self::$data['uid'] = self::checkCookie();
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
    }

    public static function get($key)
    {
        if (empty(self::$data)) {
            self::init();
        }
        if (isset(self::$data[$key])) {
            return self::$data[$key];
        } else {
            return self::$data;
        }
    }

    public static function isLogin()
    {
        return true;
    }

    public static function checkCookie()
    {
        return true;
    }
}
