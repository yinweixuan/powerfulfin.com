<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 3:50 PM
 */

namespace App\Components;


class HttpUtil
{

    const DEFAULT_COOKIE_PATH = '/';
    const DEFAULT_TIMEOUT = 30;

    /**
     * 将array变成querystring的形式
     * @param type $arr
     * @return string
     */
    public static function arrayToString($arr)
    {
        $tmpArr = array();
        foreach ($arr as $k => $v) {
            if (is_array($v) || is_object($v)) {
                $v = json_encode($v);
            }
            $tmpArr[] = "{$k}={$v}";
        }
        $str = implode('&', $tmpArr);
        return $str;
    }

    /**
     * 需要登录的操作,登录后跳转到原页面
     */
    public static function goLogin()
    {
        $url = UrlUtil::get('login', array(), DOMAIN_WEB);
        if (strpos($url, '?') === false) {
            $url .= '?';
        } else {
            $url .= '&';
        }
        $url .= 'jumpurl=' . urlencode(("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']));
        self::goUrl($url);
    }

    /**
     * 跳转地址
     * @param $url
     */
    public static function goUrl($url)
    {
        header("Location: {$url}");
    }

    /**
     * 跳转地址
     * @param $url
     */
    public static function go301($url)
    {
        header("http/1.1 301 moved permanently");
        header("Location: {$url}");
    }

    private static $_isHttp = null;

    /**
     * 判断当前请求是http请求还是cli
     */
    public static function isHttp()
    {
        if (self::$_isHttp !== null) {
            return self::$_isHttp;
        }
        if (php_sapi_name() == 'cli') {
            self::$_isHttp = false;
        } else {
            self::$_isHttp = true;
        }
        return self::$_isHttp;
    }

    /**
     * 替换url的host为指定的
     * @param type $url
     * @param type $host
     * @return type|string
     */
    public static function replaceHost($url, $host)
    {
        $ret = '';
        if (strpos($url, 'http') === false) {
            $ret = "http://{$host}{$url}";
        } else if (strpos($url, 'http') === 0) {
            $tmpArr = explode('/', $url);
            if (count($tmpArr) < 2) {
                $ret = $url;
            } else {
                $tmpArr[2] = $host;
                $ret = implode('/', $tmpArr);
            }
        } else {
            $ret = $url;
        }
        return $ret;
    }

    /**
     * 将当前请求原样转发到指定的机器和ip
     * @param type $url 要转发到的url,可以带host也可以不带
     * @param string $host 要转发到的host
     * @param string $ip 要指定的ip
     * @param bool $needReturn
     * @return string
     * @throws PFException
     */
    public static function transfer($url, $host = '', $ip = '', $needReturn = false)
    {
        $optArr = array('header' => array(),);
        //设置代理和域名
        if ($host && $ip) {
            //替换url中的host部分为ip
            $url = self::replaceHost($url, $ip);
            //指定host
            $optArr['header'][] = "Host: {$host}";
            $optArr['header'][] = "Hostname: {$host}";
        } else if ($host) {
            $url = self::replaceHost($url, $host);
        } else if ($ip) {
            $url = self::replaceHost($url, $ip);
        }
        //设置UA,referer,cookie
        $optArr['header'][] = "User-Agent: {$_SERVER['HTTP_USER_AGENT']}";
        if ($_SERVER['HTTP_REFERER']) {
            $optArr['header'][] = "Referer: {$_SERVER['HTTP_REFERER']}";
        }
        $optArr['header'][] = "Cookie: {$_SERVER['HTTP_COOKIE']}";
        $optArr['header'][] = "Connection: keep-alive";
        $optArr['header'][] = "Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8";
        $optArr['header'][] = "Upgrade-Insecure-Requests: 1";
        $optArr['header'][] = "DNT:1";
        $optArr['header'][] = "Accept-Language: zh-CN,zh;q=0.8,en-GB;q=0.6,en;q=0.4,en-US;q=0.2";
        if (isset($_SERVER['QUERY_STRING']) && $_SERVER['QUERY_STRING']) {
            if (strpos($url, '?') === false) {
                $url .= "?{$_SERVER['QUERY_STRING']}";
            } else {
                $url .= "&{$_SERVER['QUERY_STRING']}";
            }
        }
        if (strtolower($_SERVER['REQUEST_METHOD']) == 'post') {
            $optArr['request'] = file_get_contents('php://input');
            $ret = self::doPost($url, $optArr);
        } else {
            $ret = self::doGet($url, $optArr);
        }
        if (!$needReturn) {
            echo $ret;
        }
        return $ret;
    }

    /**
     * post请求
     * @param string $url
     * @param array $optArr (
     *              'proxy'=>"http://proxy.xxx:80"
     *              'cookie'=>''
     *              'request'=>'a=11&b=22'
     *              'header'=>array
     *              )
     * @param bool $needThrow
     * @param bool $needRetry
     * @return string
     * @throws PFException
     * @PFException PFException cur
     */
    public static function doPost($url, $optArr = array(), $needThrow = true, $needRetry = true)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, false);
        if (isset($optArr['ua'])) {
            curl_setopt($ch, CURLOPT_USERAGENT, $optArr['ua']);
        } else {
            curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36');
        }
        if (isset($optArr['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $optArr['referer']);
        }
        if (isset($optArr['header'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $optArr['header']);
        }
        $timeOut = self::DEFAULT_TIMEOUT;
        if (isset($optArr['timeout'])) {
            $timeOut = intval($optArr['timeout']);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut); //conn timeout
        //    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 1); //conn timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut); //execute timeout
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        if (isset($optArr['cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $optArr['cookie']);
        }
        if (isset($optArr['request'])) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $optArr['request']);
        }
        if (isset($optArr['proxy'])) {
            curl_setopt($ch, CURLOPT_PROXY, $optArr['proxy']);
        }

        $tryCnt = 1;
        $success = true;
        do {
            $data = curl_exec($ch);
            $errno = curl_errno($ch);
            if ($errno != 0) {
                usleep(10000);
                $success = false;
                if ($tryCnt >= 3) {
                    $error = curl_error($ch);
                    $message = "curl erron:{$errno},error:{$error},url:{$url}";
                    curl_close($ch);
                    throw new PFException($message, $errno);
                }
            } else {
                $success = true;
            }
        } while (!$success && $needRetry && $tryCnt++ < 3);

        $errno = curl_errno($ch);
        if ($errno != 0) {
            $error = curl_error($ch);
            $message = "curl erron:{$errno},error:{$error},url:{$url}";
            curl_close($ch);
            throw new PFException($message, $errno);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($needThrow && $httpCode != 200) {
            $message = "http code:{$httpCode},url:{$url}";
            curl_close($ch);
            throw new PFException($message, $httpCode);
        }
        curl_close($ch);
        return $data;
    }

    /**
     * get请求
     * @param string $url
     * @param array $optionArray (
     *              'proxy'=>"http://proxy.xxx:80"
     *              )
     * @param bool $needRetry
     * @return string
     * @throws PFException
     * @PFException PFException cur
     */
    public static function doGet($url, $optionArray = array(), $needRetry = true)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, false); //CURLOPT_HEADER
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 6.1) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/31.0.1650.57 Safari/537.36');
        if (isset($optionArray['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $optionArray['referer']);
        }
        $timeOut = self::DEFAULT_TIMEOUT;
        if (isset($optionArray['timeout'])) {
            $timeOut = intval($optionArray['timeout']);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut); //conn timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut); //execute timeout
        if (isset($optionArray['proxy'])) {
            curl_setopt($ch, CURLOPT_PROXY, $optionArray['proxy']);
        }
        if (isset($optionArray['header'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $optionArray['header']);
        }
        if (isset($optionArray['cookie'])) {
            curl_setopt($ch, CURLOPT_COOKIE, $optionArray['cookie']);
        }
        if (isset($optionArray['raw'])) {
            curl_setopt($ch, CURLOPT_HEADER, $optionArray['raw']);
        }

        if (isset($optionArray['referer'])) {
            curl_setopt($ch, CURLOPT_REFERER, $optionArray['referer']);
        }

        $tryCnt = 1;
        $success = true;
        do {
            $data = curl_exec($ch);
            $errno = curl_errno($ch);
            if ($errno != 0) {
                usleep(10000);
                $success = false;
                if ($tryCnt >= 1) {
                    $error = curl_error($ch);
                    $message = "curl erron:{$errno},error:{$error},url:{$url}";
                    curl_close($ch);
                    throw new PFException($message, $errno);
                }
            } else {
                $success = true;
            }
        } while (!$success && $needRetry && $tryCnt++ < 1);

        $errno = curl_errno($ch);
        if ($errno != 0) {
            $error = curl_error($ch);
            $message = "curl erron:{$errno},error:{$error},url:{$url}";
            curl_close($ch);
            throw new PFException($message, $errno);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        if ($httpCode != 200) {
            $message = "http code:{$httpCode},url:{$url}";
            curl_close($ch);
            throw new PFException($message, $httpCode);
        }
        curl_close($ch);
        return $data;
    }

    public static function doGetWithHeader($url, $optionArray = array())
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, true); //CURLOPT_HEADER
        //curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);


        $timeOut = self::DEFAULT_TIMEOUT;
        if (isset($optionArray['timeout'])) {
            $timeOut = intval($optionArray['timeout']);
        }

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeOut); //conn timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeOut); //execute timeout
        if (isset($optionArray['proxy'])) {
            curl_setopt($ch, CURLOPT_PROXY, $optionArray['proxy']);
        }
        if (isset($optionArray['header'])) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, $optionArray['header']);
        }
        if ($optionArray['cookie']) {
            curl_setopt($ch, CURLOPT_COOKIE, $optionArray['cookie']);
        }
        if ($optionArray['raw']) {
            curl_setopt($ch, CURLOPT_HEADER, $optionArray['raw']);
        }

        $tryCnt = 1;
        $success = true;
        do {
            $data = curl_exec($ch);
            $errno = curl_errno($ch);
            if ($errno != 0) {
                usleep(10000);
                $success = false;
                if ($tryCnt >= 1) {
                    $error = curl_error($ch);
                    $message = "curl erron:{$errno},error:{$error},url:{$url}";
                    curl_close($ch);
                    throw new PFException($message, $errno);
                }
            } else {
                $success = true;
            }
        } while (!$success && $tryCnt++ < 1);

        $errno = curl_errno($ch);
        if ($errno != 0) {
            $error = curl_error($ch);
            $message = "curl erron:{$errno},error:{$error},url:{$url}";
            curl_close($ch);
            throw new PFException($message, $errno);
        }

        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        if ($httpCode != 200) {
            $message = "http code:{$httpCode},url:{$url}";
            curl_close($ch);
            throw new PFException($message, $httpCode);
        }
        curl_close($ch);
        $result['header'] = substr($data, 0, $headerSize);
        $result['content'] = substr($data, $headerSize);
        return $result;
    }

    public static function getContent($url, $optionArray = array())
    {
        return self::doGet($url, $optionArray);
    }

    public static function parseRawData($data)
    {
        $cookie = '';
        $start = 0;
        $index = strpos($data, "\r\n\r\n");
        $header = substr($data, 0, $index);
        $strData = substr($data, $index + 4);

        do {
            $index = strpos($header, "\r\n", $start);
            if ($index !== false) {
                $line = substr($header, $start, $index - $start);
                $start = $index + 2;
            } else {
                $line = substr($header, $start);
            }
            if (0 === strncasecmp($line, "Set-Cookie: ", strlen("Set-Cookie: "))) {
                $pos = strpos($line, ";");
                $oneCookie = substr($line, strlen("Set-Cookie: "), $pos + 1 - strlen("Set-Cookie: "));
                if (strpos($oneCookie, '=EXPIRED;') === false) {
                    $cookie .= $oneCookie;
                }
            }
        } while ($index && !empty($line));
        return array('data' => $strData, 'cookie' => $cookie);
    }

    /**
     * 拉取多个url的结果
     * @param type $urlArr
     * @return
     */
    public static function multiCurl($urlArr)
    {
        $mucurl = new CMultiCurl($urlArr);
        $ret = $mucurl->ctlFlow();
        return $ret;
    }

    /**
     * 获得当前请求的地址
     * @return type
     */
    public static function getThisUrl()
    {
        return "http://{$_SERVER['SERVER_NAME']}{$_SERVER['REQUEST_URI']}";
    }

    /**
     * 是否是微信
     */
    public static function isWX()
    {
        $isWX = (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'micromessenger') !== false ? true : false);
        return $isWX;
    }

    /**
     * 是否是大圣分期
     */
    public static function isSelf()
    {
        $isSelf = (isset($_SERVER['HTTP_DS_USER_AGENT']) && stripos($_SERVER['HTTP_DS_USER_AGENT'], 'dasheng') !== false ? true : false);
        return $isSelf;
    }

    public static function getPhoneID()
    {
        return '';
    }

    public static function adminErrHtml($message, $jumpUrl)
    {
        $url = 'http://' . DOMAIN_ADMIN . '/admin/home/err';
        $info = [
            'message' => $message,
            'url' => $jumpUrl
        ];

        $url .= '?' . http_build_query($info);
        return header("Location: {$url}");
    }

    public static function adminSuccessHtml($message, $jumpUrl)
    {
        $url = 'http://' . DOMAIN_ADMIN . '/admin/home/success';
        $info = [
            'message' => $message,
            'url' => $jumpUrl
        ];

        $url .= '?' . http_build_query($info);
        return header("Location: {$url}");
    }
}
