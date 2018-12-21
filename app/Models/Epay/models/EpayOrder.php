<?php


class EpayOrder
{
    const SOURCE_ZS = '409e56820c52033361264d0db023a126';
    const SOURCE_SAAS = '3d7e620e9057841d146d000ad3aed072';
    const source_finance = '384b52c2de81ec86000c59af8b4b462e';

    public static function getSourceMd5String($source = 0)
    {
        switch ($source) {
            case ARPayOrder::SOURCE_ZHAOSHENG:
                $md5String = self::SOURCE_ZS;
                break;
            case ARPayOrder::SOURCE_SAAS:
                $md5String = self::SOURCE_SAAS;
                break;
            case ARPayOrder::SOURCE_FINANCE:
                $md5String = self::source_finance;
                break;
            default:
                throw new Exception('Error Processing Request', 1);
                break;
        }
        return $md5String;
    }

    /**
     * 验证签名
     * @param null $sign
     * @param array $info
     * @return bool
     * @throws KZException
     */
    public static function verifyOrderInfo($sign = null, $info = array())
    {
        $is_numeric = array('utype', 'payType', 'productNum', 'orderAmount', 'source', '_t', 'phone');
        foreach ($is_numeric as $item) {
            if (!is_numeric($info[$item])) {
                throw new KZException('参数提交异常');
            }
        }

//        self::checkTimeOut($info['_t']);
        unset($info['sign']);

        $md5 = self::getSignOrderInfo($info);
        if ($sign == $md5) {
            return true;
        } else {
            throw new KZException('验签失败，请重试！');
        }
    }

    /**
     * 根据参数生成md5密文
     * @param array $info
     * @return string
     */
    public static function getSignOrderInfo($info = array())
    {
        ksort($info);
        $query = urldecode(http_build_query($info));
        $md5String = self::getSourceMd5String($info['source']);
        $string = $query . '&' . $md5String;
        $md5 = md5($string);

        return $md5;
    }

    /**
     * 检查时间是否过期
     * @param $timestamp
     * @return bool
     * @throws KZException
     */
    public static function checkTimeOut($timestamp)
    {
        $strlen = strlen($timestamp);
        switch ($strlen) {
            case 10:
                $time = $timestamp;
                break;
            case 13:
                $time = (int)substr($timestamp, 0, 10);
                break;
            default:
                $time = false;
                break;
        }
        if (!$time) {
            throw new KZException('时间格式错误，请使用10位或13位长度UNIX时间戳');
        }

        $checkTime = abs($time - $_SERVER['REQUEST_TIME']);
        if ($checkTime > 30 || $checkTime < 0) {
            KZOutput::err(ERR_SYS_UNKNOWN, '已过期，访问无效');
        }

        return true;
    }

    /**
     * 创建订单
     * @param array $info
     * @param array $user
     * @return array
     * @throws KZException
     */
    public static function createOrder($info = array(), $user = array())
    {
        if (empty($info) || empty($user)) {
            throw new KZException('创建支付订单失败，提交参数异常');
        }

        $payOrder = array(
            'relation' => $info['relation'],
            'uid' => $user['uid'],
            'utype' => $info['utype'],
            'order_type' => ARPayOrder::ORDER_TYPE_PAY,
            'status' => ARPayOrder::STATUS_CREATE,
            'open_id' => ROPPay::orderid(),
            'order_rs' => 'null',
            'source' => $info['source'],
            'plat' => $info['plat'],
            'amount' => $info['orderAmount'],
            'number' => $info['productNum'],
            'goods_name' => $info['productName'],
            'remarks' => $info['productDesc'],
            'order_time' => DataBus::get('ctime'),
            'create_time' => date('Y-m-d H:i:s'),
        );
        try {
            $data = ARPayOrder::add($payOrder);
            return $data;
        } catch (Exception $e) {
            throw new KZException('创建订单失败：' . $e->getMessage());
        }
    }

    public static function updateOrder($newOrder = array(), $oldOrder = array())
    {
        if (empty($newOrder) || empty($oldOrder)) {
            return false;
        }

        $diff = array();
        foreach ($newOrder as $key => $value) {
            if ($value != $oldOrder[$key]) {
                $diff[$key] = $value;
            }
        }
        $diff['status'] = ARPayOrder::STATUS_PAY_ING;
        try {
            ARPayOrder::_update($oldOrder['id'], $diff);
        } catch (Exception $e) {
            throw new KZException($e->getMessage);
        }
    }
}
