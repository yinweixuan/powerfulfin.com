<?php

namespace App\Models\Fcs;

class FcsSoap {

    public static function call($wsdl_name, $func, $params, $lid = null) {
        ini_set('default_socket_timeout', config('fcs.default_socket_timeout'));
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
            $sorted_params = self::prepare4test($func, $sorted_params);
            $wsse = 'http://schemas.xmlsoap.org/ws/2002/07/secext';
            $token_xml = '<wsse:Security xmlns:wsse="http://schemas.xmlsoap.org/ws/2002/07/secext">';
            $token_xml .= '<wsse:UsernameToken xmlns:wsu="http://schemas.xmlsoap.org/ws/2002/07/utility">';
            $token_xml .= '<wsse:Username>' . config('fcs.soap_username') . '</wsse:Username>';
            $token_xml .= '<wsse:Password Type="wsse:PasswordText">' . config('fcs.soap_password') . '</wsse:Password>';
            $token_xml .= '</wsse:UsernameToken>';
            $token_xml .= '</wsse:Security>';
            $user_token = new \SoapVar($token_xml, XSD_ANYXML);
            $soap_header = new \SoapHeader($wsse, 'Security', $user_token, false);
            $client = new \SOAPClient($wsdl, array('cache_wsdl' => WSDL_CACHE_NONE,));
            $client->__setSoapHeaders($soap_header);
            $time1 = microtime(true);
            $r = $client->__soapCall($func, $sorted_params);
        } catch (\SoapFault $sf) {
            $error = $sf->getMessage();
        }
        $time2 = microtime(true);
        $log = 'function:' . $func . PHP_EOL;
        if ($lid) {
            $log = 'lid : ' . $lid . PHP_EOL;
        }
        if ($func != 'KZPhoneBookSyn') {
            $log .= 'arguments:' . print_r($sorted_params, true);
        }
        if (!empty($error)) {
            $log .= 'error:' . $error . PHP_EOL;
        }
        $log .= 'soapcall_cost_time:' . ($time2 - $time1) . '秒' . PHP_EOL
            . 'return:' . print_r($r, true) . PHP_EOL;
        FcsUtil::log($log);
        return $r;
    }

    /**
     * 处理数据，测试使用
     */
    public static function prepare4test($func, $sorted_params) {
        if (config('app.env') == 'local') {
            $pic_arr = array(
                'attachment1', 'attachment2', 'attachment3',
                'attachment4', 'attachment5', 'attachment7',
                'attachment8', 'attachment9', 'YLZD01', 'YLZD08'
            );
            foreach ($sorted_params as $k => &$v) {
                if ($func == 'KZCreateLoan') {
                    if ($k == 'liveDetailAddress' && !$v) {
                        $v = '---';
                    }
                    if (in_array($k, $pic_arr) && !$v) {
                        $v = FcsFtp::parsePath(config('fcs.blank_pic'));
                    }
                    if ($k == 'YLZD06') {
                        $v = mt_rand(100000, 999999);
                    }
                }
            }
        }
        return $sorted_params;
    }

}
