<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/21
 * Time: 2:46 PM
 */

namespace App\Components;


class MapUtil
{
    private static function getAccessKey()
    {
        return env("MAP_ACCESS_KEY") ? env("MAP_ACCESS_KEY") : "";

    }

    const URL_POS_INFO = 'http://api.map.baidu.com/geocoder/v2/?output=json';

    /**
     * 根据经纬度,拉取位置信息
     * @param type $lng
     * @param type $lat
     * @return
     * @throws PFException
     */
    public static function getPosInfo($lng, $lat)
    {
        $url = self::URL_POS_INFO . "&ak=" . self::getAccessKey() . "&location={$lat},{$lng}";
        $res = HttpUtil::doGet($url);
        $res = OutputUtil::json_decode($res);
        if (!isset($res['status']) || $res['status'] != 0 || !isset($res['result'])) {
            throw new PFException("获取地图信息错误");
        }
        $ret = $res['result'];
        return $ret;
    }

    const URL_IP_TO_POS = 'http://api.map.baidu.com/location/ip?output=json&coor=bd09ll';

    /**
     * 将ip地址转成坐标
     * @param type $ip
     * @param bool $returnOriginal
     * @return array|mixed|null|string
     * @throws PFException
     */
    public static function getPosByIp($ip, $returnOriginal = false)
    {
        if (is_numeric($ip)) {
            $ip = long2ip($ip);
        }
        if ($ip == '127.0.0.1') {
            return null;
        }
        $url = self::URL_IP_TO_POS . "&ak=" . self::getAccessKey() . "&ip={$ip}";
        $res = HttpUtil::doGet($url);
        $res = OutputUtil::json_decode($res);
        if (!isset($res['status']) || $res['status'] != 0 || !isset($res['content'])) {
            return null;
        }
        if (array_key_exists('point', $res['content'])) {
            if (!$returnOriginal) {
                return array('lng' => $res['content']['point']['x'], 'lat' => $res['content']['point']['y'],);
            } else {
                return $res;
            }

        } else {
            return null;
        }
    }
}
