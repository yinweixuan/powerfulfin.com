<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 11:42 AM
 */

namespace App\Http\Controllers;


use App\Components\HttpUtil;
use App\Components\OutputUtil;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redis;

class HomeController extends Controller {
    public function index() {
        return view('web.home.index');
    }

    /**
     * 下载页面
     */
    public function download() {
        $detect = new \Mobile_Detect();
        $isIOS = $detect->is('iphone') || $detect->is('ios');
        $isWX = HttpUtil::isWX();
        return view('web.home.download', ['isWX' => $isWX, 'isIOS' => $isIOS,]);
        /*
        $content = file_get_contents(PATH_RESOURCES . '/views/wx_download/wx_download.html');
        echo $content;
        return;
        */
    }

    /**
     * 下载包的内容
     */
    public function downloadPackage() {
        $detect = new \Mobile_Detect();
        $isIOS = $detect->is('iphone') || $detect->is('ios');
        if ($isIOS) {
            $hearder = ['Content-Type' => 'application/octet-stream;charset=utf-8'];
            return response()->download(storage_path('upload/pkg/powerfulfin.ipa'), 'powerfulfin.ipa', $hearder);
            //header('Location: https://itunes.apple.com/app/id1026601319?mt=8');
            //return;
        } else {
            $hearder = ['Content-Type' => 'application/apk;charset=utf-8'];
            return response()->download(storage_path('upload/pkg/powerfulfin.apk'), 'powerfulfin.apk', $hearder);
        }
    }

    /**
     * 申请分期二维码扫描之后的
     */
    public function qrscan() {
        if (!HttpUtil::isSelf()) {
            $url = "http://www." . DOMAIN_WEB . "/download?f=qr";
        } else {
            $oid = Input::get('oid');
            $url = "powerfulfin://apply?oid={$oid}";
        }
        HttpUtil::goUrl($url);
    }

    /**
     * app升级接口
     */
    public function appUpdate() {
        $version = Input::get('version');
        $header = getallheaders();
        $ds_ua = explode('|', urldecode($header['Ds-User-Agent']));
        $type = $ds_ua[6];
        if (strtolower($type) == 'ios') {
            $update = config('app_update.ios');
        } else {
            $update = config('app_update.android');
        }
        $version_arr = explode('.', $version);
        $latest_version_arr = explode('.', $update['version']);
        $result = [
            'update' => '0'
        ];
        foreach ($latest_version_arr as $k => $v) {
            if ($v > $version_arr[$k]) {
                $result['update'] = '1';
                break;
            } elseif ($v < $version_arr[$k]) {
                break;
            }
        }
        if ($result['update'] == '1') {
            $result = array_merge($update, $result);
        }
        OutputUtil::out($result);
    }

    /**
     * app审核接口
     */
    public function appAudit() {
        $header = getallheaders();
        $ds_ua = explode('|', urldecode($header['Ds-User-Agent']));
        $channel = $ds_ua[1];
        $version = $ds_ua[2];
        $type = $ds_ua[6];
        if (strtolower($type) == 'ios') {
            $audit_info = config('app_audit.ios');
        } else {
            $audit_info = config('app_audit.android');
        }
        $result = [];
        $result['audit'] = $audit_info[$version][$channel] ? $audit_info[$version][$channel] : '0';
        OutputUtil::out($result);
    }

    /**
     * app天气接口（包含语录）
     */
    public function weather() {
        //天气
        $lng = round(Input::get('lng'), 2);
        $lat = round(Input::get('lat'), 2);
        $result = [];
        if ($lng > 1 && $lat > 1) {
            $location = $lat . ':' . $lng;
            $result = $this->getWeather($location, true);
        }
        if ($result === false) {
            $ip = \App\Models\DataBus::get('ip_addr');
            if ($ip && $ip != '127.0.0.1') {
                $location = isset($location) ? $location : $ip;
                $result = $this->getWeather($ip, true);
            }
        }
        if (isset($location) && $result === false) {
            $result = $this->getWeather($location);
        }
        //默认地址
        $default_location = 'beijing';
        if (empty($result) && (!isset($location) || $location != $default_location)) {
            $result = $this->getWeather($default_location);
        }
        //语录
        $quote_key = 'PF_AUDIT_QUOTE_' . date('Ymd');
        try {
            $quote_cache = Redis::get($quote_key);
            if ($quote_cache) {
                $quote_arr = unserialize($quote_cache);
                if (is_array($quote_arr) && !empty($quote_arr)) {
                    $result['quote'] = $quote_arr[mt_rand(0, count($quote_arr) - 1)];
                }
            }
        } catch (\Exception $ex) {
        }
        if (!$result['quote']) {
            $quote_host = 'http://www.dutangapp.cn/u/day_text';
            $quote_params = [];
            $quote_params['date'] = date('Y-m-d');
            $quote_content = $this->curlGet($quote_host, $quote_params);
            $quote_result = json_decode($quote_content, true);
            if (is_array($quote_result['data'])) {
                foreach ($quote_result['data'] as $item) {
                    $quote_arr[] = $item['data'];
                }
            }
            if (!empty($quote_arr)) {
                try {
                    Redis::setex($quote_key, 86400, serialize($quote_arr));
                } catch (\Exception $ex) {
                }
                $result['quote'] = $quote_arr[mt_rand(0, count($quote_arr) - 1)];
            }
        }
        OutputUtil::out($result);
    }

    public function getWeather($location, $only_cache = false) {
        $weather = false;
        $weather_key = 'PF_AUDIT_WEATHER:' . date('YmdH') . '_' . str_replace(':', '_', $location);
        try {
            $weather_cache = Redis::get($weather_key);
            if ($weather_cache) {
                $weather = unserialize($weather_cache);
            }
        } catch (\Exception $ex) {
        }
        if (!$only_cache && !is_array($weather)) {
            $params = [];
            $params['key'] = 'aeh6u6uodepugnpe';
            $params['location'] = $location;
            $params['language'] = 'zh-Hans';
            $params['unit'] = 'c';
            $host = 'https://api.seniverse.com/v3/weather/now.json';
            $content = $this->curlGet($host, $params);
            $weather_result = json_decode($content, true);
            $weather = empty($weather_result['results'][0]) ? [] : $weather_result['results'][0];
            if (isset($weather['now']['code'])) {
                $weather['now']['code'] = $this->convertWeatherCode($weather['now']['code']);
            }
            try {
                Redis::setex($weather_key, 3600, serialize($weather));
            } catch (\Exception $ex) {
            }
        }
        return $weather;
    }

    public function convertWeatherCode($code) {
        //https://www.seniverse.com/doc#code
        //0：晴，1：多云，2：雨，3：雪，4：沙尘，5：雾，6：霾，7：风，99：无对应
        $code_map = [
            '0' => '0',
            '1' => '0',
            '2' => '0',
            '3' => '0',
            '4' => '1',
            '5' => '1',
            '6' => '1',
            '7' => '1',
            '8' => '1',
            '9' => '1',
            '10' => '2',
            '11' => '2',
            '12' => '2',
            '13' => '2',
            '14' => '2',
            '15' => '2',
            '16' => '2',
            '17' => '2',
            '18' => '2',
            '19' => '2',
            '20' => '2',
            '21' => '3',
            '22' => '3',
            '23' => '3',
            '24' => '3',
            '25' => '3',
            '26' => '4',
            '27' => '4',
            '28' => '4',
            '29' => '4',
            '30' => '5',
            '31' => '6',
            '32' => '7',
            '33' => '7',
            '34' => '7',
            '35' => '7',
            '36' => '7',
            '37' => '99',
            '38' => '99',
            '99' => '99',
        ];
        return $code_map[$code];
    }

    public function curlGet($host, $params) {
        $url = $host . '?' . http_build_query($params);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_URL, $url);
        $content = curl_exec($ch);
        curl_close($ch);
        return $content;
    }
}
