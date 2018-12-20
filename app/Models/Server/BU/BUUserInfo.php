<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/19
 * Time: 5:19 PM
 */

namespace App\Models\Server\BU;


use App\Components\PFException;
use App\Models\ActiveRecord\ARPFUsersAuthLog;
use App\Models\DataBus;

class BUUserInfo
{
    private static $user;
    private static $data;

    /**
     * 获取用户配置
     * @param $user
     * @param $data
     * @param $part
     * @return array|bool
     * @throws PFException
     */
    public static function getUConfig($user, $data, $part)
    {
        self::$user = $user;
        self::$data = $data;
        switch ($part) {
            case 1:
                $result = self::getUserRealConfig();
                break;
            case 2:
                $result = self::getUserBanksConfig();
                break;
            case 3:
                $result = self::getUserContactConfig();
                break;
            case 4:
                $result = [];
                break;
            case 5:
                $result = [];
                break;
            case 6:
                $result = [];
                break;
            default:
                $result = false;
                break;
        }
        if ($result) {
            return $result;
        } else {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }
    }

    /**
     * 获取云慧眼认证配置
     * @return array
     */
    public static function getUserRealConfig()
    {
        $order = self::$user['id'] . '_' . DataBus::orderid();

        $info = ARPFUsersAuthLog::getUserAuthSuccessLast(self::$user['id']);
        if (!empty($info)) {
            $verified = 1;
        } else {
            $verified = 0;
        }

        return $data = array(
            'key' => env('UDCREDIT_MERCHANT_KEY'),
            'order' => $order,
            'notify_url' => DOMAIN_INNER . '/udcredit/notify',
            'user_id' => ARPFUsersAuthLog::USER_ID_SUFFIX . self::$user['id'],
            'safe_mode' => ARPFUsersAuthLog::SAFE_MODE_HIGH,
            'verified' => $verified,
        );
    }

    /**
     * 获取银行卡配置
     * @return array
     */
    public static function getUserBanksConfig()
    {
        $banks = BUBanks::getBanksInfo();
        foreach ($banks as &$bank) {
            unset($bank['jcfc_bank_code']);
            unset($bank['jcfc_bank_code_tl']);
        }
        return array('bank_list' => array_values($banks));
    }

    /**
     * 获取联系人配置
     * @return array
     */
    public static function getUserContactConfig()
    {
        $data = [
            'relations' => ['父母', '配偶', '监护人', '子女'],
            'housing_situation' => ['宿舍', '租房', '与父母同住', '与其他亲属同住', '自有住房', '其他'],
            'marital_status' => ['已婚有子女', '已婚无子女', '未婚', '离异', '其他'],
        ];
        return $data;
    }
}
