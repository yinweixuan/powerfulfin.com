<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/13
 * Time: 下午6:31
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

class CF201020
{
    public static function getParams($params = array())
    {
        if (empty($params['applseq'])) {
            throw new PFException("缺失晋商流水号");
        }

        if (empty($params['loan_no'])) {
            throw new PFException("缺失晋商借据号");
        }

        return array(
//            'chlresource' => JcfcInit::getChlresource(),
            'applseq' => $params['applseq'],
            'loanno' => $params['loan_order']
        );
    }
}
