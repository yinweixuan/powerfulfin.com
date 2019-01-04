<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:32 PM
 */

namespace App\Models\Server\BU;

use App\Components\RedisUtil;

/**
 * 银行卡业务处理
 * Class BUBanks
 * @package App\Models\Server
 */
class BUBanks
{
    /**
     * 读取并缓存银行卡信息
     * @return mixed
     */
    public static function getBanksInfo()
    {
        $redis = RedisUtil::getInstance();
        $redisKey = 'PF_BANKS_INFO';
        if ($redis && $redis->exists($redisKey)) {
            return json_decode($redis->get($redisKey), true);
        } else {
            $banks = config('bank');
            if ($redis) {
                $redis->set($redisKey, json_encode($banks));
            }
            return $banks;
        }
    }

    /**
     * 银行英文缩写
     * @param $bankCode
     * @return array
     */
    public static function getBankInfo($bankCode)
    {
        $banks = self::getBanksInfo();
        if (empty($banks) || empty($banks[$bankCode])) {
            return array();
        } else {
            return $banks[$bankCode];
        }
    }

    /**
     * 获取银行名称
     * @param $bankCode
     * @return mixed|string
     */
    public static function getBankName($bankCode)
    {
        $bankInfo = self::getBankInfo($bankCode);
        return !empty($bankInfo['bankname']) ? $bankInfo['bankname'] : '未知';
    }

    /**
     * 获取银行LOGO
     * @param $bankCode
     * @return mixed|string
     */
    public static function getBankLogo($bankCode)
    {
        $bankInfo = self::getBankInfo($bankCode);
        return !empty($bankInfo['logo']) ? $bankInfo['logo'] : '';
    }
}
