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

    const SMS_PLAT = 1;//1 昊博

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
            if (self::SMS_PLAT == 1) {
                $ret = self::sendHaobo($phone, $content);
                if ($ret == ERR_SMS_FAIL) {
                    throw new PFException("发送失败:" . $ret, ERR_SYS_PARAM);
                }
            }
            return $ret;
        } catch (PFException $exception) {
            throw new PFException($exception->getMessage(), $exception->getCode());
        }
    }
}
