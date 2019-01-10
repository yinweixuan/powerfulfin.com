<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/1/10
 * Time: 11:02 AM
 */

namespace App\Models\Calc;


use App\Components\AliyunOSSUtil;
use App\Components\OutputUtil;
use App\Components\PFException;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\DataBus;

class CalcContract
{
    /**
     * 获取合同
     * @param int $lid 订单号
     * @param int $user_type 用户类型：1：学员；2：课栈后台
     * @param boolean $is_download 是否下载
     * @param string $contract_type 合同类型：loan：分期协议；all：全部
     * @return mixed 合同地址，单条string，多条array
     * @throws PFException
     */
    public static function getContract($lid, $user_type, $is_download, $contract_type = null)
    {
        //获取贷款单的信息
        $loan = ARPFLoan::getLoanById($lid);
        if (empty($loan)) {
            throw new PFException('分期信息不存在');
        }
        if ($user_type == 1) {
            //学员
            $uid = DataBus::get('uid');
            if (!$uid) {
                throw new PFException('请先登录');
            }
            if ($uid != $loan['uid']) {
                throw new PFException('无权查看该分期信息');
            }
            if (!$contract_type) {
                $contract_type = 'loan';
            }
        } elseif ($user_type == 2) {
            //课栈后台
            if (!$contract_type) {
                $contract_type = 'all';
            }
        } elseif ($user_type == 3) {
            // 机构管理员
        } else {
            throw new PFException('参数错误！');
        }
        $contract = self::getOssLoanContract($loan['resource'], $loan['hid'], $lid);

        try {
            OutputUtil::file(basename($contract), $contract, 'application/force-download');
        } catch (PFException $e) {
            \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:file download error:' . $e->getMessage(), CLogger::LEVEL_INFO, 'loan.op');
            throw new PFException("合同下载失败！");
        }
        return $contract;
    }

    public static function getOssLoanContract($resource, $hid, $lid)
    {
        $local_path = PATH_STORAGE . '/upload/student/contract/' . $resource . '/' . $hid . '/' . (floor($lid / 10000)) . '/' . $lid;
        if (!file_exists($local_path)) {
            mkdir($local_path, 0755, true);
        }
        $local_contract = $local_path . '/signed_loan_contract.pdf';
        $oss_contract = AliyunOSSUtil::getObjectPrefix($resource, $hid, $lid) . '/signed_loan_contract.pdf';
        AliyunOSSUtil::download(AliyunOSSUtil::BUCKET_LOAN, $oss_contract, $local_contract);
        return $local_contract;
    }
}
