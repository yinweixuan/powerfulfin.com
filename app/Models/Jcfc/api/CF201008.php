<?php


/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2017/7/13
 * Time: 下午6:18
 */

namespace App\Models\Jcfc\api;

use App\Components\PFException;

/**
 * 审批状态查询
 * 渠道机构发起审批结果查询的请求。包含申请结果查询、放款结果查询。
 * Class CF201008
 */
class CF201008
{
    // 接口文档v1.0常量
    const RESULT_FLAG_APPLY_SUCCESS = '1001';   //申请审批成功
    const RESULT_FLAG_APPLY_ING = '1002';   //申请审批中
    const RESULT_FLAG_APPLY_FAIL = '1003';  //申请审批失败
    const RESULT_FLAG_APPLY_TURN = '1006';  //申请审批退回 (影像资料补件)
    const RESULT_FLAG_REVIEW_SUCCESS = '2001';  //同意放款
    const RESULT_FLAG_REVIEW_ING = '2002';  //放款审批中
    const RESULT_FLAG_REVIEW_TURN = '2003'; //放款审批打回(合同影像资料补件)
    const RESULT_FLAG_LOAN_SUCCESS = '3001';    //放款成功
    const RESULT_FLAG_LOAN_ING = '3002';    //放款处理中
    const RESULT_FLAG_LOAN_FAIL = '3003';   //放款失败
    const RESULT_FLAG_LOAN_REFUSE = '3004'; //拒绝放款
    const RESULT_FLAG_LOAN_REVIEW = '3005'; //放款审查中
    const RESULT_FLAG_LOAN_TURN = '3006';   //放款退回
    const RESULT_FLAG_PAY_TURN = '4001';    //放款撤销

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
