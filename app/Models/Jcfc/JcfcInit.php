<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:06 PM
 */

namespace App\Models\Jcfc;


use App\Components\CryptAES;
use App\Components\HttpUtil;
use App\Components\PFException;
use Illuminate\Support\Facades\Log;

class JcfcInit
{
    private static $_instance;

    private static $_yii_log_name = 'jcfc.log';

    private static $_chlresource = '0046';

    public static $_api = '';

    private static $_channel = '0046';

    private static $_uat = true;

    public function __construct()
    {
        if (ENV_DEBUG) {
            if (self::$_uat) {
                self::$_channel = '0046';
                self::$_chlresource = '0046';
            } else {
                self::$_channel = '0046';
                self::$_chlresource = '0046';
            }
        } else {
            self::$_channel = '0046';
            self::$_chlresource = '0046';
        }
    }

    private static function getInstance()
    {
        if (!(self::$_instance instanceof self)) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }

    public static function getChannel()
    {
        self::getInstance();
        return self::$_channel;
    }

    public static function getChlresource()
    {
        self::getInstance();
        return self::$_chlresource;
    }

    private static function getDomain()
    {
        if (empty(self::$_api)) {
            throw new PFException("未获取到有效的API接口");
        }
        if (config('app.env') != "production") {
            if (self::$_uat) {
                return 'http://class.jcfc.cn:8090/iLoan/servlet/JSONReqServlet';
            } else {
                return 'http://test.jcfc.cn:8090/iLoan/servlet/JSONReqServlet';
            }
        } else {
            return 'https://cmisapp.jcfc.cn:8090/iLoan/servlet/JSONReqServlet';
        }
    }

    /**
     * 通信请求，POST+加密
     * @param array $params
     * @return string
     * @throws PFException
     */
    public static function httpRequest($params = array())
    {
        ini_set('memory_limit', '20480M');
        self::getInstance();
        try {
            $domain = self::getDomain();
            Log::info('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . '][请求参数JSON化]:' . json_encode($params));
            $jcfcBody = self::getSendMess($params);
            Log::info('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . '][加密请求报文]:' . $jcfcBody);
            $request = self::getHead($jcfcBody);
            Log::info('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . '][加密请求完整报文]:' . $request);
            if (self::$_api == 'CF201009') {
                $timeout = 60000;
            } else {
                $timeout = 6000;
            }
            $optArr = array('request' => 'jsonString=' . $request, 'timeout' => $timeout);
            Log::info('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . '][请求报文设置]:' . json_encode($optArr));
            $result = HttpUtil::doPost($domain, $optArr);
            Log::info('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . '][HTTP请求结果]:' . $result);
            return $result;
        } catch (PFException $exception) {
            Log::info('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . '][ERROR]:' . $exception->getMessage());
            throw new PFException($exception->getMessage());
        }
    }

    /**
     * 获取json报文头
     */
    private static function getHead($jcfcBody)
    {
        $string = array(
            'sysheader' => array(
                'accessType' => 0,
                'currentBusinessCode' => self::$_api,
                'responseFormat' => 'JSON',
                'channel' => self::$_channel,
                'reqTime' => date('Y-m-d H:i:s'),
                "version" => "1.0",
            ),
            'appheader' => array(
                'security' => true,
            ),
            'reqdata' => array(
                'jcfcBody' => $jcfcBody,
            ),
        );
        return json_encode($string);
    }

    /**
     * 获取加密签名
     * @param $params
     * @return string
     * @throws PFException
     */
    private static function getSendMess($params)
    {
        if (empty($params)) {
            throw new PFException('上送参数不能为空');
        }

        $paramsJson = json_encode($params);
        if (empty($paramsJson)) {
            throw new PFException("上送json报文不能为空");
        }

        $sendBody = self::encryptSign($paramsJson);
        return $sendBody;
    }

    /**
     * 签名
     * @param $paramsJson
     * @return string
     */
    private static function encryptSign($paramsJson)
    {
        $signStr = self::sign($paramsJson);
        $encryptStr = self::encrypt($paramsJson);
        $sign = $signStr . "|jcfc|" . $encryptStr;
        return $sign;
    }

    /**
     * RSA签名
     * @param $content 待签名数据
     * @return mixed|string 签名值
     */
    private static function sign($content)
    {
        if (config('app.env') != "production") {
            if (self::$_uat) {
                $rsa = dirname(__FILE__) . '/rsa/uat/rsa_private.pem';
            } else {
                $rsa = dirname(__FILE__) . '/rsa/test/rsa_private.pem';
            }
        } else {
            $rsa = dirname(__FILE__) . '/rsa/online/rsa_private.pem';
        }
        $privateKey = openssl_pkey_get_private(file_get_contents($rsa));
        openssl_sign($content, $out, $privateKey, OPENSSL_ALGO_SHA1);
        $encrypted = $out;
        $encrypted = base64_encode($encrypted);
        $encrypted = str_replace('+', '%2B', $encrypted);
        return $encrypted;
    }

    /**
     * AES 加密
     * @param $content
     * @return string
     */
    private static function encrypt($content)
    {
        if (config('app.env') != "production") {
            if (self::$_uat) {
                $encrypt = require dirname(__FILE__) . '/rsa/uat/private.php';
            } else {
                $encrypt = require dirname(__FILE__) . '/rsa/test/private.php';
            }
        } else {
            $encrypt = require dirname(__FILE__) . '/rsa/online/private.php';
        }
        $password = self::generateKey($encrypt);
        $aes = new CryptAES();
        $aes->set_key($password);
        $aes->require_pkcs5();
        $encryptText = $aes->encrypt($content);
        return $encryptText;
    }

    /**
     * 生产密码器
     * @param $sPassword
     * @return string
     */
    private static function generateKey($sPassword)
    {
        $password = self::getBytes_16($sPassword);

        $pwd = array(0x20, 0x20, 0x20, 0x20, 0x20, 0x20, 0x20, 0x20, 0x20, 0x20, 0x20, 0x20, 0x20, 0x20, 0x20, 0x20);

        $new = array_replace($pwd, $password);
        foreach ($new as $key => $item) {
            if ($key > 15) {
                unset($new[$key]);
            }
        }
        return self::toStr($new);
    }

    /**
     * 密码字符串转十六进制
     * @param $str
     * @return array
     */
    private static function getBytes_16($str)
    {
        $len = strlen($str);
        $bytes = array();
        for ($i = 0; $i < $len; $i++) {
            if (ord($str[$i]) >= 128) {
                $byte = ord($str[$i]) - 256;
            } else {
                $byte = ord($str[$i]);
            }
            $bytes[] = "0x" . dechex($byte);
        }
        return $bytes;
    }

    /**
     * 转字符串
     * @param $bytes
     * @return string
     */
    private static function toStr($bytes)
    {
        $str = '';
        foreach ($bytes as $ch) {
            $str .= chr($ch);
        }
        return $str;
    }
}
