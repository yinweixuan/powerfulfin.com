<?php

require_once('baofoo/BFRSA.php');
require_once('baofoo/SdkXML.php');
require_once('baofoo/Log.php');
require_once('baofoo/HttpClient.php');
require_once('baofoo/BFRSAUtil.php');

use \App\Models\ActiveRecord\ARPFZhifuOrder;
use \App\Models\Epay\models\KZPayCommon;

/**
 * 宝付支付-协议支付
 */
class BaofooPayAP extends App\Models\Epay\Epay {

    private $version; //版本号
    private $card_type; //银行卡类型
    private $id_card_type; //证件类型
    private $member_id; //商户号
    private $terminal_id; //终端号
    private $request_url; //服务器请求地址
    private $return_url; //服务器通知地址
    private $bf_public_cer;
    private $bf_private_cer;
    private $bf_private_cer_password;

    public function __construct($scene, $config = array()) {
        parent::__construct($scene, $config);
        $this->version = '4.0.0.0'; //版本号
        $this->card_type = '101'; //卡类型（借记卡）
        $this->id_card_type = '01'; //身份证
        if ($this->env == self::ENV_DEV) {
            //测试环境
            $this->bf_public_cer = PATH_BASE . '/cer/baofoo/baofoo/cer_test/bfkey_100025773@@200001173.cer';
            $this->bf_private_cer = PATH_BASE . '/cer/baofoo/baofoo/cer_test/bfkey_100025773@@200001173.pfx';
            $this->bf_private_cer_password = env('BAOFOO_AP_CER_PASSWORD_DEV');
            $this->member_id = env('BAOFOO_AP_MEMBER_ID_DEV');
            $this->terminal_id = env('BAOFOO_AP_TERMINAL_ID_DEV');
            $this->request_url = 'https://vgw.baofoo.com/cutpayment/protocol/backTransRequest';
            $this->return_url = 'http://www.kezhanwang.cn/test1/eoasduhfaeuieiygsuyfs';
        } else {
            //正式环境
            $this->bf_public_cer = dirname(__FILE__) . '/baofoo/cer/b2k_public.cer';
            $this->bf_private_cer = dirname(__FILE__) . '/baofoo/cer/k2b_private.pfx';
            $this->bf_private_cer_password = env('BAOFOO_AP_CER_PASSWORD');
            $this->member_id = env('BAOFOO_AP_MEMBER_ID');
            $this->terminal_id = env('BAOFOO_AP_TERMINAL_ID');
            $this->request_url = 'https://public.baofoo.com/cutpayment/protocol/backTransRequest';
            $this->return_url = 'http://pay.kezhanwang.cn/epay/notify/baofooappay';
        }
    }

    /**
     * 按要求获取数组sha1
     */
    public function getSha1($data) {
        ksort($data);
        foreach ($data as $k => $v) {
            if ($k != 'signature' && ($v || $v === 0)) {
                $arr[] = $k . '=' . $v;
            }
        }
        $params_str = implode('&', $arr);
        return sha1($params_str);
    }

    /**
     * 计算签名
     */
    public function sign($data) {
        $pkcs12 = file_get_contents($this->bf_private_cer);
        $private_key_arr = array();
        openssl_pkcs12_read($pkcs12, $private_key_arr, $this->bf_private_cer_password);
        $private_key = $private_key_arr['pkey'];
        $signature = '';
        openssl_sign($this->getSha1($data), $signature, $private_key);
        return bin2hex($signature);
    }

    /**
     * 验证签名
     */
    public function verifySign($data) {
        $public_key = file_get_contents($this->bf_public_cer);
        $r = openssl_verify($this->getSha1($data), hex2bin($data['signature']), $public_key);
        return $r ? true : false;
    }

    /**
     * 生成aes key
     */
    public function getAesKey() {
        $str = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($str), 0, 16);
    }

    /**
     * 加密敏感信息
     */
    public function aesEncrypt($aes_key, $datastr) {
        $iv = $aes_key;
//        $encrypted = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $aes_key, base64_encode($datastr), MCRYPT_MODE_CBC, $iv);
        $encrypted = openssl_encrypt(base64_encode($datastr), 'aes-128-cbc', $aes_key, OPENSSL_RAW_DATA, $iv);
        $data = bin2hex($encrypted);
        return $data;
    }

    /**
     * 解密敏感信息
     */
    public function aesDecrypt($dgtl_envlp, $datastr) {
        $decrypted_dgtl_envlp = BFRSAUtil::decryptByPFXFile($dgtl_envlp, $this->bf_private_cer, $this->bf_private_cer_password);
        $arr = explode('|', trim($decrypted_dgtl_envlp));
        $aes_key = $arr[1];
        $iv = $aes_key;
        return base64_decode(openssl_decrypt(hex2bin($datastr), 'aes-128-cbc', $aes_key, OPENSSL_RAW_DATA | OPENSSL_ZERO_PADDING, $iv));
    }

    /**
     * 补完参数
     */
    public function completeParams($params) {
        $base_params = array(
            'send_time' => date('Y-m-d H:i:s'),
            'msg_id' => $this->calcFlowId(),
            'terminal_id' => $this->terminal_id,
            'member_id' => $this->member_id,
            'version' => $this->version
        );
        $bf_params = array_merge($base_params, $params);
        $bf_params['signature'] = $this->sign($bf_params);
        return $bf_params;
    }

    /**
     * 请求接口
     */
    public function postRequest($bf_params) {
        \Yii::log($this->channel . ':' . $this->scene . ',flow_id: ' . $bf_params['msg_id'] . ',input:' . print_r($bf_params, true), 'zhifu.op');
        $return = HttpClient::Post($bf_params, $this->request_url);  //发送请求到宝付服务器，并输出返回结果。
        $result = array();
        parse_str($return, $result);
        \Yii::log($this->channel . ':' . $this->scene . ',flow_id: ' . $bf_params['msg_id'] . ',result:' . print_r($result, true), 'zhifu.op');
        //检查返回结果是否成功
        if (!is_array($result) || !array_key_exists('signature', $result)) {
            throw new KZException('请求宝付失败');
        }
        if (!$this->verifySign($result)) {
            throw new KZException('验签失败');
        }
        return $result;
    }

    /**
     * 协议支付：预绑卡
     */
    public function preBind($params) {
        $bf_params = array();
        $bf_params['txn_type'] = '01';
        $bf_params['user_id'] = $params['uid'];
        $bf_params['card_type'] = $this->card_type;
        $bf_params['id_card_type'] = $this->id_card_type;
        $aes_key = $this->getAesKey();
        $bf_params['dgtl_envlp'] = BFRSAUtil::encryptByCERFile('01|' . $aes_key, $this->bf_public_cer);
        $acc_info = $params['bank_account'] . '|' . $params['idcard_name'] . '|' .
                $params['idcard_number'] . '|' . $params['phone'] . '||';
        $bf_params['acc_info'] = $this->aesEncrypt($aes_key, $acc_info);
        $completed_bf_params = $this->completeParams($bf_params);
        $result = $this->postRequest($completed_bf_params);
        $r = array();
        if ($result['resp_code'] == 'S' && $result['dgtl_envlp'] && $result['unique_code']) {
            $r['unique_code'] = $this->aesDecrypt($result['dgtl_envlp'], $result['unique_code']);
        }
        if ($r['unique_code']) {
            return $r;
        } else {
            if ($result['biz_resp_msg']) {
                throw new Exception($result['biz_resp_msg']);
            } else {
                throw new Exception('请求失败');
            }
        }
    }

    /**
     * 协议支付：确认绑卡
     */
    public function bind($params) {
        $bf_params = array();
        $bf_params['txn_type'] = '02';
        $aes_key = $this->getAesKey();
        $bf_params['dgtl_envlp'] = BFRSAUtil::encryptByCERFile('01|' . $aes_key, $this->bf_public_cer);
        $unique_code = $params['unique_code'] . '|' . $params['sms_code'];
        $bf_params['unique_code'] = $this->aesEncrypt($aes_key, $unique_code);
        $completed_bf_params = $this->completeParams($bf_params);
        $result = $this->postRequest($completed_bf_params);
        $r = array();
        if ($result['resp_code'] == 'S' && $result['dgtl_envlp'] && $result['protocol_no']) {
            $r['protocol_no'] = $this->aesDecrypt($result['dgtl_envlp'], $result['protocol_no']);
        }
        if ($r['protocol_no']) {
            return $r;
        } else {
            if ($result['biz_resp_msg']) {
                throw new Exception($result['biz_resp_msg']);
            } else {
                throw new Exception('请求失败');
            }
        }
    }

    /**
     * 协议支付：绑卡结果查询
     */
    public function queryBind($params) {
        $bf_params = array();
        $bf_params['txn_type'] = '03';
        $aes_key = $this->getAesKey();
        $bf_params['dgtl_envlp'] = BFRSAUtil::encryptByCERFile('01|' . $aes_key, $this->bf_public_cer);
        if (strlen($params['uid']) > 12) {
            $bf_params['acc_no'] = $this->aesEncrypt($aes_key, $params['uid']);
        } else {
            $bf_params['user_id'] = $params['uid'];
        }
        $completed_bf_params = $this->completeParams($bf_params);
        $result = $this->postRequest($completed_bf_params);
        if ($result['resp_code'] == 'S' && $result['dgtl_envlp'] && $result['protocols']) {
            $protocols = $this->aesDecrypt($result['dgtl_envlp'], $result['protocols']);
        }
        $protocol_arr = explode(';', $protocols);
        $r = array();
        foreach ($protocol_arr as $str) {
            if ($str) {
                $item = explode('|', $str);
                $temp = array();
                $temp['protocol_no'] = $item[0];
                $temp['uid'] = $item[1];
                $temp['bank_account'] = $item[2];
                $temp['bank_code'] = $item[3];
                $temp['bank_name'] = $item[4];
                $r[] = $temp;
            }
        }
        return $r;
    }

    /**
     * 协议支付：直接支付
     */
    public function pay($params) {
        $bf_params = array();
        $bf_params['txn_type'] = '08';
        $bf_params['trans_id'] = $params['order_id'] ? $params['order_id'] : $this->calcOrderId();
        if ($params['order_id']) {
            $order = ARPFZhifuOrder::getByOrderid($params['order_id']);
            if ($order) {
                return KZPayCommon::sgetOrderStatus($params['order_id']);
            }
        }
        $bf_params['user_id'] = $params['uid'];
        $bf_params['txn_amt'] = $params['money_fen'];
        $bf_params['return_url'] = $this->return_url;
        $bf_params['card_info'] = '';
        $bf_params['risk_item'] = json_encode(array());
        $aes_key = $this->getAesKey();
        $bf_params['dgtl_envlp'] = BFRSAUtil::encryptByCERFile('01|' . $aes_key, $this->bf_public_cer);
        $bf_params['protocol_no'] = $this->aesEncrypt($aes_key, $params['protocol_no']);
        $completed_bf_params = $this->completeParams($bf_params);
        $this->addOrder($completed_bf_params);
        $result = $this->postRequest($completed_bf_params);
        $this->updateOrder($result, $bf_params['trans_id']);
        return KZPayCommon::sgetOrderStatus($bf_params['trans_id']);
    }

    /**
     * 协议支付：支付结果查询
     */
    public function queryPay($params) {
        $bf_params = array();
        $bf_params['txn_type'] = '07';
        $bf_params['orig_trans_id'] = $params['order_id'];
        $bf_params['orig_trade_date'] = $params['bill_time'];
        $completed_bf_params = $this->completeParams($bf_params);
        $result = $this->postRequest($completed_bf_params);
        $this->updateOrder($result, $params['order_id']);
        return KZPayCommon::sgetOrderStatus($params['order_id']);
    }

    /**
     * 根据后台通知更新订单
     */
    public function updateOrderByCallback($result) {
        if (!is_array($result) || !array_key_exists('signature', $result)) {
            return;
        }
        if (!$this->verifySign($result)) {
            return;
        }
        $this->updateOrder($result);
    }

    /**
     * 添加一条订单数据
     */
    public function addOrder($bf_params) {
        $data = array();
        $data['scene'] = $this->scene;
        $data['channel'] = $this->channel;
        $data['status'] = self::STATUS_DOING;
        $data['busi_id'] = $this->config['busi_id'] ? $this->config['busi_id'] : 0;
        $data['busi_type'] = $this->config['busi_type'] ? $this->config['busi_type'] : '';
        $data['money_fen'] = $bf_params['txn_amt'] ? $bf_params['txn_amt'] : 0;
        $data['bill_date'] = '';
        $data['order_id'] = $bf_params['trans_id'];
        $data['flow_id'] = $bf_params['msg_id'];
        $data['bill_time'] = date('YmdHis', strtotime($bf_params['send_time']));
        $data['idcard_name'] = '';
        $data['idcard_number'] = '';
        $data['phone'] = '';
        $data['userid'] = $bf_params['user_id'];
        $data['bank_code'] = '';
        $data['bank_account'] = '';
        $data['input'] = json_encode($bf_params);
        $data['additional_info'] = '';
        $id = ARPFZhifuOrder::add($data);
        return $id;
    }

    /**
     * 更新订单数据
     */
    public function updateOrder($result, $order_id = null) {
        $success_code = array(
            '0000', 'BF00114'
        );
        $doing_code = array(
            'BF00100', 'BF00112', 'BF00113', 'BF00115'
        );
        $update = array();
        if ($result['resp_code'] == 'S' && in_array($result['biz_resp_code'], $success_code)) {
            $update['status'] = self::STATUS_SUCCESS;
        } elseif ($result['resp_code'] == 'I' || in_array($result['biz_resp_code'], $doing_code)) {
            $update['status'] = self::STATUS_DOING;
        } else {
            $update['status'] = self::STATUS_FAIL;
        }
        $update['remark'] = $result['biz_resp_msg'];
        $update['output'] = json_encode($result);
        if (!$order_id) {
            $order_id = $result['trans_id'];
        }
        ARPFZhifuOrder::updateByOrderid($order_id, $update);
        return $result;
    }

}
