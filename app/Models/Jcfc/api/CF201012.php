<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/13
 * Time: 下午6:24
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * 还款试算
 * 渠道机构调用此接口，进行还款试算。这里不支持NF还款类型
 * Class CF201012
 */
class CF201012
{
    const SETTLE_IN_ADVANCE = "FS";
    const PART_OF_THE_REPAYMENT = "ER";
    const DUE_REPAYMENT = "NM";
    const ADVANCE_PAYMENT = "NF";

    public static function getParams($params = array())
    {
        if (empty($params['applseq'])) {
            throw new PFException("缺失晋商流水号");
        }

        $data = array(
//            'chlresource' => JcfcInit::getChlresource(),
            'applseq' => $params['applseq'],
            'loanno' => $params['loan_order'],   //v2.1删除
            'contno' => $params['contract_order'],   //v2.1删除
            'dnamt' => $params['amount'],
            'paymentmode' => $params['paymentmode'],
            'actvpayind' => $params['actvpayind'],
        );
        return $data;
    }
}
