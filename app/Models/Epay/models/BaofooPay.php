<?php

/**
 * 宝付支付的对象
 */
require_once("baofoo/BFRSA.php");
require_once("baofoo/SdkXML.php");
require_once("baofoo/Log.php");
require_once("baofoo/HttpClient.php");
require_once("baofoo/BfPay.php");
require_once("baofoo/GwPay.php");

use \App\Models\ActiveRecord\ARPFZhifuOrder;
use \App\Models\Epay\models\KZPayCommon;

class BaofooPay extends App\Models\Epay\Epay {

    /**
     * 版本号
     */
    const BF_VERSION = '4.0.0.0';

    /**
     * 商户号
     */
    private $BF_MEMBER_ID = '';

    /**
     * 加密报文的数据类型（xml/json）
     */
    const BF_DATA_TYPE = 'json';

    /**
     * 宝付公钥
     * @var type 
     */
    private $BF_PUBLIC_CER = '';

    /**
     * 课栈2宝付私钥
     * @var type 
     */
    private $BF_PRIVATE_CER = '';

    /**
     * 课栈2宝付私钥密码
     */
    private $BF_PRIVATE_CER_PASSWORD = '';

    public function __construct($scene, $config = array()) {
        parent::__construct($scene, $config);
        $this->txn_type = '0431';
        $this->biz_type = '0000';
        $this->id_card_type = '01';
        if ($this->env == self::ENV_DEV) {
            //测试环境
            $this->BF_PUBLIC_CER = PATH_BASE . '/cer/baofoo/baofoo/cer_test/baofoo_pub.cer';
            $this->BF_PRIVATE_CER = PATH_BASE . '/cer/baofoo/baofoo/cer_test/bfkey_100000178@@100000916.pfx';
            $this->BF_PRIVATE_CER_PASSWORD = env('BAOFOO_PAY_CER_PASSWORD_DEV');
            $this->BF_MEMBER_ID = env('BAOFOO_PAY_MEMBER_ID_DEV');
            $this->terminal_id = env('BAOFOO_PAY_TERMINAL_ID_DEV');
            $this->apiurl = 'https://vgw.baofoo.com/cutpayment/api/backTransRequest';
        } else {
            //正式环境
            $this->BF_PUBLIC_CER = PATH_BASE . '/cer/baofoo/baofoo/cer/b2k_public.cer';
            $this->BF_PRIVATE_CER = PATH_BASE . '/cer/baofoo/baofoo/cer/k2b_private.pfx';
            $this->BF_PRIVATE_CER_PASSWORD = env('BAOFOO_PAY_CER_PASSWORD');
            $this->BF_MEMBER_ID = env('BAOFOO_PAY_MEMBER_ID');
            $this->terminal_id = env('BAOFOO_PAY_TERMINAL_ID');
            $this->apiurl = 'https://public.baofoo.com/cutpayment/api/backTransRequest';
        }
    }

    /**
     * 宝付需要查询的代扣错误
     */
    public static $BF_WITHHOLD_ERROR_QUERY = array(
        'BF00100' => '系统异常,请联系宝付',
        'BF00112' => '系统繁忙,请稍后再试',
        'BF00113' => '交易结果未知,请稍后查询',
        'BF00115' => '交易处理中,请稍后查询',
        'BF00144' => '该交易有风险,订单处理中',
        'BF00202' => '交易超时,请稍后查询',
    );

    /**
     * 宝付无需查询的代扣错误
     */
    public static $BF_WITHHOLD_ERROR_NOQUERY = array(
        'BF00101' => '持卡人信息有误',
        'BF00102' => '银行卡已过有效期，请联系发卡行',
        'BF00103' => '账户余额不足',
        'BF00104' => '交易金额超限',
        'BF00107' => '当前银行卡不支持该业务，请联系发卡行',
        'BF00108' => '交易失败，请联系发卡行',
        'BF00109' => '交易金额低于限额',
        'BF00110' => '该卡暂不支持此交易',
        'BF00111' => '交易失败',
        'BF00116' => '该终端号不存在',
        'BF00118' => '报文中密文解析失败',
        'BF00120' => '报文交易要素缺失',
        'BF00121' => '报文交易要素格式错误',
        'BF00122' => '卡号和支付通道不匹配',
        'BF00123' => '商户不存在或状态不正常，请联系宝付',
        'BF00124' => '商户与终端号不匹配',
        'BF00125' => '商户该终端下未开通此类型交易',
        'BF00126' => '该笔订单已存在',
        'BF00127' => '不支持该支付通道的交易',
        'BF00128' => '该笔订单不存在',
        'BF00129' => '密文和明文中参数不一致,请确认是否被篡改！',
        'BF00135' => '交易金额不正确 ',
        'BF00136' => '订单创建失败',
        'BF00140' => '该卡已被注销',
        'BF00141' => '该卡已挂失',
        'BF00146' => '订单金额超过单笔限额',
        'BF00147' => '该银行卡不支持此交易',
        'BF00177' => '非法的交易',
        'BF00190' => '商户流水号不能重复',
        'BF00199' => '订单日期格式不正确',
        'BF00232' => '银行卡未开通认证支付',
        'BF00233' => '密码输入次数超限，请联系发卡行',
        'BF00234' => '单日交易金额超限',
        'BF00235' => '单笔交易金额超限',
        'BF00236' => '卡号无效，请确认后输入',
        'BF00237' => '该卡已冻结，请联系发卡行',
        'BF00249' => '订单已过期，请使用新的订单号发起交易',
        'BF00251' => '订单未支付',
        'BF00253' => '交易拒绝',
        'BF00258' => '手机号码校验失败',
        'BF00262' => '交易金额与扣款成功金额不一致，请联系宝付',
        'BF00311' => '卡类型和biz_type值不匹配',
        'BF00312' => '交易金额不匹配',
        'BF00313' => '商户未开通此产品',
        'BF00315' => '手机号码为空，请重新输入',
        'BF00316' => 'ip未绑定，请联系宝付',
        'BF00321' => '身份证号不合法',
        'BF00322' => '卡类型和卡号不匹配',
        'BF00323' => '商户未开通交易模版',
        'BF00324' => '暂不支持此银行卡支付，请更换其他银行卡或咨询商户客服',
        'BF00325' => '非常抱歉！目前该银行正在维护中，请更换其他银行卡支付',
        'BF00327' => '请联系银行核实您的卡状态是否正常',
        'BF00331' => '卡号校验失败',
        'BF00332' => '交易失败，请重新支付',
        'BF00333' => '该卡有风险，发卡行限制交易',
        'BF00341' => '该卡有风险，请持卡人联系银联客服[95516]',
        'BF00342' => '单卡单日余额不足次数超限',
        'BF00343' => '验证失败（手机号有误）',
        'BF00344' => '验证失败（卡号有误）',
        'BF00345' => '验证失败（姓名有误）',
        'BF00346' => '验证失败（身份证号有误）',
        'BF00343' => '验证失败（手机号有误）',
        'BF08701' => '超过该卡本次可支付金额，请更换其他银行卡！',
        'BF08702' => '超过该商户本次可支付金额，请更换其他银行卡或咨询商户客服！',
        'BF08703' => '支付金额不能低于最低限额！ ',
        'BF08704' => '单笔金额超限！',
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
        $allKeys = array_merge($needKeys, $optionalKeys);
        if (in_array('origTradeDate', $allKeys) && empty($this->config['origTradeDate']) && $this->config['origOrderId']) {
            $row = ARPFZhifuOrder::getByOrderid($this->config['origOrderId'], 'bill_time');
            $this->config['origTradeDate'] = $row['bill_time'];
        }
        $this->checkParams($this->config, $needKeys);
        $paramMap = array(
            'bankAccount' => 'acc_no',
            'bankCode' => 'pay_code',
            'idcardNumber' => 'id_card',
            'idcardName' => 'id_holder',
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
        );
        if (in_array('bankCode', $allKeys)) {
            $this->config['kzBankCode'] = $this->config['bankCode'];
            $this->config['bankCode'] = $this->config['bankCode'];
        }
        if (in_array('additionalInfo', $allKeys)) {
            $this->config['additionalInfo'] = $this->config['additionalInfo'] ? $this->config['additionalInfo'] : json_encode(array('busi_id' => $this->config['busi_id'], 'busi_type' => $this->config['busi_type'],));
        }
        $bfParams = array(
            'biz_type' => $this->biz_type,
            'terminal_id' => $this->terminal_id,
            'member_id' => $this->BF_MEMBER_ID,
            'trans_serial_no' => $this->calcFlowId(),
            'trans_id' => $this->calcOrderId(),
            'id_card_type' => $this->id_card_type,
            'trade_date' => date('YmdHis')
        );
        foreach ($allKeys as $pkey) {
            if ($paramMap[$pkey] && isset($this->config[$pkey])) {
                $bfParams[$paramMap[$pkey]] = $this->config[$pkey];
            }
        }
        return $bfParams;
    }

    public function postRequest($txn_sub_type, $bfParams, $record = true) {
        $jsonstr = str_replace("\\/", "/", json_encode($bfParams)); //转JSON
        $BFRsa = new BFRSA($this->BF_PRIVATE_CER, $this->BF_PUBLIC_CER, $this->BF_PRIVATE_CER_PASSWORD); //实例化加密类。
        $Encrypted = $BFRsa->encryptedByPrivateKey($jsonstr); //先BASE64进行编码再RSA加密
        $PostArry = array(
            'version' => self::BF_VERSION,
            'terminal_id' => $this->terminal_id,
            'txn_type' => $this->txn_type,
            'member_id' => $this->BF_MEMBER_ID,
            'data_type' => self::BF_DATA_TYPE
        );
        $PostArry['txn_sub_type'] = $txn_sub_type;
        $PostArry['data_content'] = $Encrypted;
        //记录日志
        \Yii::log($this->channel . ':' . $this->scene . " create input:" . print_r($bfParams, true), 'zhifu.op');
        if ($record) {
            //记数据库
            $data = array();
            $data['scene'] = $this->scene;
            $data['channel'] = $this->channel;
            $data['status'] = self::STATUS_DOING;
            $data['money_fen'] = $this->config['moneyFen'] ? $this->config['moneyFen'] : 0;
            $data['busi_id'] = $this->config['busi_id'] ? $this->config['busi_id'] : 0;
            $data['busi_type'] = $this->config['busi_type'] ? $this->config['busi_type'] : '';
            $data['bill_date'] = $this->config['bill_date'] ? $this->config['bill_date'] : '';
            $data['order_id'] = $bfParams['trans_id'];
            $data['flow_id'] = $bfParams['trans_serial_no'];
            $data['bill_time'] = $bfParams['trade_date'];
            $data['idcard_name'] = $this->config['idcardName'] ? $this->config['idcardName'] : '';
            $data['idcard_number'] = $this->config['idcardNumber'] ? $this->config['idcardNumber'] : '';
            $data['phone'] = $this->config['phone'] ? $this->config['phone'] : '';
            $data['userid'] = $this->config['userid'] ? $this->config['userid'] : '';
            $data['bank_code'] = $this->config['kzBankCode'] ? $this->config['kzBankCode'] : '';
            $data['bank_account'] = $this->config['bankAccount'] ? $this->config['bankAccount'] : '';
            $data['input'] = json_encode($bfParams);
            $data['additional_info'] = $bfParams['additional_info'] ? $bfParams['additional_info'] : '';
            ARPFZhifuOrder::add($data);
        }
        $return = HttpClient::Post($PostArry, $this->apiurl);  //发送请求到宝付服务器，并输出返回结果。
        $resultJson = $BFRsa->decryptByPublicKey($return); //解密返回的报文
        $result = json_decode($resultJson, true);
        \Yii::log($this->channel . ':' . $this->scene . ' create result. orderId: ' . $bfParams['trans_id'] . '. result:' . print_r($result, true), 'zhifu.op');
        //检查返回结果是否成功
        $update = array();
        if (!is_array($result) || !array_key_exists('resp_code', $result)) {
            throw new KZException("bfpay format error. no resp_code");
        } else {
            $orderid = $bfParams['orig_trans_id'] ? $bfParams['orig_trans_id'] : $bfParams['trans_id'];
            $update = $this->updateStatus($orderid, $result['resp_code'], $result['resp_msg'], $resultJson);
        }
        $update['kz_order_id'] = $orderid;
        return $update;
    }

    public function updateStatus($orderid, $code, $msg = '', $output = '') {
        $update = array();
        if ($orderid) {
            if ($code !== null) {
                $update['code'] = $code;
                if (in_array($code, array('0000', 'BF00114'))) {
                    $update['status'] = self::STATUS_SUCCESS;
                } elseif (array_key_exists($code, self::$BF_WITHHOLD_ERROR_NOQUERY)) {
                    $update['remark'] = self::$BF_WITHHOLD_ERROR_NOQUERY[$code];
                    $update['status'] = self::STATUS_FAIL;
                } else if (array_key_exists($code, self::$BF_WITHHOLD_ERROR_QUERY)) {
                    $update['remark'] = self::$BF_WITHHOLD_ERROR_QUERY[$code];
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
                ARPFZhifuOrder::updateByOrderid($orderid, $update);
            }
        }
        return $update;
    }

    public function withhold($params = array()) {
        //兼容新旧参数
        if ($params['idcard_name']) {
            $params['idcardName'] = $params['idcard_name'];
        }
        if ($params['idcard_number']) {
            $params['idcardNumber'] = $params['idcard_number'];
        }
        if ($params['bank_code']) {
            $params['bankCode'] = $params['bank_code'];
        }
        if ($params['bank_account']) {
            $params['bankAccount'] = $params['bank_account'];
        }
        if ($params['money_fen']) {
            $params['moneyFen'] = $params['money_fen'];
        }
        //交易子类：代扣
        $txn_sub_type = '13';
        $needKeys = array('idcardName', 'idcardNumber', 'bankCode', 'bankAccount', 'phone', 'moneyFen');
        $optionalKeys = array('busi_id', 'busi_type', 'additionalInfo');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        $bfParams['txn_sub_type'] = $txn_sub_type;
        //发送请求
        $result = $this->postRequest($txn_sub_type, $bfParams);
        return KZPayCommon::sgetOrderStatus($result['kz_order_id']);
    }

    /**
     * 获取代扣结果
     */
    public function withholdResult($params = array()) {
        //兼容新旧参数
        if ($params['order_id']) {
            $params['origOrderId'] = $params['order_id'];
        }
        //交易子类：获取代扣结果
        $txn_sub_type = '31';
        $needKeys = array('origOrderId', 'origTradeDate');
        $optionalKeys = array();
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        $bfParams['txn_sub_type'] = $txn_sub_type;
        //发送请求
        $result = $this->postRequest($txn_sub_type, $bfParams, false);
        return KZPayCommon::sgetOrderStatus($params['order_id']);
    }

    public function preBindCard($params = array()) {
        //交易子类：预绑卡类交易
        $txn_sub_type = '11';
        $needKeys = array('busi_id', 'busi_type', 'bankAccount', 'idcardNumber', 'idcardName', 'phone', 'bankCode');
        $optionalKeys = array('validDate', 'validNo', 'additionalInfo');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        $bfParams['txn_sub_type'] = $txn_sub_type;
        //发送请求
        $result = $this->postRequest($txn_sub_type, $bfParams);
        return $result;
    }

    public function confirmBindCard($params = array()) {
        //交易子类：确认绑卡类交易
        $txn_sub_type = '12';
        $needKeys = array('busi_id', 'busi_type', 'smsCode', 'orderId');
        $optionalKeys = array('additionalInfo');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        $bfParams['txn_sub_type'] = $txn_sub_type;
        //发送请求
        $result = $this->postRequest($txn_sub_type, $bfParams);
        if ($result['status'] == self::STATUS_SUCCESS) {
            $response = json_decode($result['output'], true);
            //todo
            /*
             * "bind_id\":\"201707281641551000009168808\",\"trans_id\":\"pay201707281642009728000000\",\"trans_serial_no\":\"20170728164200972800\"
             */
        }
        return $result;
    }

    public function BindCard($params = array()) {
        //交易子类：直接绑卡类交易
        $txn_sub_type = '01';
        $needKeys = array('busi_id', 'busi_type', 'bankAccount', 'idcardNumber', 'idcardName', 'phone', 'bankCode');
        $optionalKeys = array('validDate', 'validNo', 'additionalInfo');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        $bfParams['txn_sub_type'] = $txn_sub_type;
        //发送请求
        $result = $this->postRequest($txn_sub_type, $bfParams);
        if ($result['status'] == self::STATUS_SUCCESS) {
            $response = json_decode($result['output'], true);
            //todo
            /*
             * "bind_id\":\"201707281641551000009168808\",\"trans_id\":\"pay201707281642009728000000\",\"trans_serial_no\":\"20170728164200972800\"
             */
        }
        return $result;
    }

    public function bindCardResult($params = array()) {
        //交易子类：查询绑定关系类交易
        $txn_sub_type = '03';
        $needKeys = array('busi_id', 'busi_type', 'bankAccount');
        $optionalKeys = array('additionalInfo');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        $bfParams['txn_sub_type'] = $txn_sub_type;
        //发送请求
        $result = $this->postRequest($txn_sub_type, $bfParams);
        if ($result['status'] == self::STATUS_SUCCESS) {
            $response = json_decode($result['output'], true);
            //todo
        }
        return $result;
    }

    public function unbindCard($params = array()) {
        //交易子类：解除绑定关系类交易
        $txn_sub_type = '02';
        $needKeys = array('busi_id', 'busi_type', 'bindId');
        $optionalKeys = array('additionalInfo');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        $bfParams['txn_sub_type'] = $txn_sub_type;
        //发送请求
        $result = $this->postRequest($txn_sub_type, $bfParams);
        if ($result['status'] == self::STATUS_SUCCESS) {
            $response = json_decode($result['output'], true);
            //todo
        }
        return $result;
    }

    public function prePay($params = array()) {
        //交易子类：认证支付类预支付交易
        $txn_sub_type = '15';
        $needKeys = array('busi_id', 'busi_type', 'bindId', 'moneyFen', 'clientIp');
        $optionalKeys = array('additionalInfo');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        $bfParams['txn_sub_type'] = $txn_sub_type;
        $bfParams['risk_content'] = array('client_ip' => $params['clientIp']);
        //发送请求
        $result = $this->postRequest($txn_sub_type, $bfParams);
        if ($result['status'] == self::STATUS_SUCCESS) {
            $response = json_decode($result['output'], true);
            //todo
        }
        return $result;
    }

    public function confirmPay($params = array()) {
        //交易子类：认证支付类支付确认交易
        $txn_sub_type = '16';
        $needKeys = array('busi_id', 'busi_type', 'businessNo', 'smsCode');
        $optionalKeys = array('additionalInfo');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        $bfParams['txn_sub_type'] = $txn_sub_type;
        //发送请求
        $result = $this->postRequest($txn_sub_type, $bfParams);
        if ($result['status'] == self::STATUS_SUCCESS) {
            $response = json_decode($result['output'], true);
            //todo
        }
        return $result;
    }

    public function payResult($params = array()) {
        //交易子类：认证支付类支付确认交易
        $txn_sub_type = '31';
        $needKeys = array('origOrderId', 'origTradeDate');
        $optionalKeys = array('additionalInfo');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        $bfParams['txn_sub_type'] = $txn_sub_type;
        //发送请求
        $result = $this->postRequest($txn_sub_type, $bfParams);
        if ($result['status'] == self::STATUS_SUCCESS) {
            $response = json_decode($result['output'], true);
            //todo
        }
        return $result;
    }

    public function __call($name, $arguments) {
        $prefix = substr($name, 0, 5);
        if ($prefix == 'wepay') {
            $bfpay = new BfPay($this->scene, $this->config);
            $r = $bfpay->$name($arguments[0]);
        } elseif ($prefix == 'gwpay') {
            $bfpay = new GwPay($this->scene, $this->config);
            $r = $bfpay->$name($arguments[0]);
        }
        return $r;
    }

}
