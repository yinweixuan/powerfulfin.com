<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/13
 * Time: 下午6:16
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * 还款日变更
 * 合同签订之前，可以做还款日变更
 * Class CF201007
 */
class CF201007
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
