<?php

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/13
 * Time: 下午6:28
 */
class CF201015
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
