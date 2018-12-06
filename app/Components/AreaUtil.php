<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 2:56 PM
 */

namespace App\Components;


class AreaUtil
{
    /**
     * 获得ip地址的点分格式
     */
    public static function getIp()
    {
        if (isset($_SERVER['REMOTE_ADDR'])) {
            return $_SERVER['REMOTE_ADDR'];
        } else {
            return '127.0.0.1';
        }
    }
}
