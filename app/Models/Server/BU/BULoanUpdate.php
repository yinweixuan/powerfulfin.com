<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/24
 * Time: 3:33 PM
 */

namespace App\Models\Server\BU;


use App\Components\PFException;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFLoanLog;

class BULoanUpdate
{
    /**
     * 创建分期订单
     * @param array $info
     * @return array|bool
     * @throws PFException
     */
    public static function createLoan(array $info)
    {
        try {
            $loan = ARPFLoan::addLoan($info);
            ARPFLoanLog::insertLog($loan, LOAN_1000_CREATE, '创建分期信息');
            if ($info['resource'] == RESOURCE_JCFC) {
                ARPFLoan::_update($loan['id'], array('status' => LOAN_1200_SURE_FILE));
                ARPFLoanLog::insertLog($loan, LOAN_1200_SURE_FILE, '创建分期信息Success,并需要确认相关协议');
            } else {
                ARPFLoan::_update($loan['id'], array('status' => LOAN_1100_CREATE_ACCOUNT));
                ARPFLoanLog::insertLog($loan, LOAN_1100_CREATE_ACCOUNT, '创建分期信息Success');
            }
        } catch (PFException $exception) {
            throw new PFException($exception->getMessage(), $exception->getCode());
        }
        return $loan;
    }

    /**
     * 处理订单状态的变更,并记录日志
     * @param $lid      订单号
     * @param $new     修改后属性
     * @param string $remark 备注
     * @return
     * @throws PFException
     */
    public static function changeStatus($lid, $new, $remark = '')
    {
        //检查属性中是否有status,如果status不变,也可以变更.
        if (!array_key_exists('status', $new)
            || BULoanStatus::getStatusDescriptionForAdmin($new['status']) == BULoanStatus::NOT_FOUND
        ) {
            throw new PFException("订单状态修改参数错误", ERR_SYS_PARAM);
        }
        $loan = ARPFLoan::getLoanById($lid);
        if (empty($loan)) {
            throw new PFException("订单号不存在", ERR_SYS_PARAM);
        }
        ARPFLoan::_update($lid, $new);
        ARPFLoanLog::insertLog($loan, $new['status'], $remark);
        return $new['status'];
    }
}
