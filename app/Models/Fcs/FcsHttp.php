<?php

namespace App\Models\Fcs;

class FcsHttp {

    const CODE_SUCCESS = 0;
    const CODE_FAIL = 1;
    const VERSION = '1.0.0';

    public static function getParams() {
        $post_data = file_get_contents('php://input');
        $params = json_decode(str_replace(PHP_EOL, '', $post_data), true);
        return $params;
    }

    public static function success($data, $lid = null) {
        self::out(self::CODE_SUCCESS, '', $data, $lid);
    }

    public static function fail($msg, $lid = null) {
        if (!$msg) {
            $msg = '服务异常';
        }
        self::out(self::CODE_FAIL, $msg, [], $lid);
    }

    public static function out($code, $msg, $data = array(), $lid = null) {
        $response = array();
        $response['c'] = config('fcs.channel');
        $response['t'] = time();
        $response['v'] = self::VERSION;
        $response['code'] = $code;
        $response['msg'] = $msg;
        $response['data'] = $data;
        echo json_encode($response);
        $uri = $_SERVER['REQUEST_URI'];
        $params = self::getParams();
        $log = '';
        if ($lid) {
            $log .= 'lid:' . $lid . PHP_EOL;
        }
        $log .= 'notice_uri:' . $uri . PHP_EOL
            . 'notice_data:' . print_r($params, true)
            . 'notice_response:' . print_r($response, true);
        FcsUtil::log($log);
        exit();
    }

}
