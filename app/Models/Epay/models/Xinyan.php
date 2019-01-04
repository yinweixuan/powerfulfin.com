<?php

require_once('xinyan/HttpCurl.php');
require_once('xinyan/BaofooUtils.php');
require_once('xinyan/Log.php');
require_once('xinyan/BFRSA.php');
require_once('xinyan/HttpClient.php');

class Xinyan extends App\Models\Epay\Epay {

    public function __construct($scene, $config = array()) {
        parent::__construct($scene, $config);
        $this->data_type = 'json';
        $this->product_type = '0';
        $this->industry_type = 'C13';
        if ($this->env == self::ENV_DEV) {
            //测试环境
            $this->host = 'https://test.xinyan.com';
            $this->member_id = env('XINYAN_MEMBER_ID_DEV');
            $this->terminal_id = env('XINYAN_TERMINAL_ID_DEV');
            $this->pfxpath = PATH_STORAGE . '/cer/baofoo/xinyan/cer_test/8000013189_pri.pfx';
            $this->cerpath = PATH_STORAGE . '/cer/baofoo/xinyan/cer_test/bfkey_8000013189.cer';
            $this->pfx_pwd = env('XINYAN_CER_PASSWORD_DEV');
        } else {
            //正式环境
            $this->host = 'https://api.xinyan.com';
            $this->member_id = env('XINYAN_MEMBER_ID');
            $this->terminal_id = env('XINYAN_TERMINAL_ID');
            $this->pfxpath = PATH_STORAGE . '/cer/baofoo/xinyan/cer/k2b_private.pfx';
            $this->cerpath = PATH_STORAGE . '/cer/baofoo/xinyan/cer/b2k_public.cer';
            $this->pfx_pwd = env('XINYAN_CER_PASSWORD');
        }
    }

    /**
     * 支持的银行
     */
    public static $xinyan_banks = array(
        '102' => 'ICBC', // => '中国工商银行',
        '103' => 'ABC', // => '中国农业银行',
        '105' => 'CCB', // => '中国建设银行',
        '104' => 'BOC', // => '中国银行',
        '301' => 'BCOM', //  交通银行
        '309' => 'CIB', // => '兴业银行',
        '302' => 'CITIC', // => '中信银行',
        '303' => 'CEB', // => '中国光大银行',
        '403' => 'PSBC', // => '中国邮政储蓄银行',
        '308' => 'CMB', // => '招商银行',
        '306' => 'GDB', //广东发展银行
        '307' => 'PAB', //平安银行
        '301' => 'BCOM', //中国交通银行
    );

    public function checkParams($params, $needKeys = array()) {
        foreach ($needKeys as $needKey) {
            if (!array_key_exists($needKey, $params)) {
                throw new Exception("参数缺失{$needKey}");
            }
            if (empty($params[$needKey])) {
                throw new Exception("参数不可为空{$needKey}");
            }
        }
    }

    public function getBfParams($params, $needKeys = array(), $optionalKeys = array()) {
        $this->config = array_merge($this->config, $params);
        $this->checkParams($this->config, $needKeys);
        $paramMap = array(
            'bank_account' => 'acc_no',
            'bankCode' => 'pay_code',
            'idcard_number' => 'id_card',
            'idcard_name' => 'id_holder',
            'phone' => 'mobile',
            'validDate' => 'valid_date',
            'validNo' => 'valid_no',
            'smsCode' => 'sms_code',
            'additionalInfo' => 'additional_info',
            'bindId' => 'bind_id',
            'moneyFen' => 'txn_amt',
            'riskContent' => 'risk_content',
            'businessNo' => 'business_no',
            'orderId' => 'trans_id',
            'origOrderId' => 'orig_trans_id',
            'origTradeDate' => 'orig_trade_date',
            'validDateYear' => 'valid_date_year',
            'validDateMonth' => 'valid_date_month',
            'verify' => 'verify_element',
            'cardType' => 'card_type',
            'tradeNo' => 'trade_no',
        );
        $allKeys = array_merge($needKeys, $optionalKeys);
        if (in_array('bankCode', $allKeys)) {
            $this->config['kzBankCode'] = $this->config['bankCode'];
            $this->config['bankCode'] = self::$xinyan_banks[$this->config['bankCode']];
        }
        if (in_array('additionalInfo', $allKeys)) {
            $this->config['additionalInfo'] = $this->config['additionalInfo'] ? $this->config['additionalInfo'] : json_encode(array('busi_id' => $this->config['busiId'], 'busi_type' => $this->config['busiType'],));
        }
        $bfParams = array(
            'terminal_id' => $this->terminal_id,
            'member_id' => $this->member_id,
            'trans_id' => $this->calcOrderId(),
            'trade_date' => date('YmdHis'),
            'product_type' => $this->product_type,
            'industry_type' => $this->industry_type
        );
        foreach ($allKeys as $pkey) {
            if ($paramMap[$pkey] && isset($this->config[$pkey])) {
                $bfParams[$paramMap[$pkey]] = $this->config[$pkey];
            }
        }
        return $bfParams;
    }

    public function postRequest($apiPath, $bfParams) {
        $jsonstr = str_replace("\\/", "/", json_encode($bfParams)); //转JSON
        $BFRsa = new BFRSA($this->pfxpath, $this->cerpath, $this->pfx_pwd, true); //实例化加密类。
        $Encrypted = $BFRsa->encryptedByPrivateKey($jsonstr); //先BASE64进行编码再RSA加密
        $PostArry = array(
            'terminal_id' => $this->terminal_id,
            'member_id' => $this->member_id,
            'data_type' => $this->data_type
        );
        $PostArry['data_content'] = $Encrypted;
        $url = $this->host . $apiPath;
        //记录日志
        \Yii::log($this->channel . ':' . $this->scene . " xinyan input:" . print_r($bfParams, true), 'zhifu.op');
        $return = HttpClient::Post($PostArry, $url);  //发送请求到宝付服务器，并输出返回结果。
        \Yii::log($this->channel . ':' . $this->scene . ' xinyan json result. orderId: ' . $bfParams['trans_id'] . '. result:' . $return, 'zhifu.op');
        $result = json_decode($return, true);
        \Yii::log($this->channel . ':' . $this->scene . ' xinyan result. orderId: ' . $bfParams['trans_id'] . '. result:' . print_r($result, true), 'zhifu.op');
        //检查返回结果是否成功
        if (!is_array($result) || !array_key_exists('success', $result)) {
            throw new Exception("xinyan format error.");
        }
        return $result;
    }

    public function updateStatus($id, $result, $output = '') {
        $update = array();
        if ($id && $result) {
            if ($result['success']) {
                $update['code'] = $result['data']['code'];
                $update['remark'] = $result['data']['desc'];
                $update['flow_id'] = $result['data']['trade_no'];
                $update['status'] = self::STATUS_SUCCESS;
            } else {
                $update['code'] = $result['errorCode'];
                $update['remark'] = $result['errorMsg'];
                if ($update['code'] == 'S1002') {
                    $update['status'] = self::STATUS_DOING;
                } else {
                    $update['status'] = self::STATUS_FAIL;
                }
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

    /**
     * 验卡四要素
     * @param array $params 参数数组
     * <br>idcard_name 姓名
     * <br>idcard_number 身份证号
     * <br>bank_account 银行卡号
     * <br>phone 手机号
     * <br>verify 验证类型：4:四要素，3：三要素，2：二要素
     * @return array
     * @throws Exception
     */
    public function cardAuth($params = array()) {
        $apiPath = '/bankcard/v3/auth';
        $needKeys = array('bank_account', 'idcard_number', 'idcard_name', 'verify');
        $optionalKeys = array('phone', 'cardType', 'validNo', 'validDateYear', 'validDateMonth');
        //检查参数,拼装请求
        if ($params['verify'] == '2') {
            $params['verify'] = '12';
        } elseif ($params['verify'] == '3') {
            $params['verify'] = '123';
        } elseif ($params['verify'] == '4') {
            $params['verify'] = '1234';
        } else {
            throw new Exception("验证元素参数错误");
        }
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        //发送请求
        $result = $this->postRequest($apiPath, $bfParams);
        return $result;
    }

    public function cardAuthSms($params = array()) {
        $apiPath = '/bankcard/v1/authsms';
        $needKeys = array('busiId', 'busiType', 'phone', 'bank_account', 'idcard_number', 'idcard_name');
        $optionalKeys = array('cardType');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        //发送请求
        $result = $this->postRequest($apiPath, $bfParams);
        return $result;
    }

    public function cardAuthConfirm($params = array()) {
        $apiPath = '/bankcard/v1/authconfirm';
        $needKeys = array('busiId', 'busiType', 'smsCode', 'tradeNo');
        $optionalKeys = array();
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        //发送请求
        $result = $this->postRequest($apiPath, $bfParams);
        return $result;
    }

}
