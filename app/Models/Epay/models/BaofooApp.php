<?php

require_once("baofoo/BFRSA.php");
require_once("baofoo/SdkXML.php");
require_once("baofoo/Log.php");
require_once("baofoo/HttpClient.php");

class BaofooApp extends App\Models\Epay\Epay {

    private $version; //版本号
    private $input_charset; //字符集
    private $language; //网关页面显示语言种类
    private $biz_type; //接入类型
    private $id_card_type; //证件类型
    private $data_type; //加密数据类型
    private $member_id; //商户号
    private $terminal_id; //终端号
    private $return_url; //服务器通知地址
    private $bf_public_cer;
    private $bf_private_cer;
    private $bf_private_cer_password;

    public function __construct($scene, $config = array()) {
        parent::__construct($scene, $config);
        $this->input_charset = 1; //固定选择值：1，2，3。1代表UTF-8；2代表GBK；3代表GB2312；
        $this->language = 1; //中文
        $this->biz_type = '0000'; //储蓄卡支付
        $this->id_card_type = '01'; //身份证
        $this->data_type = 'json';
        if ($this->env == self::ENV_DEV) {
            //测试环境
            $this->bf_public_cer = dirname(__FILE__) . '/baofoo/cer_test/baofoo_pub.cer';
            $this->bf_private_cer = dirname(__FILE__) . '/baofoo/cer_test/bfkey_100000178@@100000916.pfx';
            $this->bf_private_cer_password = '100000178_204500';
            $this->member_id = '100000178';
            $this->terminal_id = '100000916';
            $this->return_url = 'http://www.kezhanwang.cn/test1/eoasduhfaeuieiygsuyfs';
        } else {
            //正式环境
            $this->bf_public_cer = dirname(__FILE__) . '/baofoo/cer/b2k_public.cer';
            $this->bf_private_cer = dirname(__FILE__) . '/baofoo/cer/k2b_private.pfx';
            $this->bf_private_cer_password = 'kezhan';
            $this->member_id = '1157332';
            $this->terminal_id = '34276';
            $this->return_url = 'http://pay.kezhanwang.cn/epay/app/rechargecallback';
        }
    }

    /**
     * 支持代扣的银行
     */
    private $kz_bf_banks = array(
        102 => 'ICBC', //中国工商银行
        103 => 'ABC', //中国农业银行
        104 => 'BOC', //中国银行
        105 => 'CCB', //中国建设银行
        301 => 'BCOM',    //  交通银行
        302 => 'CITIC', //中信银行
        303 => 'CEB', //中国光大银行
        306 => 'GDB', //广东发展银行
        307 => 'PAB', //平安银行
        308 => 'CMB', //招商银行
        309 => 'CIB', //兴业银行
        403 => 'PSBC', //中国邮政储蓄银行
        301 => 'BCOM', //中国交通银行
    );

    /**
     * 检查必填参数
     */
    public function checkParams($params, $needKeys = array()) {
        foreach ($needKeys as $needKey) {
            if (!array_key_exists($needKey, $params)) {
                throw new KZException('参数缺失:' . $needKey);
            }
            if (empty($params[$needKey])) {
                throw new KZException('参数不可为空:' . $needKey);
            }
        }
        if (isset($params['bank_code']) && !array_key_exists($params['bank_code'], $this->kz_bf_banks)) {
            throw new KZException('不支持当前银行:' . $params['bank_code']);
        }
    }

    /**
     * 获取宝付参数
     */
    public function getBfParams($params, $needKeys = array(), $optionalKeys = array()) {
        $this->config = array_merge($this->config, $params);
        $allKeys = array_merge($needKeys, $optionalKeys);
        $this->checkParams($this->config, $needKeys);
        if (in_array('additionalInfo', $allKeys)) {
            $this->config['additionalInfo'] = $this->config['additionalInfo'] ? $this->config['additionalInfo'] : json_encode(array('busi_id' => $this->config['busi_id'], 'busi_type' => $this->config['busi_type'],));
        }
        $bfParams = array(
            'biz_type' => $this->biz_type,
            'terminal_id' => $this->terminal_id,
            'member_id' => $this->member_id,
            'trans_serial_no' => $this->calcFlowId(),
            'trans_id' => $this->calcOrderId(),
            'id_card_type' => $this->id_card_type,
            'trade_date' => date('YmdHis'),
        );
        if (in_array('bank_code', $allKeys)) {
            $this->config['kzBankCode'] = $this->config['bank_code'];
            $bfParams['pay_code'] = $this->kz_bf_banks[$this->config['bank_code']];
        }
        $paramMap = array(
            'bank_account' => 'acc_no',
            'idcard_number' => 'id_card',
            'idcard_name' => 'id_holder',
            'phone' => 'mobile',
            'validDate' => 'valid_date',
            'validNo' => 'valid_no',
            'smsCode' => 'sms_code',
            'additionalInfo' => 'additional_info',
            'bindId' => 'bind_id',
            'money_fen' => 'txn_amt',
            'riskContent' => 'risk_content',
            'businessNo' => 'business_no',
            'orderId' => 'trans_id',
            'origOrderId' => 'orig_trans_id',
            'origTradeDate' => 'orig_trade_date',
            'commodityName' => 'commodity_name',
            'commodityAmount' => 'commodity_amount',
            'userName' => 'user_name'
        );
        foreach ($allKeys as $pkey) {
            if ($paramMap[$pkey] && isset($this->config[$pkey])) {
                $bfParams[$paramMap[$pkey]] = $this->config[$pkey];
            }
        }
        return $bfParams;
    }

    /**
     * 请求接口
     */
    public function postRequest($url, $bfParams, $decrypt = false) {
        $jsonstr = str_replace("\\/", "/", json_encode($bfParams)); //转JSON
        $bfRsa = new BFRSA($this->bf_private_cer, $this->bf_public_cer, $this->bf_private_cer_password); //实例化加密类。
        $encrypted = $bfRsa->encryptedByPrivateKey($jsonstr); //先BASE64进行编码再RSA加密
        $postArray = array(
            'version' => $this->version,
            'input_charset' => $this->input_charset,
            'language' => $this->language,
            'terminal_id' => $this->terminal_id,
            'member_id' => $this->member_id,
            'data_type' => $this->data_type,
        );
        if ($bfParams['txn_type']) {
            $postArray['txn_type'] = $bfParams['txn_type'];
        }
        if ($bfParams['txn_sub_type']) {
            $postArray['txn_sub_type'] = $bfParams['txn_sub_type'];
        }
        $postArray['data_content'] = $encrypted;
        //记录日志
        Yii::log($this->channel . ':' . $this->scene . " create input:" . print_r($bfParams, true), CLogger::LEVEL_INFO, 'zhifu.op');
        $return = HttpClient::Post($postArray, $url);  //发送请求到宝付服务器，并输出返回结果。
        if ($decrypt) {
            $return = $bfRsa->decryptByPublicKey($return); //解密返回的报文
        }
        $result = json_decode($return, true);
        Yii::log($this->channel . ':' . $this->scene . ' create result. orderId: ' . $bfParams['trans_id'] . '. result:' . print_r($result, true), CLogger::LEVEL_INFO, 'zhifu.op');
        //检查返回结果是否成功
        if (!is_array($result) || (!array_key_exists('retCode', $result) && !array_key_exists('resp_code', $result))) {
            throw new KZException('请求宝付失败', 1);
        }
        return $result;
    }

    /**
     * 充值第一步
     */
    public function recharge($params = array()) {
        $this->version = '4.0.0.0';
        $needKeys = array(
            'idcard_number', 'idcard_name', 'money_fen',
            'phone', 'bank_code', 'bank_account',
        );
        $optionalKeys = array(
            'busi_id', 'busi_type', 'additionalInfo', 'validDate', 'validNo',
            'commodityName', 'commodityAmount', 'userName'
        );
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        //交易类型
        $bfParams['txn_type'] = '03311';
        //交易子类：支付类交易
        $bfParams['txn_sub_type'] = '02';
        //结果通知地址
        $bfParams['return_url'] = $this->return_url;
        $this->addOrder($bfParams);
        //发送请求
        if ($this->env == self::ENV_DEV) {
            //测试环境
            $url = 'https://tgw.baofoo.com/apipay/sdk';
        } else {
            //正式环境
            $url = 'https://gw.baofoo.com/apipay/sdk';
        }
        $result = $this->postRequest($url, $bfParams);
        $update = array();
        if (in_array($result['retCode'], array('0000', '0003', '0004', '0005'))) {
            $update['status'] = self::STATUS_DOING;
        } elseif (array_key_exists($result['retCode'], $this->code_desp_recharge)) {
            $update['status'] = self::STATUS_FAIL;
        }
        if (!$result['tradeNo']) {
            $update['status'] = self::STATUS_FAIL;
        }
        $update['remark'] = $this->code_desp_recharge[$result['retCode']];
        if ($result['retMsg']) {
            $update['remark'] .= '（' . $result['retMsg'] . '）';
        }
        $update['output'] = json_encode($result);
        ARPayZhifuOrder::_updateByOrderid($bfParams['trans_id'], $update);
        if ($update['status'] == self::STATUS_FAIL) {
//            if ($result['retMsg']) {
//                throw new Exception($result['retMsg'], 1);
//            } else {
//                throw new Exception('创建交易失败', 1);
//            }
        }
        $result['orderid'] = $bfParams['trans_id'];
        return $result;
    }

    /**
     * 充值第一步
     */
    public function rechargeNew($params = array()) {
        $this->version = '4.0.0.0';
        $needKeys = array(
            'money_fen', 'idcard_number', 'idcard_name',
            'bank_code', 'bank_account', 'phone'
        );
        $optionalKeys = array(
            'busi_id', 'busi_type', 'additionalInfo', 'validDate', 'validNo',
            'commodityName', 'commodityAmount', 'userName'
        );
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        //交易类型
        $bfParams['txn_type'] = '03311';
        //交易子类：支付类交易
        $bfParams['txn_sub_type'] = '02';
        //结果通知地址
        $bfParams['return_url'] = $this->return_url;
        $this->addOrder($bfParams);
        //发送请求
        if ($this->env == self::ENV_DEV) {
            //测试环境
            $url = 'https://tgw.baofoo.com/apipay/sdk';
        } else {
            //正式环境
            $url = 'https://gw.baofoo.com/apipay/sdk';
        }
        $result = $this->postRequest($url, $bfParams);
        $update = array();
        if (in_array($result['retCode'], array('0000', '0003', '0004', '0005'))) {
            $update['status'] = self::STATUS_DOING;
        } elseif (array_key_exists($result['retCode'], $this->code_desp_recharge)) {
            $update['status'] = self::STATUS_FAIL;
        }
        if (!$result['tradeNo']) {
            $update['status'] = self::STATUS_FAIL;
        }
        $update['remark'] = $this->code_desp_recharge[$result['retCode']];
        if ($result['retMsg']) {
            $update['remark'] .= '（' . $result['retMsg'] . '）';
        }
        $update['output'] = json_encode($result);
        ARPayZhifuOrder::_updateByOrderid($bfParams['trans_id'], $update);
        if ($update['status'] == self::STATUS_FAIL) {
            throw new Exception('创建交易失败', 1);
        }
        $result['orderid'] = $bfParams['trans_id'];
        $result_new = KZPayCommon::sgetOrderStatus($result['orderid']);
        $result_new['trade_no'] = $result['tradeNo'];
        return $result_new;
    }

    /**
     * 查询支付结果
     */
    public function query($params = array()) {
        $this->version = '4.0.0.1';
        if ($params['order_id']) {
            $params['origOrderId'] = $params['order_id'];
        }
        $needKeys = array(
            'origOrderId'
        );
        $optionalKeys = array(
            'additionalInfo'
        );
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        //发送请求
        if ($this->env == self::ENV_DEV) {
            //测试环境
            $url = 'https://tgw.baofoo.com/apipay/queryQuickOrder';
        } else {
            //正式环境
            $url = 'https://gw.baofoo.com/apipay/queryQuickOrder';
        }
        $result = $this->postRequest($url, $bfParams, true);
        $code_desp_query = array(
            '0000' => '交易成功',
            'FI00002' => '交易结果未知，请稍后查询',
            'FI00007' => '交易失败',
            'FI00014' => '订单不存在',
            'FI00054' => '订单未支付',
        );
        $update = array();
        if (in_array($result['resp_code'], array('0000'))) {
            $update['status'] = self::STATUS_SUCCESS;
        } elseif (in_array($result['resp_code'], array('FI00002'))) {
            $update['status'] = self::STATUS_DOING;
        } elseif (array_key_exists($result['resp_code'], $code_desp_query)) {
            $update['status'] = self::STATUS_FAIL;
        }
        $update['remark'] = $code_desp_query[$result['resp_code']];
        if ($result['resp_msg']) {
            $update['remark'] .= '（' . $result['resp_msg'] . '）';
        }
        $order = ARPayZhifuOrder::getByOrderid($bfParams['orig_trans_id']);
        ARPayZhifuOrder::_updateByOrderid($bfParams['orig_trans_id'], $update);
        if ($update['status'] == $order['status']) {
            $result['update'] = false;
        } else {
            $result['update'] = true;
        }
        $result['bill_time'] = $order['bill_time'];
        $result['money_fen'] = $order['money_fen'];
        return $result;
    }
    
    /**
     * 查询支付结果
     */
    public function queryNew($params = array()) {
        $this->version = '4.0.0.1';
        if ($params['order_id']) {
            $params['origOrderId'] = $params['order_id'];
        }
        $needKeys = array(
            'origOrderId'
        );
        $optionalKeys = array(
            'additionalInfo'
        );
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        //发送请求
        if ($this->env == self::ENV_DEV) {
            //测试环境
            $url = 'https://tgw.baofoo.com/apipay/queryQuickOrder';
        } else {
            //正式环境
            $url = 'https://gw.baofoo.com/apipay/queryQuickOrder';
        }
        $result = $this->postRequest($url, $bfParams, true);
        $code_desp_query = array(
            '0000' => '交易成功',
            'FI00002' => '交易结果未知，请稍后查询',
            'FI00007' => '交易失败',
            'FI00014' => '订单不存在',
            'FI00054' => '订单未支付',
        );
        $update = array();
        if (in_array($result['resp_code'], array('0000'))) {
            $update['status'] = self::STATUS_SUCCESS;
        } elseif (in_array($result['resp_code'], array('FI00002'))) {
            $update['status'] = self::STATUS_DOING;
        } elseif (array_key_exists($result['resp_code'], $code_desp_query)) {
            $update['status'] = self::STATUS_FAIL;
        }
        $update['remark'] = $code_desp_query[$result['resp_code']];
        if ($result['resp_msg']) {
            $update['remark'] .= '（' . $result['resp_msg'] . '）';
        }
        ARPayZhifuOrder::_updateByOrderid($bfParams['orig_trans_id'], $update);
        return KZPayCommon::sgetOrderStatus($bfParams['orig_trans_id']);
    }

    /**
     * 根据后台通知更新订单
     */
    public function updateOrder($data_encrypted) {
        $bfRsa = new BFRSA($this->bf_private_cer, $this->bf_public_cer, $this->bf_private_cer_password);
        $data_json = $bfRsa->decryptByPublicKey($data_encrypted);
        $result = json_decode($data_json, true);
        $update = array();
        if ($result['resp_code'] == '0000') {
            $update['status'] = self::STATUS_SUCCESS;
        } elseif (in_array($result['retCode'], array('0003', '0004', '0005'))) {
            $update['status'] = self::STATUS_DOING;
        } elseif (array_key_exists($result['retCode'], $this->code_desp_recharge)) {
            $update['status'] = self::STATUS_FAIL;
        }
        $update['remark'] = $this->code_desp_recharge[$result['resp_code']];
        if ($result['resp_msg']) {
            $update['remark'] .= '（' . $result['resp_msg'] . '）';
        }
        ARPayZhifuOrder::_updateByOrderid($result['trans_id'], $update);
        return $result;
    }

    /**
     * 添加一条订单数据
     */
    public function addOrder($bfParams) {
        //记数据库
        $data = array();
        $data['scene'] = $this->scene;
        $data['channel'] = 'baofoo';
        $data['status'] = self::STATUS_DOING;
        $data['money_fen'] = $this->config['money_fen'] ? $this->config['money_fen'] : 0;
        $data['busi_id'] = $this->config['busi_id'] ? $this->config['busi_id'] : 0;
        $data['busi_type'] = $this->config['busi_type'] ? $this->config['busi_type'] : '';
        $data['bill_date'] = $this->config['bill_date'] ? $this->config['bill_date'] : '';
        $data['order_id'] = $bfParams['trans_id'];
        $data['flow_id'] = $bfParams['trans_serial_no'];
        $data['bill_time'] = $bfParams['trade_date'];
        $data['idcard_name'] = $this->config['idcard_name'] ? $this->config['idcard_name'] : '';
        $data['idcard_number'] = $this->config['idcard_number'] ? $this->config['idcard_number'] : '';
        $data['phone'] = $this->config['phone'] ? $this->config['phone'] : '';
        $data['userid'] = $this->config['userid'] ? $this->config['userid'] : '';
        $data['bank_code'] = $this->config['kzBankCode'] ? $this->config['kzBankCode'] : '';
        $data['bank_account'] = $this->config['bank_account'] ? $this->config['bank_account'] : '';
        $data['input'] = json_encode($bfParams);
        $data['additional_info'] = $bfParams['additional_info'] ? $bfParams['additional_info'] : '';
        $id = ARPayZhifuOrder::_add($data);
        return $id;
    }

    /**
     * 第一步应答码
     */
    private $code_desp_recharge = array(
        '0000' => '创建订单成功',
        '0001' => '交易失败。详情请咨询宝付',
        '0002' => '系统未开放或暂时关闭，请稍后再试',
        '0003' => '交易通讯超时，请发起查询交易',
        '0004' => '交易已受理，请稍后查询交易结果',
        '0005' => '交易状态未明，请查询对账结果',
        '0006' => '报文格式错误',
        '0007' => '验证签名失败',
        '0008' => '报文交易要素缺失',
        '0009' => '重复交易',
        '0010' => '交易信息不存在',
        '0011' => '订单重复提交',
        '0012' => '报文参数超出范围',
        '0014' => '此商户暂不支持该业务',
        '0015' => '此商户暂不支持该银行',
        '0016' => '该终端不存在',
        '0017' => '该商户不存在',
        '0018' => '该商户未开通',
        '0019' => '该终端未开通',
        '0020' => '错误的商户号，终端号',
        '0021' => '错误的交易密文',
        '0022' => '信息不匹配',
        '0024' => '报文格式错误',
        '0030' => '商户状态不正确,请确认是否开通活启用',
        '0031' => '商户终端状态不正确,请确认是否开通或启用',
        '0032' => '商户未绑定该终端',
        '0033' => '商户该终端下未开通此类型交易',
        '0034' => '绑定关系不存在',
        '0050' => '支付超时',
        '0060' => '存在钓鱼风险，交易中止',
        '0061' => '请使用 HTTPS 安全通讯请求',
        '0062' => '交易金额不正确',
        '0063' => '支付卡号校验失败',
        '0064' => '银行卡姓名校验失败',
        '0201' => '密文和明文中参数不一致,请确认是否被篡改！',
        '0202' => '请确认是否发送短信,当前交易必须通过短信验证！',
        '0203' => '当前交易信息与短信交易信息不一致,请核对信息',
        '0204' => '绑卡失败',
        '0300' => '发送验证码超时，请稍后再试',
        '0301' => '系统繁忙，请稍后再试',
        '0302' => '该交易有风险,请联系宝付',
        '0303' => '交易结果未知，请稍后查询',
        '0304' => '交易正在处理，请勿重复支付',
        '0305' => '暂不支持该信用卡的支付功能',
        '0306' => '卡类型和 biz_type 接入类型不一致',
        '0307' => 'txn_sub_type 字段值非法',
        '0308' => '银行卡号和银行编码不一致',
        '0309' => '系统异常，请联系宝付',
        '0310' => '系统异常，请稍后再试',
        '0311' => '商户流水号不能重复',
        '0312' => '此笔订单未支付成功',
        '0313' => '不支持信用卡交易',
        '0367' => '订单取消',
        '0368' => '请求参数缺失',
        '1000' => '系统异常，请稍后再试',
        '1001' => '转换加密串到对象异常',
        '1002' => '短信调用异常',
        '1003' => '短信验证异常',
        '1004' => '数据重复插入',
        '1005' => '数据更新异常',
        '1006' => '数据查询有误',
        '1007' => 'Fi 支付处理失败',
        '1008' => 'Fi 查询处理失败',
    );

}
