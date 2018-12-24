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
    public static function info($msg = '', $code = ERR_OK, array $data = [])
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
    public static function err($msg = "", $code = ERR_SYS_UNKNOWN, array $data = [])
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

        $message = 'UID:' . DataBus::getUid() . "====error message:" . $msg . "====error code:" . $code . "====error data:" . self::json_encode($data);
        \Yii::log($message, 'echo.op');
        echo $ret;
        exit;
    }
    
    /**
     * 统一输出，统一类型
     */
    public static function out($mixed = [], $str = true) {
        if ($mixed instanceof \Exception) {
            self::err($mixed->getMessage(), $mixed->getCode());
        } else {
            if ($str && is_array($mixed)) {
                array_walk_recursive($mixed, function(&$val) {
                    if (is_numeric($val)) {
                        $val .= '';
                    }
                });
            }
            self::info(ERR_OK_CONTENT, ERR_OK, $mixed);
        }
    }

    public static function json_encode($value)
    {
        return json_encode($value);
    }

    public static function json_decode($json, $assoc = true)
    {
        return json_decode($json, $assoc);
    }

}
