<?php

/**
 * 宝付网关支付
 */
class GwPay extends App\Models\Epay\Epay {

    public function __construct($scene, $config = array()) {
        parent::__construct($scene, $config);
        $this->version = '4.0';
        $this->key_type = 1;
        $this->notice_type = 1;
        if ($this->env == self::ENV_DEV) {
            //测试环境
            $this->host = 'https://tgw.baofoo.com';
            $this->member_id = '100000178';
            $this->terminal_id = '10000001';
            //结果通知地址
            $this->notice_url = 'http://www.kezhanwang.cn/test1/eoasduhfaeuieiygsuyfs';
            //完成支付后跳转的地址
            $this->page_url = '';
            $this->md5_key = 'abcdefg';
        } else {
            //正式环境
            $this->host = 'https://gw.baofoo.com';
            $this->member_id = '123';
            $this->terminal_id = '456';
            //结果通知地址
            $this->notice_url = '';
            //完成支付后跳转的地址
            $this->page_url = '';
            $this->md5_key = 'abcdef';
        }
    }

    /**
     * 返回码描述
     */
    public static $return_code = array(
        '01' => '支付成功',
        '0000' => '支付失败',
        '0001' => '系统错误',
        '0002' => '订单超时',
        '0011' => '系统维护',
        '0012' => '无效商户',
        '0013' => '余额不足',
        '0014' => '超过支付限额',
        '0015' => '卡号和卡密错误',
        '0016' => '不合法的IP地址',
        '0017' => '重复订单金额不符',
        '0018' => '卡密已被使用',
        '0019' => '订单金额错误',
        '0020' => '支付的类型错误',
        '0021' => '卡类型有误',
        '0022' => '卡信息不完整',
        '0023' => '卡号、卡密、金额不正确',
        '0024' => '不能用此卡继续做交易',
        '0025' => '订单无效',
    );

    public function checkParams($params, $needKeys = array()) {
        foreach ($needKeys as $needKey) {
            if (!array_key_exists($needKey, $params)) {
                throw new KZException('参数缺失' . $needKey);
            }
            if (empty($params[$needKey])) {
                throw new KZException('参数不可为空' . $needKey);
            }
        }
    }

    public function getBfParams($params, $needKeys = array(), $optionalKeys = array()) {
        $this->config = array_merge($this->config, $params);
        $this->checkParams($this->config, $needKeys);
        $paramMap = array(
            'idcardName' => 'Username',
            'remark' => 'AdditionalInfo',
            'moneyFen' => 'OrderMoney',
            'amount' => 'Amount',
            'productName' => 'ProductName',
            'orderId' => 'TransID',
        );
        $allKeys = array_merge($needKeys, $optionalKeys);
        $bfParams = array();
        foreach ($allKeys as $pkey) {
            if ($paramMap[$pkey] && isset($this->config[$pkey])) {
                $bfParams[$paramMap[$pkey]] = $this->config[$pkey];
            }
        }
        if (in_array('bankCode', $allKeys)) {
            $bfParams['PayID'] = $this->getPayId($this->config['bankCode']);
        }
        if (in_array('AdditionalInfo', $allKeys)) {
            $bfParams['AdditionalInfo'] = $bfParams['AdditionalInfo'] ? $bfParams['AdditionalInfo'] : json_encode(array('busi_id' => $this->config['busiId'], 'busi_type' => $this->config['busiType'],));
        }
        return $bfParams;
    }

    public function postRequest($apiPath, $bfParams) {
        $bfParams['InterfaceVersion'] = $this->version;
        $bfParams['TerminalID'] = $this->terminal_id;
        $bfParams['MemberID'] = $this->member_id;
        $bfParams['TransID'] = $this->calcOrderId();
        $bfParams['KeyType'] = $this->key_type;
        $bfParams['ReturnUrl'] = $this->notice_url;
        $bfParams['PageUrl'] = $this->page_url;
        $bfParams['NoticeType'] = $this->notice_type;
        $bfParams['TradeDate'] = date('YmdHis');
        $glue = '|';
        $bfParams['Signature'] = md5($bfParams['MemberID'] . $glue .
                $bfParams['PayID'] . $glue . $bfParams['TradeDate'] . $glue .
                $bfParams['TransID'] . $glue . $bfParams['OrderMoney'] . $glue .
                $bfParams['PageUrl'] . $glue . $bfParams['ReturnUrl'] . $glue .
                $bfParams['NoticeType'] . $glue . $this->md5_key);
        $url = $this->host . $apiPath . '?' . http_build_query($bfParams);
        //记录日志
        Yii::log($this->channel . ':' . $this->scene . ' gwpay input:' . print_r($bfParams, true), CLogger::LEVEL_INFO, 'zhifu.op');
        $data = array();
        $data['scene'] = $this->scene;
        $data['channel'] = $this->channel;
        $data['status'] = self::STATUS_DOING;
        $data['money_fen'] = $this->config['moneyFen'] ? $this->config['moneyFen'] : 0;
        $data['amount'] = $this->config['amount'] ? $this->config['amount'] : 0;
        $data['product_name'] = $this->config['productName'] ? $this->config['productName'] : '';
        $data['busi_id'] = $this->config['busiId'];
        $data['busi_type'] = $this->config['busiType'];
        $data['order_id'] = $bfParams['TransID'];
        $data['flow_id'] = '';
        $data['bill_time'] = $bfParams['TradeDate'];
        $data['idcard_name'] = $this->config['idcardName'] ? $this->config['idcardName'] : '';
        $data['idcard_number'] = $this->config['idcardNumber'] ? $this->config['idcardNumber'] : '';
        $data['phone'] = $this->config['phone'] ? $this->config['phone'] : '';
        $data['userid'] = $this->config['userid'] ? $this->config['userid'] : '';
        $data['bank_code'] = $this->config['bankCode'] ? $this->config['bankCode'] : '';
        $data['bank_account'] = $this->config['bankAccount'] ? $this->config['bankAccount'] : '';
        $data['input'] = json_encode($bfParams);
        $data['additional_info'] = $bfParams['AdditionalInfo'] ? $bfParams['AdditionalInfo'] : '';
        $id = ARPayZhifuOrder::_add($data);
        return $url;
    }

    /**
     * 处理通知结果
     */
    public function gwpayNotice($result) {
        if (empty($result)) {
            throw new Exception('无数据');
        }
        Yii::log($this->channel . ':' . $this->scene . ' gwpay notice:' . print_r($result, true), CLogger::LEVEL_INFO, 'zhifu.op');
        $glue = '~|~';
        $sign = md5('MemberID=' . $result['MemberID'] . $glue .
                'TerminalID=' . $result['TerminalID'] . $glue .
                'TransID=' . $result['TransID'] . $glue .
                'Result=' . $result['Result'] . $glue .
                'ResultDesc=' . $result['ResultDesc'] . $glue .
                'FactMoney=' . $result['FactMoney'] . $glue .
                'AdditionalInfo=' . $result['AdditionalInfo'] . $glue .
                'SuccTime=' . $result['SuccTime'] . $glue . 'Md5Sign=' . $this->md5_key);
        if ($sign != $result['Md5Sign']) {
            throw new Exception('验证失败');
        }
        $update = array();
        if ($result['Result'] == '1') {
            $update['status'] = self::STATUS_SUCCESS;
            $update['paid_fen'] = $result['FactMoney'];
            $update['bank_code'] = $this->getBankCode($result['BankID']);
        } else {
            $update['status'] = self::STATUS_FAIL;
        }
        $update['code'] = $result['ResultDesc'];
        $update['remark'] = self::$return_code[$result['ResultDesc']];
        $update['output'] = json_encode($result);
        ARPayZhifuOrder::_updateByOrderid($result['TransID'], $update);
    }

    /**
     * 网关支付
     */
    public function gwpay($params = array()) {
        $apiPath = '/payindex';
        $needKeys = array('busiId', 'busiType', 'moneyFen', 'productName', 'amount', 'idcardName');
        $optionalKeys = array('remark', 'bankCode');
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        //发送请求
        $result = $this->postRequest($apiPath, $bfParams);
        return $result;
    }

    /**
     * 网关支付结果查询
     */
    public function gwpayResult($params = array()) {
        $apiPath = '/order/query';
        $needKeys = array('orderId');
        $optionalKeys = array();
        //检查参数,拼装请求
        $bfParams = $this->getBfParams($params, $needKeys, $optionalKeys);
        $bfParams['TerminalID'] = $this->terminal_id;
        $bfParams['MemberID'] = $this->member_id;
        $glue = '|';
        $bfParams['MD5Sign'] = md5($bfParams['MemberID'] . $glue .
                $bfParams['TerminalID'] . $glue .
                $bfParams['TransID'] . $glue . $this->md5_key);
        //发送请求
        $url = $this->host . $apiPath;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_POST, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);
        curl_setopt($ch, CURLOPT_SSLVERSION, 6);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT_MS, 1000);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($bfParams));
        $content = curl_exec($ch);
        if ($content) {
            Yii::log($this->channel . ':' . $this->scene . ' gwpay result:' . $content, CLogger::LEVEL_INFO, 'zhifu.op');
        } else {
            Yii::log($this->channel . ':' . $this->scene . ' gwpay resulterror:' . curl_error($ch), CLogger::LEVEL_INFO, 'zhifu.op');
        }
        curl_close($ch);
        $result = explode($glue, $content);
        $update_result = false;
        //更新数据库
        if (is_array($result) && $result[3] && $result[6]) {
            $sign = md5($result[0] . $glue . $result[1] . $glue .
                    $result[2] . $glue . $result[3] . $glue .
                    $result[4] . $glue . $result[5] . $glue . $this->md5_key);
            if ($sign == $result[6]) {
                $update['code'] = $result[3];
                $update['paid_fen'] = $result[4];
                $update['output'] = $content;
                if ($result[3] == 'Y') {
                    $update['status'] = self::STATUS_SUCCESS;
                    $update['remark'] = '成功';
                } elseif ($result[3] == 'F') {
                    $update['status'] = self::STATUS_FAIL;
                    $update['remark'] = '失败';
                } elseif ($result[3] == 'P') {
                    $update['status'] = self::STATUS_DOING;
                    $update['remark'] = '处理中';
                } elseif ($result[3] == 'N') {
                    $update['status'] = self::STATUS_FAIL;
                    $update['remark'] = '没有订单';
                }
                ARPayZhifuOrder::_updateByOrderid($bfParams['TransID'], $update);
                $update_result = true;
            }
        }
        return $update_result;
    }

    /**
     * 银行编码对照表
     */
    public static $bank_map = array(
        '102' => '3001',
    );

    public function getPayId($code) {
        if (!$code) {
            return '';
        }
        $pay_id = self::$bank_map[$code];
        if (!$pay_id) {
            throw new KZException('不支持该银行');
        }
        return $pay_id;
    }

    public function getBankCode($pay_id) {
        if (!$pay_id) {
            return '';
        }
        foreach (self::$bank_map as $k => $v) {
            if ($v == $pay_id) {
                return $k;
            }
        }
        return '';
    }

}
