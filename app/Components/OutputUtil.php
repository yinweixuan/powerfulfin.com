<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 3:27 PM
 */

namespace App\Components;


use App\Models\DataBus;
use Illuminate\Support\Facades\Log;

class OutputUtil
{
    /**
     * 消息结构体
     * @var array
     */
    private static $msgData = [
        'code' => '',
        'msg' => '',
        'data' => [],
        'isLogin' => false,
        'time' => '',
    ];

    /**
     * 正确消息输出
     * @param string $msg
     * @param $code
     * @param array $data
     */
    public static function info($msg = '', $code, array $data = [])
    {
        self::$msgData['code'] = $code;
        self::$msgData['msg'] = $msg;
        if (empty($data)) {
            self::$msgData['data'] = new \stdClass();
        } else {
            self::$msgData['data'] = $data;
        }
        self::$msgData['isLogin'] = DataBus::isLogin();
        self::$msgData['time'] = DataBus::get('ctime');
        $ret = json_encode(self::$msgData);
        echo $ret;
    }

    /**
     * 异常消息输出并终止
     * @param string $msg
     * @param $code
     * @param array $data
     */
    public static function err($msg = "", $code, array $data = [])
    {
        self::$msgData['code'] = $code;
        self::$msgData['msg'] = $msg;
        if (empty($data)) {
            self::$msgData['data'] = new \stdClass();
        } else {
            self::$msgData['data'] = $data;
        }
        self::$msgData['isLogin'] = DataBus::isLogin();
        self::$msgData['time'] = DataBus::get('ctime');
        $ret = json_encode(self::$msgData);
        Log::error($ret);
        echo $ret;
        exit;
    }


}
