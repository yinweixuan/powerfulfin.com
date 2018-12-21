<?php

/**
 * 宝付代付
 */
class BfPay extends App\Models\Epay\Epay {

    public function __construct($scene, $config = array()) {
        parent::__construct($scene, $config);
        $this->version = '4.0.0';
        $this->data_type = 'json';
        if ($this->env == self::ENV_DEV) {
            $this->host = 'http://paytest.baofoo.com';
            $this->member_id = '100000178';
            $this->terminal_id = '100000859';
            $this->pfxpath = dirname(__FILE__) . '/cer_test/m_pri.pfx';
            $this->cerpath = dirname(__FILE__) . '/cer_test/baofoo_pub1.cer';
            $this->pfx_pwd = '123456';
        } else {
            $this->host = 'http://public.baofoo.com';
            $this->member_id = '123';
            $this->terminal_id = '456';
            $this->pfxpath = dirname(__FILE__) . '/baofoo/cer/bfkey_100000178@@100000916.pfx';
            $this->cerpath = dirname(__FILE__) . '/baofoo/cer/baofoo_pub.cer';
            $this->pfx_pwd = '100000178_204500';
        }
    }

    public static $return_code_fail = array(
        '0001' => '商户代付公共参数格式不正确',
        '0002' => '商户代付证书无效',
        '0003' => '商户代付报文格式不正确',
        '0004' => '交易请求记录条数超过上限!',
        '0201' => '商户未开通代付业务',
        '0202' => '商户不存在，请联系宝付技术支持',
        '0203' => '商户代付业务未绑定IP，请联系宝付技术支持',
        '0204' => '商户代付终端号不存在，请联系宝付技术支持',
        '0205' => '商户代付收款方账号被列入黑名单，代付失败',
        '0206' => '商户代付交易受限',
        '0207' => '商户和委托商户不能相同',
        '0208' => '商户和委托商户绑定关系不存在',
        '0301' => '代付交易失败',
        '0501' => '代付白名单添加失败',
        '0601' => '代付(同卡进出)交易失败',
    );
    public static $return_code_doing = array(
        '0000' => '代付请求交易成功（交易已受理）',
        '0300' => '代付交易未明，请发起该笔订单查询',
        '0401' => '代付交易查证信息不存在',
        '0999' => '代付主机系统繁忙',
    );

    public function checkParams($params, $needKeys = array()) {
        foreach ($needKeys as $needKey) {
            if (!array_key_exists($needKey, $params)) {
                throw new KZException("参数缺失{$needKey}");
            }
            if (empty($params[$needKey])) {
                throw new KZException("参数不可为空{$needKey}");
            }
        }
    }

    public function getBfParams($params, $needKeys = array(), $optionalKeys = array()) {
        $this->config = array_merge($this->config, $params);
        $this->checkParams($this->config, $needKeys);
        $paramMap = array(
            'idcardName' => 'to_acc_name',
            'bankAccount' => 'to_acc_no',
            'bfName' => 'to_acc_name',
            'bfAccount' => 'to_acc_no',
            'province' => 'to_pro_name',
            'city' => 'to_city_name',
            'branch' => 'to_acc_dept',
            'idcardNumber' => 'trans_card_id',
            'phone' => 'trans_mobile',
            'remark' => 'trans_summary',
            'batchId' => 'trans_batchid',
            'orderId' => 'trans_no',
            'beginTime' => 'trans_btime',
            'endTime' => 'trans_etime',
            'bindId' => 'trans_bindid',
            'memberId' => 'to_member_id',
            'bfOrderId' => 'trans_orderid',
        );
        $allKeys = array_merge($needKeys, $optionalKeys);
        $bfParams = array();
        foreach ($allKeys as $pkey) {
            if ($paramMap[$pkey] && isset($this->config[$pkey])) {
                $bfParams[$paramMap[$pkey]] = $this->config[$pkey];
            }
        }
        if (in_array('moneyFen', $allKeys)) {
            $bfParams['trans_money'] = $this->config['moneyFen'] / 100;
        }
        if (in_array('bankCode', $allKeys)) {
            $bfParams['to_bank_name'] = $this->getBankname($this->config['bankCode']);
        }
        $bfParams['trans_no'] = $this->calcOrderId();
        return array('trans_content' => array('trans_reqDatas' => array(array('trans_reqData' => $bfParams))));
    }

    public function postRequest($apiPath, $bfParams) {
        $jsonstr = str_replace("\\/", "/", json_encode($bfParams)); //转JSON
        $BFRsa = new BFRSA($this->pfxpath, $this->cerpath, $this->pfx_pwd); //实例化加密类。
        $Encrypted = $BFRsa->encryptedByPrivateKey($jsonstr); //先BASE64进行编码再RSA加密
        $PostArry = array(
            'version' => $this->version,
            'terminal_id' => $this->terminal_id,
            'member_id' => $this->member_id,
            'data_type' => $this->data_type,
        );
        $PostArry['data_content'] = $Encrypted;
        $url = $this->host . $apiPath;
//        var_dump($url,$bfParams,$PostArry);
//        exit();
        //记录日志
        Yii::log($this->channel . ':' . $this->scene . " create input:" . print_r($bfParams, true), CLogger::LEVEL_INFO, 'zhifu.op');
        $trans_reqData = $bfParams['trans_content']['trans_reqDatas'][0]['trans_reqData'];
        $data = array();
        $data['scene'] = $this->scene;
        $data['channel'] = $this->channel;
        $data['status'] = self::STATUS_DOING;
        $data['money_fen'] = $this->config['moneyFen'] ? $this->config['moneyFen'] : 0;
        $data['busi_id'] = $this->config['busiId'];
        $data['busi_type'] = $this->config['busiType'];
        $data['order_id'] = $trans_reqData['trans_no'];
        $data['flow_id'] = '';
        $data['bill_time'] = date('YmdHis');
        $data['idcard_name'] = $this->config['idcardName'] ? $this->config['idcardName'] : '';
        $data['idcard_number'] = $this->config['idcardNumber'] ? $this->config['idcardNumber'] : '';
        $data['phone'] = $this->config['phone'] ? $this->config['phone'] : '';
        $data['userid'] = array_key_exists('userid', $this->config) ? $this->config['userid'] : '';
        $data['bank_code'] = $this->config['bankCode'] ? $this->config['bankCode'] : '';
        $data['bank_account'] = $this->config['bankAccount'] ? $this->config['bankAccount'] : '';
        $data['input'] = json_encode($trans_reqData);
        $data['additional_info'] = $trans_reqData['additional_info'] ? $trans_reqData['additional_info'] : '';
        $id = ARPayZhifuOrder::_add($data);
        $return = HttpClient::Post($PostArry, $url);  //发送请求到宝付服务器，并输出返回结果。
//        var_dump($return);
        $resultJson = $BFRsa->decryptByPublicKey($return); //解密返回的报文
        $result = json_decode($resultJson, true);
        var_dump($result);
        Yii::log($this->channel . ':' . $this->scene . ' create result. orderId: ' . $bfParams['trans_id'] . '. result:' . print_r($result, true), CLogger::LEVEL_INFO, 'zhifu.op');
        //检查返回结果是否成功
        $update = array();
        $result_head = $result['trans_content']['trans_head'];
        if (!is_array($result_head) || !array_key_exists('return_code', $result_head)) {
            throw new KZException("bfpay format error. no resp_code");
        } else {
            $update = $this->updateStatus($id, $result_head['return_code'], $result_head['return_msg'], $resultJson);
        }
        $update['kz_id'] = $id;
        return $update;
    }

    public function updateStatus($id, $code, $msg = '', $output = '') {
        $update = array();
        if ($id) {
            if ($code !== null) {
                $update['code'] = $code;
                if (in_array($code, array('200'))) {
                    $update['status'] = self::STATUS_SUCCESS;
                } elseif (array_key_exists($code, self::$return_code_fail)) {
                    $update['remark'] = self::$return_code_fail[$code];
                    $update['status'] = self::STATUS_FAIL;
                } else if (array_key_exists($code, self::$return_code_doing)) {
                    $update['remark'] = self::$return_code_doing[$code];
                    $update['status'] = self::STATUS_DOING;
                }
            } else {
                $update['status'] = self::STATUS_ERROR;
            }
            if ($msg) {
                $update['remark'] = $msg;
            }
            if ($output) {
                $update['output'] = $output;
            }
            if ($update) {
                ARPayZhifuOrder::_update($id, $update);
            }
        }
        return $update;
    }

    public function wepay($params = array()) {
        $apiPath = '/baofoo-fopay/pay/BF0040001.do';
        $needKeys = array('busiId', 'busiType', 'moneyFen', 'idcardName', 'bankAccount', 'bankCode');
        $optionalKeys = array('province', 'city', 'branch', 'idcardNumber', 'phone', 'remark');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        //发送请求
        $result = $this->postRequest($apiPath, $bfParams);
        return $result;
    }

    public function wepayResult($params = array()) {
        $apiPath = '/baofoo-fopay/pay/BF0040002.do';
        $needKeys = array('busiId', 'busiType', 'batchId', 'orderId');
        $optionalKeys = array();
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        //发送请求
        $result = $this->postRequest($apiPath, $bfParams);
        return $result;
    }

    public function wepayRefundResult($params = array()) {
        $apiPath = '/baofoo-fopay/pay/BF0040003.do';
        $needKeys = array('busiId', 'busiType', 'beginTime', 'endTime');
        $optionalKeys = array();
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        //发送请求
        $result = $this->postRequest($apiPath, $bfParams);
        return $result;
    }

    public function wepaySplit($params = array()) {
        $apiPath = '/baofoo-fopay/pay/BF0040004.do';
        $needKeys = array('busiId', 'busiType', 'moneyFen', 'idcardName', 'bankAccount', 'bankCode');
        $optionalKeys = array('province', 'city', 'branch', 'idcardNumber', 'phone', 'remark');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        $bfParams['trans_content']['trans_head'] = array(
            'trans_count' => '',
            'trans_totalMoney' => ''
        );
        //发送请求
        $result = $this->postRequest($apiPath, $bfParams);
        return $result;
    }

    public function wepayWhitelist($params = array()) {
        $apiPath = '/baofoo-fopay/pay/BF0040005.do';
        $needKeys = array('busiId', 'busiType', 'idcardName', 'bankAccount');
        $optionalKeys = array();
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        //发送请求
        $result = $this->postRequest($apiPath, $bfParams);
        return $result;
    }

    public function wepayBindCard($params = array()) {
        $apiPath = '/baofoo-fopay/pay/BF0040006.do';
        $needKeys = array('busiId', 'busiType', 'bindId', 'moneyFen', 'orderId');
        $optionalKeys = array('remark');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        //发送请求
        $result = $this->postRequest($apiPath, $bfParams);
        return $result;
    }

    public function wepayRealtime($params = array()) {
        $apiPath = '/baofoo-fopay/pay/BF0040007.do';
        $needKeys = array('busiId', 'busiType', 'bfName', 'moneyFen', 'orderId',
            'bfAccount', 'memberId');
        $optionalKeys = array('remark');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        //发送请求
        $result = $this->postRequest($apiPath, $bfParams);
        return $result;
    }

    public function wepayRealtimeResult($params = array()) {
        $apiPath = '/baofoo-fopay/pay/BF0040010.do';
        $needKeys = array('busiId', 'busiType', 'bfName', 'batchId', 'orderId',
            'bfAccount', 'memberId', 'bfOrderId');
        $optionalKeys = array();
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        $bfParams['trans_content']['trans_reqDatas'][0]['trans_reqData']['trans_member_id'] = $this->member_id;
        var_dump($bfParams);
        //发送请求
        $result = $this->postRequest($apiPath, $bfParams);
        return $result;
    }

    public function getBankname($code) {
        $bank_list = array(
            '102' => '招商银行',
        );
        $bank_name = $bank_list[$code];
        if (!$bank_name) {
            throw new KZException('不支持该银行');
        }
        return $bank_name;
    }

}
