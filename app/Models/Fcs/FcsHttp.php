<?php

namespace App\Models\Fcs;

use App\Models\Fcs\FcsCommon;
use App\Models\Fcs\FcsContract;
use App\Models\Fcs\FcsField;
use App\Models\Fcs\FcsFtp;
use App\Models\Fcs\FcsHttp;
use App\Models\Fcs\FcsLoan;
use App\Models\Fcs\FcsQueue;
use App\Models\Fcs\FcsSoap;

class FcsHttp {

    const CODE_SUCCESS = 0;
    const CODE_FAIL = 1;
    const VERSION = '1.0.0';
    const CHANNEL = 'kezhan';

    public static function getParams() {
        $post_data = file_get_contents('php://input');
        $params = json_decode(str_replace(PHP_EOL, '', $post_data), true);
        return $params;
    }

    public static function success($data) {
        self::out(self::CODE_SUCCESS, '', $data);
    }

    public static function fail($msg) {
        if (!$msg) {
            $msg = '服务异常';
        }
        self::out(self::CODE_FAIL, $msg);
    }

    public static function out($code, $msg, $data = array()) {
        $response = array();
        $response['c'] = self::CHANNEL;
        $response['t'] = time();
        $response['v'] = self::VERSION;
        $response['code'] = $code;
        $response['msg'] = $msg;
        $response['data'] = $data;
        echo json_encode($response);
        $uri = $_SERVER['REQUEST_URI'];
        $post_data = file_get_contents('php://input');
        $params = json_decode(str_replace(PHP_EOL, '', $post_data), true);
        $log = 'notice_uri:' . $uri . PHP_EOL
                . 'notice_data:' . print_r($params, true)
                . 'notice_response:' . print_r($response, true);
        \Yii::log($log,  'fcs.op');
        \Yii::log($log,  'fcs_all.op');
        exit();
    }

}
