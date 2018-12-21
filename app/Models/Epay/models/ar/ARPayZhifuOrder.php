<?php

/**
 * 支付订单信息
 *
 */
class ARPayZhifuOrder extends KZActiveRecord {

    const TABLE_NAME = 'pay_zhifu_order';

    /**
     * returns the static model of the specified AR class
     * @param    $strClassName
     *     [string]: active record class name
     * @return
     *     [CActiveRecord]: active record model instance
     */
    public static function model($strClassName = __CLASS__) {
        return parent::model($strClassName);
    }

    /**
     * get the associated table name
     * @return
     *     [string]: associated database table name
     */
    public function tableName() {
        return self::TABLE_NAME;
    }

    /**
     * rules
     * @return
     *     [array]: validation rules for model attributes
     */
    public function rules() {
        return array(
        );
    }

    public static function _add($data) {
        $data['ctime'] = date('Y-m-d H:i:s');
        $data['utime'] = $data['ctime'];
        $conn = Yii::app()->db;
        $r = $conn->createCommand()->insert(self::TABLE_NAME, $data);
        if ($r) {
            return $conn->getLastInsertID();
        } else {
            return false;
        }
    }

    public static function _update($id, $data) {
        $data['utime'] = date('Y-m-d H:i:s');
        Yii::app()->db->createCommand()
                ->update(self::TABLE_NAME, $data, 'id=:id', array(':id' => $id));
    }

    public static function _updateByFlowid($flowid, $data) {
        $data['utime'] = date('Y-m-d H:i:s');
        Yii::app()->db->createCommand()
                ->update(self::TABLE_NAME, $data, 'flow_id=:flowid', array(':flowid' => $flowid));
    }

    public static function _updateByOrderid($orderId, $data) {
        $data['utime'] = date('Y-m-d H:i:s');
        $n = Yii::app()->db->createCommand()
                ->update(self::TABLE_NAME, $data, 'order_id=:orderid', array(':orderid' => $orderId));
        return $n;
    }

    public static function getByOrderid($orderId, $field = '*') {
        $row = Yii::app()->db->createCommand()
                ->select($field)
                ->from(self::TABLE_NAME)
                ->where('order_id=:orderid', array(':orderid' => $orderId))
                ->queryRow();
        return $row;
    }

}

?>
