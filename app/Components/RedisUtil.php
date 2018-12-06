<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 5:15 PM
 */

namespace App\Components;

/**
 * redis
 * Class RedisUtil
 * @package App\Components
 */
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
            $redisConfig = config('database.redis.default');
            if (empty($redisConfig)) {
                return false;
            }
            try {
                if ($instance->connect($redisConfig['host'], $redisConfig['port'], $redisConfig['timeout']) == false) {
                    return false;
                }
                if (!empty($redisConfig['password'])) {
                    if ($instance->auth($redisConfig['password']) == false) {
                        return false;
                    }
                }
                $instance->select($db);
            } catch (\Exception $exception) {
                return false;
            }
        }
        return $instance;
    }
}
