<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/20
 * Time: 6:03 PM
 */

namespace App\Models\Server\BU;


use App\Models\ActiveRecord\ARPFMobileModel;
use App\Models\DataBus;
use Illuminate\Support\Facades\Input;

class BUAppMobile
{
    /**
     * 安卓手机统计
     */
    public static function android()
    {
        if (isset($_SERVER['HTTP_KZUA'])) {
            $kzua = $_SERVER['HTTP_KZUA'];
            $kzua_arr = explode('||', $kzua);
            $filedData = array(
                'uid' => DataBus::get('uid'),
                'unique_id' => self::getPhoneID(),
                'type' => 'Android',
                'brand' => $kzua_arr[8],
                'model' => $kzua_arr[7],
                'system' => $kzua_arr[9],
                'channel' => 0,
                'info' => $kzua . ":" . $_SERVER['HTTP_USER_AGENT'],
                'ctime' => DataBus::get('ctime'),
            );
            $result = ARPFMobileModel::addInfo($filedData);
        }
    }

    /**
     * 苹果手机统计
     */
    public static function ios()
    {
        if (isset($_SERVER['HTTP_USER_AGENT'])) {
            $userAgent = $_SERVER['HTTP_USER_AGENT'];
            $mobile = isset($_SERVER['HTTP_PAY_USER_AGENT']) ? urldecode($_SERVER['HTTP_PAY_USER_AGENT']) : $_SERVER['HTTP_USER_AGENT'];
            $filedData = array(
                'uid' => DataBus::get('uid'),
                'unique_id' => self::getPhoneID(),
                'type' => 'IOS',
                'brand' => 'Apple',
                'model' => $mobile,
                'system' => '',
                'channel' => 0,
                'info' => $userAgent,
                'ctime' => DataBus::get('ctime'),
            );
            $result = ARPFMobileModel::addInfo($filedData);
        }
    }

    public static function getPhoneID()
    {
        $plat = DataBus::get('plat');
        if ($plat == 1) {
            $phoneID = Input::get('phoneid', 0);
        } elseif ($plat == 2) {
            if (isset($_SERVER['HTTP_PFUA'])) {
                $kzua = $_SERVER['HTTP_PFUA'];
                $kzua_arr = explode('||', $kzua);
                $phoneID = $kzua_arr[5];
                if ($phoneID == 'null')
                    $phoneID = 0;
            } else {
                $phoneID = 0;
            }
        } else {
            $phoneID = 0;
        }
        return $phoneID;
    }
}
