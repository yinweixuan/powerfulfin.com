<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/19
 * Time: 3:18 PM
 */

namespace App\Components;


class ViewUtil
{
    public static $beginTime = null;
    public static $endTime = null;

    /**
     * 做整体统计的
     */
    public static function setBeginTime()
    {
        self::$beginTime = microtime(true);
        return self::$beginTime;
    }

    /**
     * 返回时间统计结果
     * @return int
     */
    public static function setEndTime($beginTime = null)
    {
        if ($beginTime !== null) {
            self::$beginTime = $beginTime;
        }
        self::$endTime = microtime(true);
        if (self::$beginTime && self::$endTime) {
            return (self::$endTime - self::$beginTime) * 1000;
        } else {
            return 0;
        }
    }
}
