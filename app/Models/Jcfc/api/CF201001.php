<?php

namespace App\Models\Jcfc\api;
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/11
 * Time: 上午10:14
 */

/**
 * 流水号生成
 * 由渠道端发起，获取单笔进件的申请流水号, 返回生成的申请流水号。
 * Class CF201001
 */
class CF201001
{
    /**
     * 重组参数，根据接口文档，该接口无参数
     * 返回结果
     * 'ec' => string '0' (length=1)
     * 'em' => string '交易成功' (length=12)
     * 'hostReturnCode' => string '0' (length=1)
     * 'hostErrorMessage' => string '交易成功' (length=12)
     * 'applseq' => string '4008103' (length=7) 很重要
     * 'sequenceNo' => string '459289' (length=6)
     * 'windowId' => string 'null' (length=4)
     * @return array
     */
    public static function getParams()
    {
        return array(
//            'chlresource' => JcfcInit::getChlresource(),
            'applseq' => '0000',
        );
    }
}
