<?php
/**
 * Created by PhpStorm.
 * User: 淘气
 * Date: 2016/10/13
 * Time: 0:12
 */
class BaofooUtils
{
    /** 生成yyyymmddHHmiss 20160921142808
     * @return bool|string
     */
    static  function trade_date(){//生成时间

        return date('YmdHis',time());

    }
    /**
     * 生成唯一订单号
     */
   static function create_uuid($prefix = ""){    //可以指定前缀
        $str = md5(uniqid(mt_rand(), true));
        $uuid  = substr($str,0,8) . '-';
        $uuid .= substr($str,8,4) . '-';
        $uuid .= substr($str,12,4) . '-';
        $uuid .= substr($str,16,4) . '-';
        $uuid .= substr($str,20,12);
        return $prefix . $uuid;
    }
}