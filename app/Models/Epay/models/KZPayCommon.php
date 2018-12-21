<?php

namespace App\Models\Epay\models;

use \App\Models\ActiveRecord\ARPFZhifuOrder;

class KZPayCommon extends \App\Models\Epay\Epay {

    public function getOrderStatus($params) {
        return self::sgetOrderStatus($params['order_id']);
    }

    public static function sgetOrderStatus($order_id) {
        $fields = ['channel','order_id','status','remark','money_fen','bill_time','busi_type'];
        $order = ARPFZhifuOrder::getByOrderid($order_id, $fields);
        return $order;
    }

}
