<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/13
 * Time: 下午6:21
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * 合同签订
 * 渠道机构上传完合同影像后通知晋商消费
 * Class CF201010
 */
class CF201010
{
    const FILE_UPLOAD = '02'; //上传合同
    const FILE_SUPPLEMENT = '03'; //补传

    public static function getParams($params = array())
    {
        if (!in_array($params['node'], [self::FILE_UPLOAD, self::FILE_SUPPLEMENT])) {
            throw new PFException("标识错误");
        }

        if (empty($params['applseq'])) {
            throw new PFException("缺失晋商流水号");
        }

        return array(
//            'chlresource' => JcfcInit::getChlresource(),
            'applseq' => $params['applseq'],
            'node' => $params['node'],
        );
    }
}
