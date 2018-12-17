<?php

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * 扣款结果查询 CF201014
 * User: wangyi
 * Date: 2017/7/13
 * Time: 下午6:28
 */
class CF201014
{
    public static function getParams($params = array())
    {
        if (empty($params['applseq'])) {
            throw new PFException("缺失晋商流水号");
        }
        $data = array(
            'applseq' => $params['applseq'],
            'loanno' => $params['loan_order'],
            'lmseq' => empty($params['lmseq']) || $params['lmseq'] == 'null' ? '' : $params['lmseq'],
            'starttime' => $params['start_time'],
            'endtime' => !empty($params['end_time']) ? $params['end_time'] : date('Y-m-d H:i:s'),
        );

        if ($data['lmseq'] == 'null' || empty($data['lmseq'])) {
            unset($data['lmseq']);
        }
        return $data;
    }
}
