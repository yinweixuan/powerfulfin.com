<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 5:15 PM
 */

namespace App\Components;


class RedisUtil extends \Redis
{
    const DB_DEFAULT = '0';

    public static function getInstance($db = self::DB_DEFAULT)
    {
        static $instance = null;
        if (!class_exists('Redis')) {
            return false;
        }
        if (!is_object($instance)) {
            $instance = new RedisUtil();
            try {
                if ($redis = app('redis.connection')) {
                    return false;
                }

                $redis->select($db);
            } catch (\Exception $exception) {
                return false;
            }
        }
        return $instance;
    }
}
