<?php

class KZPayEncrypt extends KZPay {

    private $kz_epay_private_key;
    public $kz_loan_public_key;

    public function __construct($scene, $config = array()) {
        parent::__construct($scene, $config);
        if ($this->env == self::ENV_DEV) {
            //开发
            $this->kz_epay_private_key = file_get_contents(dirname(__FILE__) . '/cer/dev/kz_epay_private.pem');
            $this->kz_loan_public_key = file_get_contents(dirname(__FILE__) . '/cer/dev/kz_loan_public.pem');
        } else {
            //正式
            $this->kz_epay_private_key = file_get_contents(dirname(__FILE__) . '/cer/prod/kz_epay_private.pem');
            $this->kz_loan_public_key = file_get_contents(dirname(__FILE__) . '/cer/prod/kz_loan_public.pem');
        }
    }

    public function decrypt($data) {
        $len = 256;
        $result = '';
        $data_str = base64_decode($data);
        for ($i = 0; $i < strlen($data_str); $i += $len) {
            $decrypted = '';
            $r = openssl_private_decrypt(substr($data_str, $i, $len), $decrypted, $this->kz_epay_private_key);
            if ($r) {
                $result .= $decrypted;
            } else {
                throw new Exception('解密失败');
            }
        }
        return $result;
    }

    public function encrypt($data_str) {
        $max_len = 245;
        $result = '';
        for ($i = 0; $i < strlen($data_str); $i += $max_len) {
            $crypted = '';
            $r = openssl_public_encrypt(substr($data_str, $i, $max_len), $crypted, $this->kz_loan_public_key);
            if ($r) {
                $result .= $crypted;
            } else {
                throw new Exception('加密失败');
            }
        }
        return base64_encode($result);
    }

}
