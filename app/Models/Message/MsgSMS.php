<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 5:12 PM
 */

namespace App\Models\Message;


use App\Components\HttpUtil;
use App\Components\PFException;
use Illuminate\Support\Facades\Log;

class MsgSMS
{
    /**
     * 自动重试次数
     */
    const AUTO_RETRY = 3;

    /**
     * 发送短信 昊博  http://www.hb-media.cn/
     * http://101.227.68.49:8888
     * @param type $mobile
     * @param type $content
     * @return bool|string
     */
    public static function sendHaobo($mobile, $content, $sign = '大圣分期')
    {
        if (strpos($content, "【{$sign}】") === false) {
            $content = "【{$sign}】" . $content;
        }
        $content = str_replace(array("[{$sign}]", ' ', ' '), array("【{$sign}】", '', ''), $content);

        $post_data = array(
            'un' => env("HAOBO_USER"),
            'pw' => env("HAOBO_PASSWD"),
            'da' => $mobile,
            'sm' => bin2hex(iconv("UTf-8", "GB2312", $content)),
            'dc' => 15,
            'rd' => 0,
        );
        $tmpArr = array();
        foreach ($post_data as $k => $v) {
            $tmpArr[] = "{$k}={$v}";
        }
        try {
            $res = HttpUtil::doPost(env("HAOBO_SEND_URL"), array('request' => implode('&', $tmpArr)), false);
            if (strpos($res, 'id=') !== false) {
                $ret = substr($res, 3, strlen($res) - 3);
            } else {
                throw new PFException('发送失败', ERR_SYS_UNKNOWN);
            }
        } catch (PFException $e) {
            $ret = ERR_SMS_FAIL;
        }
        return $ret;
    }

    /**
     * 数米渠道发送短信
     * @param type $mobile
     * @param type $content
     * @param string $sign
     * @return int|string
     * @throws PFException
     */
    public static function sendShumi($mobile, $content, $sign = '大圣分期')
    {

        if (strpos($content, "【{$sign}】") === false) {
            $content = "【{$sign}】" . $content;
        }
        if (strpos($content, "退订") === false) {
            $content .= " 退订回复N";
        }
        $content = str_replace(array("[{$sign}]",), array("【{$sign}】",), $content);
        $time = date('YmdHis');
        $password = env('SHUMI_PASSWORD');
        $post_data = array(
            'userid' => env('SHUMI_USER'),
            'timespan' => $time,
            'pwd' => strtoupper(md5("{$password}{$time}")),
            'mobile' => $mobile,
            'msgfmt' => 'UTF8',
            'content' => base64_encode($content),
        );
        $tmpArr = array();
        foreach ($post_data as $k => $v) {
            $tmpArr[] = "{$k}={$v}";
        }

        try {
            $ret = HttpUtil::doPost(env('SHUMI_URL'), array('request' => implode('&', $tmpArr)), false);
        } catch (\Exception $e) {
            $ret = ERR_SMS_FAIL;
        }
        \Yii::log("sms-shumi.\nreceiver:{$mobile}.\ncontent:{$content}\nreturn:" . print_r($ret, true), 'trace', 'sms.mail');
        return $ret;
    }
    
    /**
     * 短信发送
     * @param $phone
     * @param $content
     * @return bool|string
     * @throws PFException
     */
    public static function sendSMS($phone, $content)
    {
        try {
            $smsPlat = env('SMS_PLAT', 2);
            if ($smsPlat == 1) {
                $ret = self::sendHaobo($phone, $content);
            } elseif ($smsPlat == 2) {
                $ret = self::sendShumi($phone, $content);
            }
            if ($ret == ERR_SMS_FAIL) {
                throw new PFException("发送失败:" . $ret, ERR_SYS_PARAM);
            }
            return $ret;
        } catch (PFException $exception) {
            throw new PFException($exception->getMessage(), $exception->getCode());
        }
    }
}
