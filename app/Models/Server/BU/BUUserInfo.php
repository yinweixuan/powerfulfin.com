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

    public static function getUConfig($user, $data, $part)
    {
        self::$user = $user;
        self::$data = $data;
        switch ($part) {
            case 1:
                $result = self::getUserReal();
                break;
            case 2:
                $result = self::getUserBanks();
                break;
            case 3:
                break;
            case 4:
                break;
            case 5:
                break;
            case 6:
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

    public static function getUserReal()
    {
        $order = self::$user['id'] . DataBus::orderid();

        try {
            $info = ARPFUsersAuthLog::getUserAuthSuccessLast(self::$user['id']);
            if (!empty($info)) {
                $verified = 1;
            } else {
                $verified = 0;
            }
        } catch (PFException $exception) {
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

    public static function getUserBanks()
    {

    }
}
