<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/11
 * Time: 上午11:18
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * 影像资料提交
 * 客户通过渠道上传影像至晋商的SFTP服务器，渠道通过调该接口通知晋消
 * Class CF201005
 */
class CF201005
{
    const FILE_UPLOAD = '01';   //上传影像
    const FILE_SUPPLEMENT = '03';      //补传

    public static function getParams($params = array())
    {
        if (!in_array($params['node'], [self::FILE_UPLOAD, self::FILE_SUPPLEMENT])) {
            throw new PFException("标识错误");
        }

        if (empty($params['applseq'])) {
            throw new PFException("缺失晋商流水号");
        }

        return array(
            'applseq' => $params['applseq'],
            'node' => $params['node']
        );
    }
}
