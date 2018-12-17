<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/11
 * Time: 上午11:06
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * 支持银行卡查询
 * 渠道机构发起银行列表查询，查询晋商消费公司支持的银行卡信息。如：晋商银行、中国银行、工商银行等。
 * Class CF201002
 */
class CF201002
{
    /**
     * 重组参数,根据接口文档，请求参数为空
     */
    public static function getParams($params = array())
    {
        if (empty($params['applseq'])) {
            throw new PFException("缺失晋商流水号");
        }

        return array(
//            'chlresource' => JcfcInit::getChlresource(),
            'applseq' => $params['applseq'],
        );
    }

    /**
     * 返回结果
     * array(7) {
     * 'ec' =>
     * string(1) "0"
     * 'em' =>
     * string(12) "交易成功"
     * 'approvearray008' =>
     * array(14) {
     * [0] =>
     * array(5) {
     * 'acc_bank_cde' =>
     * string(12) "313161000017"
     * 'acc_bank_name' =>
     * string(12) "晋商银行"
     * 'acc_bank_cde_tl' =>
     * string(4) "0449"
     * 'max_amt' =>
     * string(0) ""
     * 'temp1' =>
     * NULL
     * }
     * [1] =>
     * array(5) {
     * 'acc_bank_cde' =>
     * string(12) "105100000017"
     * 'acc_bank_name' =>
     * string(18) "中国建设银行"
     * 'acc_bank_cde_tl' =>
     * string(4) "0105"
     * 'max_amt' =>
     * string(0) ""
     * 'temp1' =>
     * NULL
     * }
     * [2] =>
     * array(5) {
     * 'acc_bank_cde' =>
     * string(12) "102100099996"
     * 'acc_bank_name' =>
     * string(18) "中国工商银行"
     * 'acc_bank_cde_tl' =>
     * string(4) "0102"
     * 'max_amt' =>
     * string(0) ""
     * 'temp1' =>
     * NULL
     * }
     * [3] =>
     * array(5) {
     * 'acc_bank_cde' =>
     * string(12) "103100000026"
     * 'acc_bank_name' =>
     * string(18) "中国农业银行"
     * 'acc_bank_cde_tl' =>
     * string(4) "0103"
     * 'max_amt' =>
     * string(0) ""
     * 'temp1' =>
     * NULL
     * }
     * [4] =>
     * array(5) {
     * 'acc_bank_cde' =>
     * string(12) "104100000004"
     * 'acc_bank_name' =>
     * string(12) "中国银行"
     * 'acc_bank_cde_tl' =>
     * string(4) "0104"
     * 'max_amt' =>
     * string(0) ""
     * 'temp1' =>
     * NULL
     * }
     * [5] =>
     * array(5) {
     * 'acc_bank_cde' =>
     * string(12) "302100011000"
     * 'acc_bank_name' =>
     * string(12) "中信银行"
     * 'acc_bank_cde_tl' =>
     * string(4) "0302"
     * 'max_amt' =>
     * string(0) ""
     * 'temp1' =>
     * NULL
     * }
     * [6] =>
     * array(5) {
     * 'acc_bank_cde' =>
     * string(12) "305100000013"
     * 'acc_bank_name' =>
     * string(18) "中国民生银行"
     * 'acc_bank_cde_tl' =>
     * string(4) "0305"
     * 'max_amt' =>
     * string(0) ""
     * 'temp1' =>
     * NULL
     * }
     * [7] =>
     * array(5) {
     * 'acc_bank_cde' =>
     * string(12) "303100000006"
     * 'acc_bank_name' =>
     * string(12) "光大银行"
     * 'acc_bank_cde_tl' =>
     * string(4) "0303"
     * 'max_amt' =>
     * string(0) ""
     * 'temp1' =>
     * NULL
     * }
     * [8] =>
     * array(5) {
     * 'acc_bank_cde' =>
     * string(12) "309391000011"
     * 'acc_bank_name' =>
     * string(12) "兴业银行"
     * 'acc_bank_cde_tl' =>
     * string(4) "0309"
     * 'max_amt' =>
     * string(0) ""
     * 'temp1' =>
     * NULL
     * }
     * [9] =>
     * array(5) {
     * 'acc_bank_cde' =>
     * string(12) "308584000013"
     * 'acc_bank_name' =>
     * string(12) "招商银行"
     * 'acc_bank_cde_tl' =>
     * string(4) "0308"
     * 'max_amt' =>
     * string(0) ""
     * 'temp1' =>
     * NULL
     * }
     * [10] =>
     * array(5) {
     * 'acc_bank_cde' =>
     * string(12) "403100000004"
     * 'acc_bank_name' =>
     * string(24) "中国邮政储蓄银行"
     * 'acc_bank_cde_tl' =>
     * string(4) "0403"
     * 'max_amt' =>
     * string(0) ""
     * 'temp1' =>
     * NULL
     * }
     * [11] =>
     * array(5) {
     * 'acc_bank_cde' =>
     * string(12) "307584007998"
     * 'acc_bank_name' =>
     * string(30) "平安银行股份有限公司"
     * 'acc_bank_cde_tl' =>
     * string(4) "0307"
     * 'max_amt' =>
     * string(0) ""
     * 'temp1' =>
     * NULL
     * }
     * [12] =>
     * array(5) {
     * 'acc_bank_cde' =>
     * string(12) "306581000003"
     * 'acc_bank_name' =>
     * string(30) "广发银行股份有限公司"
     * 'acc_bank_cde_tl' =>
     * string(4) "0306"
     * 'max_amt' =>
     * string(0) ""
     * 'temp1' =>
     * NULL
     * }
     * [13] =>
     * array(5) {
     * 'acc_bank_cde' =>
     * string(12) "307584007999"
     * 'acc_bank_name' =>
     * string(32) "平安银行(深圳发展银行)"
     * 'acc_bank_cde_tl' =>
     * string(4) "0307"
     * 'max_amt' =>
     * string(0) ""
     * 'temp1' =>
     * NULL
     * }
     * }
     */

}
