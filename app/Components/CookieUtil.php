<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/7
 * Time: 11:11 AM
 */

namespace App\Components;

use Illuminate\Support\Facades\Cookie;

class CookieUtil
{
    const db_cookiepre = 'dw8zh';
    const db_hash = '8RnJMxV/AfOiZar1';
    const db_sitehash = 'pyYowBoaSU3/gylyOTH6YN8kx2iOdw8zh5Z2X7NHvX+xctfdotkZn8/g';
    const db_ckpath = '/';
    const db_ckdomain = DOMAIN_WEB;

    /**
     * @return array
     */
    public static function illegalChar()
    {
        return array(
            "\\", '&', ' ', "'", '"', '/', '*', ',', '<', '>', "\r", "\t", "\n", '#', '%', '?', '　', '..', '$', '{', '}', '(', ')', '+', '=', '-', '[', ']', '|', '!', '@', '^', '.', '~', '`'
        );
    }

    /**
     * @param $cookieName
     * @param $cookieValue
     * @param string $expireTime
     * @param bool $needPrefix
     * @return bool
     */
    public static function Cookie($cookieName, $cookieValue, $expireTime = 'F', $needPrefix = true)
    {
        $timestamp = time();
        $server = $_SERVER;
        $cookiePath = self::db_ckpath;
        $cookieDomain = self::db_ckdomain;

        static $sIsSecure = null;

        if ($sIsSecure === null) {
            if (!$server['REQUEST_URI'] || ($parsed = @parse_url($server['REQUEST_URI'])) === false) {
                $parsed = array();
            }
            if (isset($parsed['scheme']) && $parsed['scheme'] == 'https' || (empty($parsed['scheme']) && (isset($server['HTTP_SCHEME']) && $server['HTTP_SCHEME'] == 'https' || isset($server['HTTPS']) && $server['HTTPS'] && strtolower($server['HTTPS']) != 'off'))) {
                $sIsSecure = true;
            } else {
                $sIsSecure = false;
            }
        }

        $agent = strtolower($server['HTTP_USER_AGENT']);
        if (!($agent && preg_match('/msie ([0-9]\.[0-9]{1,2})/i', $agent) && strstr($agent, 'mac'))) {
            $isHttponly = true;
        } else {
            $isHttponly = false;
        }

        $cookieValue = str_replace("=", '', $cookieValue);
        strlen($cookieValue) > 512 && $cookieValue = substr($cookieValue, 0, 512);
        $needPrefix && $cookieName = self::CookiePre() . '_' . $cookieName;
        if ($expireTime === 'F' || $expireTime !== 0) {
            $expireTime = strtotime('+60 minute');
        } elseif ($cookieValue == '' && $expireTime == 0) {
            return Cookie::queue($cookieName, '', -1);
        }
        return Cookie::queue($cookieName, $cookieValue, $expireTime);
    }

    /**
     * 生成cookie前缀
     *
     * @global string $cookiepre
     * @global string $db_sitehash
     * @return string
     */
    public static function cookiePre()
    {
        return (self::db_cookiepre) ? self::db_cookiepre : substr(md5(self::db_sitehash), 0, 5);
    }

    /**
     * 从请求中获取cookie值
     * @param string $cookieName cookie名
     * @return string
     */
    public static function getCookie($cookieName)
    {
        $key = self::cookiePre() . '_' . $cookieName;
        if (isset($_COOKIE[$key])) {
            return $_COOKIE[$key];
        } else {
            return '';
        }
    }

    /**
     * 加密、解密字符串
     * @param $string
     * @param string $action
     * @return string
     */
    public static function strCode($string, $action = 'ENCODE')
    {
        $action != 'ENCODE' && $string = base64_decode($string);
        $code = '';
        $key = substr(md5(self::db_hash), 8, 18);
        $keyLen = strlen($key);
        $strLen = strlen($string);
        for ($i = 0; $i < $strLen; $i++) {
            $k = $i % $keyLen;
            $code .= $string[$i] ^ $key[$k];
        }
        return ($action != 'DECODE' ? base64_encode($code) : $code);
    }

    /**
     * @param int $length
     * @return string
     */
    public static function createSafecv($length = 8)
    {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $str = '';
        for ($i = 0; $i < $length; $i++) {
            $str .= $chars[mt_rand(0, strlen($chars) - 1)];
        }
        return $str;
    }

    /**
     * @param $name
     * @param $value
     * @param int $expire
     * @param null $domain
     */
    public static function setCookie($name, $value, $expire = 0, $domain = null)
    {

        if (is_null($domain)) {
            $domain = CookieUtil::db_ckdomain;
        }
        if ($expire && is_numeric($expire)) {
            $expire = time() + $expire;
        }
        setcookie($name, $value, $expire, self::db_ckpath, $domain);
    }
}
