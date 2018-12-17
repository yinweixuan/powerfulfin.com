<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/5/24
 * Time: 下午3:27
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * 逾期还款计划查询
 * Class CF201035
 */
class CF201035
{
    /**
     * ALL:查询完整还款计划
     * OD_ALL:当前日期前所有还款计划
     * OD_ONLY: 当前日期前仅有欠款的还款计划
     * NOT_SETL:所有未结清的还款计划，包括逾期和未结清的
     */

    const QUERY_TYPE_ALL = 'ALL';
    const QUERY_TYPE_OD_ALL = 'OD_ALL';
    const QUERY_TYPE_OD_ONLY = 'OD_ONLY';
    const QUERY_TYPE_SELF = 'NOT_SELF';

    /**
     * @param array $params
     * @return array
     * @throws PFException
     */
    public static function getParams($params = array())
    {
        if (empty($params['applseq'])) {
            throw new PFException("缺失晋商流水号");
        }

        if (empty($params['loan_order'])) {
            throw new PFException("缺失晋商借据号");
        }
        return array(
            'applseq' => $params['applseq'],
            'loanno' => $params['loan_order'],
            'enqtyp' => $params['query_type'],
        );
    }
}
