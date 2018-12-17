<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/13
 * Time: 下午6:29
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

class CF201016
{
    public static function getParams($params = array())
    {
        if (empty($params['applseq'])) {
            throw new PFException("缺失晋商流水号");
        }
        return array(
//            'chlresource' => JcfcInit::getChlresource(),
            'applseq' => $params['applseq'],
            'applacnams22' => $params['applacnams22'],
            'applacnamr2' => $params['applacnamr2'],
            'tel2' => $params['tel2'],
        );
    }
}
