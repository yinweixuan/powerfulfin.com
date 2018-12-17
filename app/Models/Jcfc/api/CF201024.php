<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/9/11
 * Time: 下午4:12
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

class CF201024
{
    public static function getParams($params = array())
    {
        if (empty($params['applseq'])) {
            throw new PFException("缺失晋商流水号");
        }

        return array(
            'applseq' => $params['applseq'],
        );
    }
}
