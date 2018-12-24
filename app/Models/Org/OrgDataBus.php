<?php
/**
 * 继承DataBus,提供机构老师端数据总线.
 * User: haoxiang
 * Date: 2018/12/22
 * Time: 2:42 PM
 */
namespace App\Models\Org;

use App\Components\AreaUtil;
use App\Components\CookieUtil;
use App\Models\ActiveRecord\ARPFOrgUsers;
use App\Models\DataBus;
use Illuminate\Support\Facades\Cookie;

class OrgDataBus extends DataBus
{

    const COOKIE_KEY = 'powerfulfin_org_user';      //cookie的值

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
        $checkCookie = self::checkCookie();
        self::$data['uid'] = $checkCookie['uid'];
        self::$data['phone'] = $checkCookie['username'];
        self::$data['username'] = $checkCookie['username'];
        self::$data['user'] = self::getUserInfo();

        self::$data['isLogin'] = self::isLogin();
        $detect = new \Mobile_Detect();
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

    /**
     * 判断是否登录
     * @return bool
     */
    public static function isLogin()
    {
        return self::getUid() ? true : false;
    }

    /**
     * 解析cookie
     * @return array
     */
    public static function checkCookie()
    {
        $cookieValue = Cookie::get(CookieUtil::db_cookiepre . '_' . self::COOKIE_KEY);
        if (empty($cookieValue)) {
            return ['uid' => 0, 'phone' => '', 'username' => ''];
        }
        $cookieValue = str_replace(' ', '+', $cookieValue);
        $userInfo = CookieUtil::strCode($cookieValue, 'DECODE');
        list($uid, $phone, $username, $safecv) = explode('|', $userInfo);
        $ret = ['uid' => $uid, 'phone' => $phone, 'username' => $username];
        return $ret;
    }

    public static function getUserInfo()
    {
        $uid = self::get('uid');
        if ($uid < 1) {
            return false;
        }
        $res = ARPFOrgUsers::query()->where(['org_uid' => $uid])->first();
        return $res;
    }
}
