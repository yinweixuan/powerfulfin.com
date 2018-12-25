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

class FcsSoap {

    public static function call($wsdl_name, $func, $params) {
        ini_set('default_socket_timeout', 300);
        if (config('app.env') == 'local') {
            //开发环境
            $wsdl = dirname(__FILE__) . '/wsdl_dev/' . $wsdl_name;
        } else {
            //正式环境
            $wsdl = dirname(__FILE__) . '/wsdl_prod/' . $wsdl_name;
        }
        try {
            $xmlobj = simplexml_load_string(file_get_contents($wsdl));
            $xmlarr = json_decode(json_encode($xmlobj), true);
            $sorted_params = array();
            foreach ($xmlarr['message'][0]['part'] as $v) {
                $k = $v['@attributes']['name'];
                if (array_key_exists($k, $params)) {
                    $sorted_params[$k] = $params[$k];
                } else {
                    $sorted_params[$k] = '';
                }
            }
            if (config('app.env') == 'local') {
                //联调时先用
                $pic_arr = array(
                    'attachment1', 'attachment2', 'attachment3',
                    'attachment4', 'attachment5', 'attachment7',
                    'attachment8', 'attachment9', 'YLZD01', 'YLZD08'
                );
                $n = 0;
                foreach ($sorted_params as $k => &$v) {
                    if ($k == 'liveDetailAddress' && !$v) {
                        $v = '---';
                    }
                    if ($func == 'KZCreateLoan' && in_array($k, $pic_arr) && !$v) {
                        $v = FcsFtp::getFile(154085, '/163249_1492677169_2571.png', $n + 2);
                        $n++;
                    }
                    if ($func == 'KZCreateLoan' && $k == 'YLZD07') {
                        $v = json_encode(array(
                            FcsFtp::getFile(154085, '/163249_1492677169_2571.png', $n + 20),
                            FcsFtp::getFile(154085, '/163249_1492677169_2571.png', $n + 21),
                            FcsFtp::getFile(154085, '/163249_1492677169_2571.png', $n + 22),
                            FcsFtp::getFile(154085, '/163249_1492677169_2571.png', $n + 23),
                        ));
                    }
                    if ($func == 'KZCreateLoan' && $k == 'YLZD06') {
                        $v = mt_rand(100000, 999999);
                    }
                }
            }
            $client = new \SOAPClient($wsdl, array('cache_wsdl' => WSDL_CACHE_NONE,));
            $time1 = microtime(true);
            $r = $client->__soapCall($func, $sorted_params);
        } catch (SoapFault $sf) {
            $error = $sf->getMessage();
        }
        $time2 = microtime(true);
        $log_all = 'function:' . $func . PHP_EOL
                . 'arguments:' . print_r($sorted_params, true)
                . 'soapcall_cost_time:' . ($time2 - $time1) . '秒' . PHP_EOL
                . 'return:' . print_r($r, true);
        $log = 'function:' . $func . PHP_EOL;
        if ($func != 'KZPhoneBookSyn') {
            $log .= 'arguments:' . print_r($sorted_params, true);
        }
        $log .= 'soapcall_cost_time:' . ($time2 - $time1) . '秒' . PHP_EOL
                . 'return:' . print_r($r, true);
        if ($error) {
            if (!$r) {
                $log_all .= PHP_EOL;
                $log .= PHP_EOL;
            }
            $log_all .= 'error:' . $error . PHP_EOL;
            $log .= 'error:' . $error . PHP_EOL;
        }
        \Yii::log($log_all,  'fcs_all.op');
        \Yii::log($log,  'fcs.op');
        return $r;
    }

}
