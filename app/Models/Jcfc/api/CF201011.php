<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/13
 * Time: 下午6:22
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * 还款计划查询
 * 渠道方查询对应借据编号的还款计划明细
 * Class CF201011
 */
class CF201011
{
    public static function getParams($params = array())
    {
        if (empty($params['applseq'])) {
            throw new PFException("缺失晋商流水号");
        }

        // if (empty($params['loan_order'])) {
        //     throw new PFException("缺失晋商借据编号");
        // }

        return array(
//            'chlresource' => JcfcInit::getChlresource(),
            'applseq' => $params['applseq'],
            // 'loanno' => $params['loan_order'], //借据编号 --v2.1文档删除
        );
    }
}
