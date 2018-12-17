<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/13
 * Time: 下午6:25
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * 主动还款
 * 渠道方的用户在渠道机构端发起主动还款。
 * Class CF201013
 */
class CF201013
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
            'loanno' => $params['loan_order'],  //借据号
            'contno' => $params['contract_order'],  //合同号
            'applseq' => $params['applseq'],
            'chlfkseq' => $params['open_id'],
            'dnamt' => $params['amount'],
            'paymentmode' => $params['paymentmode'],
            'perdno' => $params['perdno'],
            'flag' => $params['flag'],
        );
        return $data;
    }
}
