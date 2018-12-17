<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/11
 * Time: 上午11:16
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * 表单资料提交
 * 贷款申请提交和作废交易。应用场景：录入完所有贷款要素后点击提交按钮，传到消费信贷系统，发起提交流程。
 * Class CF201004
 */
class CF201004
{
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
}
