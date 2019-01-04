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

    /**
     * 展示钱,加点分
     */
    public static function echoMoney($money, $len = 2)
    {
        $format_money = '';
        $negative = $money >= 0 ? '' : '-';
        $int_money = intval(abs($money) + 0.0001);
        $len = intval(abs($len));
        $decimal = '';//小数
        if ($len > 0) {
            $decimal = '.' . substr(sprintf('%01.' . $len . 'f', $money), -$len);
        }
        $tmp_money = strrev($int_money);
        $strlen = strlen($tmp_money);
        for ($i = 3; $i < $strlen; $i += 3) {
            $format_money .= substr($tmp_money, 0, 3) . ',';
            $tmp_money = substr($tmp_money, 3);
        }
        $format_money .= $tmp_money;
        $format_money = strrev($format_money);
        return $negative . $format_money . $decimal;
    }

    /**
     * 数字转汉字金额
     * @param $num
     * @return string
     */
    public static function cny($num)
    {
        $c1 = "零壹贰叁肆伍陆柒捌玖";
        $c2 = "分角元拾佰仟万拾佰仟亿";
        //精确到分后面就不要了，所以只留两个小数位
        $num = round($num, 2);
        //将数字转化为整数
        $num = $num * 100;
        if (strlen($num) > 10) {
            return "金额太大，请检查";
        }
        $i = 0;
        $c = "";
        while (1) {
            if ($i == 0) {
                //获取最后一位数字
                $n = substr($num, strlen($num) - 1, 1);
            } else {
                $n = $num % 10;
            }
            //每次将最后一位数字转化为中文
            $p1 = substr($c1, 3 * $n, 3);
            $p2 = substr($c2, 3 * $i, 3);
            if ($n != '0' || ($n == '0' && ($p2 == '亿' || $p2 == '万' || $p2 == '元'))) {
                $c = $p1 . $p2 . $c;
            } else {
                $c = $p1 . $c;
            }
            $i = $i + 1;
            //去掉数字最后一位了
            $num = $num / 10;
            $num = (int)$num;
            //结束循环
            if ($num == 0) {
                break;
            }
        }
        $j = 0;
        $slen = strlen($c);
        while ($j < $slen) {
            //utf8一个汉字相当3个字符
            $m = substr($c, $j, 6);
            //处理数字中很多0的情况,每次循环去掉一个汉字“零”
            if ($m == '零元' || $m == '零万' || $m == '零亿' || $m == '零零') {
                $left = substr($c, 0, $j);
                $right = substr($c, $j + 3);
                $c = $left . $right;
                $j = $j - 3;
                $slen = $slen - 3;
            }
            $j = $j + 3;
        }
        //这个是为了去掉类似23.0中最后一个“零”字
        if (substr($c, strlen($c) - 3, 3) == '零') {
            $c = substr($c, 0, strlen($c) - 3);
        }
        //将处理的汉字加上“整”
        if (empty($c)) {
            return "零元整";
        } else {
            return $c . "整";
        }
    }

    /**
     * 页面输出内容
     * @param type $word
     * @param type $limit
     * @param type $etc
     */
    public static function echoEscape($word, $limit = null, $etc = '...')
    {
        echo self::valueEscape($word, $limit, $etc);
    }

    /**
     * 获得展示的内容,不输出
     * @param type $word
     * @param type $limit,截字
     * @param type $etc, 截字后加上后面的内容
     */
    public static function valueEscape($word, $limit = null, $etc = '...')
    {
        $charset = 'utf-8';
        $word = htmlspecialchars($word);
        if ($limit !== null && is_numeric($limit)) {
            $len = mb_strlen($word, $charset);
            if ($len>$limit) {
                $ret = mb_substr($word, 0, $limit, $charset) . $etc;
            } else {
                $ret = $word;
            }
        } else {
            $ret = $word;
        }
        return $ret;
    }

    /**
     * 展示手机类型
     * @param $type
     */
    public static function valuePhoneType($type)
    {
        $ret = '未知';
        switch ($type) {
            case PHONE_TYPE_ANDROID:
            case 2:
                $ret = 'Android';
                break;
            case PHONE_TYPE_IOS:
            case 1:
                $ret = 'iOS';
                break;
            default:
                $ret = '未知';
                break;
        }
        return $ret;
    }

    /**
     * 展示工作状态
     * @param $workStatus
     */
    public static function valueWorkStatus($workStatus)
    {
        $ret = '未知';
        switch ($workStatus) {
            case 1:
                $ret = '在职';break;
            case 2:
                $ret = '学生';break;
            case 3:
                $ret = '待业';break;
            default:
                $ret = '未知';break;
        }
        return $ret;
    }

    /**
     * 获取图片展示地址
     * @param $object
     */
    public static function valueImg($object, $bucket = null)
    {
        if (empty($object)) {
            return '';
        }
        if (empty($bucket)) {
            $bucket = AliyunOSSUtil::getLoanBucket();
        }
        try {
            $url = AliyunOSSUtil::getAccessUrl($bucket, $object);
            return $url;
        } catch (\Exception $e) {
            return '';
        }
    }
}
