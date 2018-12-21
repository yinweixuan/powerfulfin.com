<?php

class KZPayRouter extends App\Models\Epay\Epay {

    public static $channel_baofoo = 'baofoo';
    public static $channel_yeepay = 'yeepay';
    public static $busi_type_withhold = 'withhold';
    public static $busi_type_app_recharge = 'app_recharge';
    public static $busi_type_query = 'query';
    public static $busi_type_query_bind = 'query_bind';
    public static $busi_type_ap_prebind = 'prebind';
    public static $busi_type_ap_bind = 'bind';
    public static $busi_type_ap_pay = 'pay';
    public static $busi_type_verify = 'verify';

    public static function getFormedData($params) {
        $data = array();
        //通用参数
        if (!isset($params['api'])) {
            throw new Exception('缺少参数：api');
        }
        if (!isset($params['env'])) {
            throw new Exception('缺少参数：env');
        } elseif ($params['env'] != self::ENV_DEV && $params['env'] != self::ENV_ONLINE) {
            throw new Exception('参数错误：env');
        }
        $data['api'] = $params['api'];
        $data['config']['env'] = $params['env'];
        $data['config']['userid'] = $params['uid'];
        $data['config']['busi_id'] = $params['busi_id'];
        $data['config']['busi_type'] = $params['api'];
        //其他参数
        if ($data['api'] == self::$busi_type_withhold) {
            //代扣
            $data = self::handleWithhold($params, $data);
        } elseif ($data['api'] == self::$busi_type_app_recharge) {
            //app充值
            $data = self::handleAppRecharge($params, $data);
        } elseif ($data['api'] == self::$busi_type_query) {
            //统一查询（代扣结果查询，app充值结果查询）
            $data = self::handleQuery($params, $data);
        } elseif ($data['api'] == self::$busi_type_ap_prebind) {
            //协议支付：预绑卡
            $data = self::handlePrebind($params, $data);
        } elseif ($data['api'] == self::$busi_type_ap_bind) {
            //协议支付：确认绑卡
            $data = self::handleBind($params, $data);
        } elseif ($data['api'] == self::$busi_type_query_bind) {
            //协议支付：绑卡查询
            $data = self::handleQueryBind($params, $data);
        } elseif ($data['api'] == self::$busi_type_ap_pay) {
            //协议支付：直接支付
            $data = self::handleAPPay($params, $data);
        } elseif ($data['api'] == self::$busi_type_verify) {
            //四要素验证
            $data = self::handleVerify($params, $data);
        } else {
            throw new Exception('参数错误：api');
        }
        if (!$data['class'] || !$data['func']) {
            throw new Exception('请检查提交数据');
        }
        return $data;
    }

    /**
     * 检查参数
     */
    public static function processParams($params, $required_fields = array()) {
        foreach ($required_fields as $key) {
            if (!isset($params[$key])) {
                throw new Exception('缺少参数：' . $key);
            } elseif (!$params[$key]) {
                throw new Exception('参数错误：' . $key);
            }
        }
        return $params;
    }

    /**
     * 处理统一查询数据
     */
    public static function handleQuery($params, $data) {
        $data['params'] = self::processParams($params, array('order_id'));
        $order = ARPayZhifuOrder::getByOrderid($params['order_id']);
        if (!$order) {
            throw new Exception('查询无此订单');
        } else {
            if ($order['status'] != self::STATUS_DOING) {
                $data['class'] = 'KZPayCommon';
                $data['func'] = 'getOrderStatus';
            } elseif ($order['busi_type'] == self::$busi_type_withhold || $order['busi_type'] == 'repay_bat') {
                if ($order['channel'] == self::$channel_baofoo) {
                    $data['class'] = 'BaofooPay';
                    $data['func'] = 'withholdResult';
                    $data['params']['origTradeDate'] = $order['bill_time'];
                } elseif ($order['channel'] == self::$channel_yeepay) {
                    $data['class'] = 'YeePay';
                    $data['func'] = 'queryFirstPay';
                }
            } elseif ($order['busi_type'] == self::$busi_type_app_recharge) {
                if ($order['channel'] == self::$channel_baofoo) {
                    $data['class'] = 'BaofooApp';
                    $data['func'] = 'queryNew';
                }
            } elseif ($order['busi_type'] == self::$busi_type_ap_pay) {
                if ($order['channel'] == self::$channel_baofoo) {
                    $data['class'] = 'BaofooPayAP';
                    $data['func'] = 'queryPay';
                    $data['params']['bill_time'] = date('Y-m-d H:i:s', strtotime($order['bill_time']));
                }
            }
        }
        return $data;
    }

    /**
     * 处理代扣数据
     */
    public static function handleWithhold($params, $data) {
        $fields = array(
            'money_fen', 'idcard_number', 'idcard_name',
            'bank_code', 'bank_account', 'phone', 'uid'
        );
        $data['params'] = self::processParams($params, $fields);
        $data['scene'] = self::SCENE_WITHHOLD;
        if ($params['channel'] == self::$channel_baofoo) {
            $data['class'] = 'BaofooPay';
            $data['func'] = 'withhold';
            $data['config']['channel'] = self::$channel_baofoo;
        } elseif ($params['channel'] == self::$channel_yeepay) {
            $data['class'] = 'YeePay';
            $data['func'] = 'firstPay';
            $data['config']['channel'] = self::$channel_yeepay;
        } else {
            $data['class'] = 'BaofooPay';
            $data['func'] = 'withhold';
            $data['config']['channel'] = self::$channel_baofoo;
        }
        return $data;
    }

    /**
     * 处理app充值第一步数据
     */
    public static function handleAppRecharge($params, $data) {
        $fields = array(
            'money_fen', 'idcard_number', 'idcard_name',
            'bank_code', 'bank_account', 'phone', 'uid'
        );
        $data['params'] = self::processParams($params, $fields);
        $data['scene'] = self::SCENE_RECHARGE;
        $data['class'] = 'BaofooApp';
        $data['func'] = 'rechargeNew';
        $data['config']['channel'] = self::$channel_baofoo;
        return $data;
    }

    /**
     * 处理协议支付：预绑卡
     */
    public static function handlePrebind($params, $data) {
        $fields = array(
            'uid', 'bank_account', 'idcard_name',
            'idcard_number', 'phone'
        );
        $data['params'] = self::processParams($params, $fields);
        $data['scene'] = self::SCENE_PAY;
        $data['class'] = 'BaofooPayAP';
        $data['func'] = 'preBind';
        $data['config']['channel'] = self::$channel_baofoo;
        return $data;
    }

    /**
     * 处理协议支付：绑卡
     */
    public static function handleBind($params, $data) {
        $fields = array(
            'unique_code', 'sms_code'
        );
        $data['params'] = self::processParams($params, $fields);
        $data['scene'] = self::SCENE_PAY;
        $data['class'] = 'BaofooPayAP';
        $data['func'] = 'bind';
        $data['config']['channel'] = self::$channel_baofoo;
        return $data;
    }

    /**
     * 处理协议支付：直接支付
     */
    public static function handleAPPay($params, $data) {
        $fields = array(
            'uid', 'protocol_no', 'money_fen', 'order_id'
        );
        $data['params'] = self::processParams($params, $fields);
        $data['scene'] = self::SCENE_PAY;
        $data['class'] = 'BaofooPayAP';
        $data['func'] = 'pay';
        $data['config']['channel'] = self::$channel_baofoo;
        return $data;
    }

    /**
     * 处理绑卡查询
     */
    public static function handleQueryBind($params, $data) {
        $fields = array(
            'uid'
        );
        $data['params'] = self::processParams($params, $fields);
        $data['scene'] = self::SCENE_PAY;
        $data['class'] = 'BaofooPayAP';
        $data['func'] = 'queryBind';
        $data['config']['channel'] = self::$channel_baofoo;
        return $data;
    }

    /**
     * 处理四要素验证
     */
    public static function handleVerify($params, $data) {
        $fields = array(
            'bank_account', 'idcard_name', 'phone'
        );
        $data['params'] = self::processParams($params, $fields);
        $data['scene'] = self::SCENE_VERIFY;
        $data['class'] = 'Xinyan';
        $data['func'] = 'cardAuth';
        $data['config']['channel'] = self::$channel_baofoo;
        return $data;
    }

}
