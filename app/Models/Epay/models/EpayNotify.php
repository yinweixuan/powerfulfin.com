<?php


class EpayNotify
{
    /**
     * 支付成功
     */
    const PAY_STATUS_S = 3;
    /**
     * 支付失败
     */
    const PAY_STATUS_F = 4;

    const YII_LOG_NAME = 'pay_notify.op';

    /**
     * 签证签名
     * @param $data
     * @param bool $notify
     * @return bool
     * @throws KZException
     */
    public static function checkSign($data, $notify = false)
    {
        $signMsg = $data['signMsg'];
        if (!$signMsg) {
            throw new KZException("缺少签名");
        }
        if ($notify) {
            $attrs = array('merchantNo', 'orderNo', 'orderAmount', 'orderTime', 'payStatus', 'orderpayNo', 'payAmount', 'orderPaytime');
        } else {
            $attrs = array('merchantNo', 'payType', 'orderNo', 'payOrderNo', 'payStatus', 'orderTime', 'orderAmount', 'bankCode', 'orderPayTime');
        }
        $sign = ROPPay::calcSign($data, $attrs, true);
        Yii::log(__CLASS__ . "::" . __FUNCTION__ . "::" . __LINE__ . "::PayNotify sign: \n" . $sign, CLogger::LEVEL_INFO, self::YII_LOG_NAME);
        if ($sign == $signMsg) {
            Yii::log(__CLASS__ . "::" . __FUNCTION__ . "::" . __LINE__ . "::PayNotify sign: 签名验证成功", self::YII_LOG_NAME);
            return $data;
        } else {
            Yii::log(__CLASS__ . "::" . __FUNCTION__ . "::" . __LINE__ . "::PayNotify sign: 签名验证失败", CLogger::LEVEL_INFO, self::YII_LOG_NAME);
            throw new KZException("签名验证失败");
        }
    }

    /**
     * 支付成功
     * @param $data
     * @return bool
     */
    public static function paySuccess($data)
    {
        Yii::log(__CLASS__ . "::" . __FUNCTION__ . "::" . __LINE__ . "::ERROR PayNotify: \n" . var_export($data, true), CLogger::LEVEL_INFO, 'pay_notify.op');
        try {
            $order = ARPayOrder::getByOrderRS($data['orderNo']);

            if (empty($order)) {
                return false;
            }
            // 如果订单状态已经改成已支付 则直接返回 true
            if ($order['status'] == ARPayOrder::STATUS_PAY_SUCCESS) {
                return true;
            }
            $total_fee = $data['payAmount'];
            $fieldData = array(
                'order_time' => date('Y-m-d H:i:s', strtotime($data['orderTime'])),
                'pay_time' => date('Y-m-d H:i:s', strtotime($data['orderPayTime'])),
                'pay_type' => $data['payType'],
                'bank_code' => (isset($data['bankCode']) && !empty($data['bankCode'])) ? $data['bankCode'] : 0,
                'status' => ARPayOrder::STATUS_PAY_SUCCESS,
            );
            ARPayOrder::_update($order['id'], $fieldData);
            if ($order['source'] == ARPayOrder::SOURCE_FINANCE) {
                try {
                    if (strpos($order['relation'], '|') !== false) {
                        list($lid, $repay_button, $bill_id, $orderID) = explode('|', $order['relation']);
                        if ($bill_id != 0) {
                            ARPayLoanBill::updateData($bill_id, array('status' => ARPayLoanBill::STATUS_REPAYING));
                        } else {
                            if ($repay_button == 1) {
                                $bill = ARPayLoanBill::getLoanBillByLidAndBillDate($lid, BULoan::calcLastRepayDate());
                                $bills[] = $bill;
                            } else if ($repay_button == 2) {
                                $bills = ARPayLoanBill::getLoanBillByLid($lid, ARPayLoanBill::STATUS_NO_REPAY);
                            } else if ($repay_button == 3) {
                                $bills = ARPayLoanBill::getLoanBillByLid($lid, ARPayLoanBill::STATUS_OVERDUE);
                            } else {
                                $bills = array();
                            }

                            foreach ($bills as $bill) {
                                ARPayLoanBill::updateData($bill['id'], array('status' => ARPayLoanBill::STATUS_REPAYING));
                            }
                        }
                        LoanRepayQueue::sendApprepayment($lid, $bill_id, $order['id']);
                    } else if (is_numeric($order['relation'])) {
                        $loan = LoanData::getLoanAndStudentById($order['relation']);
                        if (empty($loan)) {
                            Yii::log(__CLASS__ . "::" . __FUNCTION__ . "::" . __LINE__ . "money self pay.\ndata:" . $order['relation'] . "\nmsg: no loan", CLogger::LEVEL_ERROR, 'pay_notify.op');
                        } else {
                            if ($loan['money_self_pay'] == $total_fee) {
                                ARLoan::_update($loan['id'], array('status' => LOAN_46_PAY_SUCCESS, 'money_self_pay_id' => $order['id']));
                                ARLoanLog::insertLog($loan, LOAN_46_PAY_SUCCESS, '充值成功');
                            }
                        }
                    }
                } catch (Exception $e) {
                    Yii::log(__CLASS__ . "::" . __FUNCTION__ . "::" . __LINE__ . "::send student info error.\ndata:" . $order['relation'] . "\nmsg:" . $e->getMessage(), CLogger::LEVEL_ERROR, 'pay_notify.op');
                }
            }

            if (in_array($order['source'], array(ARPayOrder::SOURCE_ZHAOSHENG, ARPayOrder::SOURCE_SAAS)) && $order['order_type'] == ARPayOrder::ORDER_TYPE_PAY) {
                EpayQueue::sendPayForTransfer($order);
            }
            return true;
        } catch (Exception $e) {
            Yii::log(__CLASS__ . "::" . __FUNCTION__ . "::" . __LINE__ . "::ERROR PayNotify: \n" . $e->getMessage(), CLogger::LEVEL_ERROR, 'pay_notify.op');
        }
    }

    /**
     * 支付失败
     * @param $data
     * @return bool
     */
    public static function payFailed($data)
    {
        try {
            $order = ARPayOrder::getByOrderRS($data['orderNo']);

            if (empty($order)) {
                Yii::log(__CLASS__ . "::" . __FUNCTION__ . "::" . __LINE__ . "::ERROR PayNotify: no order", CLogger::LEVEL_ERROR, 'pay_notify.op');
                return false;
            } else {
                Yii::log(__CLASS__ . "::" . __FUNCTION__ . "::" . __LINE__ . "::ERROR PayNotify: \n" . var_export($order, true), CLogger::LEVEL_INFO, 'pay_notify.op');
            }
            // 如果订单状态已经改成已支付 则直接返回 true
            if ($order['status'] == ARPayOrder::STATUS_PAY_SUCCESS || $order['status'] = ARPayOrder::STATUS_PAY_FAILED) {
                return true;
            }
            $fieldData = array(
                'status' => ARPayOrder::STATUS_PAY_FAILED,
            );
            ARPayOrder::_update($order['id'], $fieldData);
        } catch (Exception $e) {
            Yii::log(__CLASS__ . "::" . __FUNCTION__ . "::" . __LINE__ . "::ERROR PayNotify: \n" . $e->getMessage(), CLogger::LEVEL_ERROR, 'pay_notify.op');
        }
    }

    /**
     * 添加通知记录
     * @param $data
     */
    public static function addNotify($data)
    {
        try {
            $orderNotify = array(
                'merchant_no' => $data['merchantNo'],
                'order_no' => $data['orderNo'],
                'pay_order_no' => $data['payOrderNo'],
                'order_time' => date('Y-m-d H:i:s', strtotime($data['orderTime'])),
                'order_pay_time' => date('Y-m-d H:i:s', strtotime($data['orderPayTime'])),
                'order_amount' => $data['orderAmount'],
                'pay_amount' => $data['payAmount'],
                'pay_status' => $data['payStatus'],
                'pay_type' => $data['payType'],
                'bank_code' => $data['bankCode'],
                'pay_msg' => $data['payMsg'],
                'expand' => $data['expand'],
                'expand2' => $data['expand2'],
                'version' => $data['version'],
                'sign_type' => $data['signType'],
                'sign_msg' => $data['signMsg'],
                'creat_time' => date('Y-m-d H:i:s'),
            );
            ARPayOrderNotify::add($orderNotify);
            Yii::log(__CLASS__ . "::" . __FUNCTION__ . "::" . __LINE__ . "::PayNotify: create success \n" . json_encode($orderNotify), CLogger::LEVEL_INFO, 'pay_notify.op');
        } catch (Exception $exception) {
            Yii::log(__CLASS__ . "::" . __FUNCTION__ . "::" . __LINE__ . "::ERROR PayNotify: \n" . $exception->getMessage(), CLogger::LEVEL_ERROR, 'pay_notify.op');
        }
    }
}
