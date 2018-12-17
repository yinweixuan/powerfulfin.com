<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/13
 * Time: 下午6:19
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * 生成合同
 * 渠道方发起请求生成合同，当晋商消费业务人员将渠道机构的放款请求审批通过后，渠道机构可以通过SFTP下载合同
 * Class CF201009
 */
class CF201009
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
