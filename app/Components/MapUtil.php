<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/21
 * Time: 2:46 PM
 */

namespace App\Components;


class MapUtil {
    private static function getAccessKey() {
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
    public static function getPosInfo($lng, $lat) {
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
    public static function getPosByIp($ip, $returnOriginal = false) {
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

    /**
     *求两个已知经纬度之间的距离,单位为米
     * @param lng1,lng2 经度
     * @param lat1,lat2 纬度
     * @return float 距离，单位米
     * @author wenling
     */
    public static function getDistance($lng1, $lat1, $lng2, $lat2) {
        $half = 6371;//地球半径，平均半径为6371km
        //将角度转为狐度
        $radLng1 = deg2rad($lng1);//deg2rad()函数将角度转换为弧度  = ($lng1 * pi() ) / 180  = $lng1 / (180 / pi())
        $radLng2 = deg2rad($lng2);
        $radLat1 = deg2rad($lat1);//deg2rad()函数将角度转换为弧度  = ($lat1 * pi() ) / 180  = $lat1 / (180 / pi())
        $radLat2 = deg2rad($lat2);

        $x = $radLng1 - $radLng2; //经度弧度差
        $y = $radLat1 - $radLat2; //纬度弧度差
        $distance = 2 * asin(
                sqrt(
                    pow(
                        sin($y / 2), 2
                    ) +
                    cos($radLat1) *
                    cos($radLat2) *
                    pow(
                        sin($x / 2), 2
                    )
                )
            ) * $half * 1000;
        return ceil($distance);
    }

    /**
     *获取一个坐标周围的四个坐标，矩形长度为2倍的距离，四个坐标为矩形四个角
     * @param lng 经度
     * @param lat 纬度
     * @distance 距离，单位千米
     * @return array
     * @author wenling
     */
    public static function getNearPoint($lng, $lat, $distance) {
        $half = 6371;//地球半径，平均半径为6371km
        $dlng = 2 * asin(
                sin(
                    $distance / (2 * $half)
                ) /
                cos(
                    deg2rad($lat)
                )
            );
        $dlng = rad2deg($dlng); //经度差  rad2deg 弧度转为角度

        $dlat = $distance / $half;  // 纬度弧度   $distance / ( 2 * pi() * 半径 ) = 弧度 / 360 ,所以  弧度 = $distance / 半径
        $dlat = rad2deg($dlat);// 纬度差 ($distance/pi()/$half)*180;  距离 / (2 * pi() * 半径 ) = 纬度 / 360,

        $fourpoint = array(
            'left-top' => array('lng' => $lng - $dlng, 'lat' => $lat + $dlat),
            'right-top' => array('lng' => $lng + $dlng, 'lat' => $lat + $dlat),
            'left-bottom' => array('lng' => $lng - $dlng, 'lat' => $lat - $dlat),
            'right-bottom' => array('lng' => $lng + $dlng, 'lat' => $lat - $dlat)
        ); // 矩形四个角的距离是距离$distance的根号2倍

        /**
         * lng > left-top[lng] and lng < right-top[lng] and    由于中国经度全部在东经，所以不考虑西经情况  左小右大
         * lat < left-top[lat] and lng > left-bottom[lat] and  由于中国全部在北半球，所以不考虑南纬情况    上大下小
         * select * from table lng > left-top[lng] and lng < right-top[lng] and lat < left-top[lat] and lng > left-bottom[lat]
         **/
        return $fourpoint;
    }
}
