<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/11
 * Time: 上午11:22
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * 撤销申请
 * 客户在贷款申请提交之后，签署合同之前可以提交贷款申请作废的请求
 * Class CF201006
 */
class CF201006
{
    public static function getParams($params = array())
    {
        if (empty($params['remark'])) {
            throw new PFException("请填写作为原因");
        }

        if (empty($params['applseq'])) {
            throw new PFException("缺失晋商流水号");
        }

        return array(
//            'chlresource'  => JcfcInit::getChlresource(),
            'applseq' => $params['applseq'],
            'cancelreason' => $params['remark'],
        );
    }
}
