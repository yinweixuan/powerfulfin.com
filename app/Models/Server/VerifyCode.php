<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 5:05 PM
 */

namespace App\Models\Server;


use App\Components\PFException;
use App\Components\RedisUtil;
use Illuminate\Support\Facades\Redis;

class VerifyCode
{
    /**
     * 创建验证码
     * @param int $length
     * @return string
     */
    public static function createCode($length = 6)
    {
        $ret = '';
        for ($i = 0; $i < $length; $i++) {
            $ret .= rand(0, 9);
        }
        return $ret;
    }

    /**
     * 检查验证码是否正确
     * @param $phone 手机号
     * @param $code 短信验证码
     * @return bool
     * @throws PFException
     */
    public static function checkVerifyCode($phone, $ip, $code)
    {
        $redis = RedisUtil::getInstance();
        $redisKey = 'PHONE_CODE_' . $phone . '_' . md5(ip2long($ip));
        if (!$redis->exists($redisKey)) {
            throw new PFException(ERR_VERIFY_CODE_CONTENT . ':已失效', ERR_VERIFY_CODE);
        }
        $data = $redis->get($redisKey);
        if ($data) {
            $params = json_decode($data, true);
            if ($params['code'] == $code) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }

    /**
     * 发送短信验证码
     * @param $phone
     * @param int $uid
     * @throws \Exception
     */
    public static function sendVerifyCode($phone, $ip, $uid = 0)
    {
        if (!CheckUtil::checkPhone($phone)) {
            throw new PFException(ERR_PHONE_FORMAT_CONTENT, ERR_PHONE_FORMAT);
        }
        $redis = RedisUtil::getInstance();
        $limitKey = 'PHONE_CODE_LIMIT_' . $phone . '_' . md5(ip2long($ip));
        if ($redis->exists($limitKey)) {
            throw new PFException(ERR_VERIFY_CODE_CONTENT . ":", ERR_VERIFY_CODE);
        }
        $redisKey = 'PHONE_CODE_' . $phone . '_' . md5(ip2long($ip));
        if ($redis->exists($redisKey)) {
            $params = json_decode($redis->get($redisKey), true);
            if ($params['number'] > 5) {
                $redis->set($limitKey, 1, 30 * 60);
                throw new PFException(ERR_VERIFY_CODE_CONTENT, ERR_VERIFY_CODE);
            } else {
                $params['number']++;
                $redis->set($redisKey, json_encode($params), 5 * 60);
            }
        } else {
            $params = [
                'code' => self::createCode(),
                'number' => 1
            ];
            $redis->set($redisKey, json_encode($params), 5 * 60);
        }
        try {
//            SmsUtil::sendSms($phone, 'verify_code', ['code' => $params['code']], self::orderid());
        } catch (\Exception $exception) {
            throw new PFException($exception->getMessage(), $exception->getCode());
        }
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
