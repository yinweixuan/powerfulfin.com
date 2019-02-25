<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 5:05 PM
 */

namespace App\Models\Server;


use App\Components\CheckUtil;
use App\Components\PFException;
use App\Models\Message\MsgInit;
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
        $redisKey = 'PHONE_CODE_' . $phone . '_' . md5(ip2long($ip));
        if (!Redis::exists($redisKey)) {
            throw new PFException(ERR_VERIFY_CODE_CONTENT . ':已失效', ERR_VERIFY_CODE);
        }
        $data = Redis::get($redisKey);
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
        $limitKey = 'PHONE_CODE_LIMIT_' . $phone . '_' . md5(ip2long($ip));
        if (Redis::exists($limitKey)) {
            throw new PFException(ERR_VERIFY_CODE_TOO_FREQUENT_CONTENT . ":", ERR_VERIFY_CODE_TOO_FREQUENT);
        }
        $redisKey = 'PHONE_CODE_' . $phone . '_' . md5(ip2long($ip));
        if (Redis::exists($redisKey)) {
            $params = json_decode(Redis::get($redisKey), true);
            if ($params['number'] > 5) {
                Redis::set($limitKey, 1, 30 * 60);
                throw new PFException(ERR_VERIFY_CODE_TOO_FREQUENT_CONTENT, ERR_VERIFY_CODE_TOO_FREQUENT);
            } else {
                $params['number']++;
                Redis::set($redisKey, json_encode($params), 5 * 60);
            }
        } else {
            $params = [
                'code' => self::createCode(),
                'number' => 1
            ];
            Redis::set($redisKey, json_encode($params), 5 * 60);
        }
        try {
            MsgInit::sendMsgQueue(MsgInit::SEND_MSG_TYPE_SMS, $uid, $phone, 'verify_code', [], [$params['code']]);
        } catch (PFException $exception) {
            throw new PFException($exception->getMessage(), $exception->getCode());
        }
    }
}
