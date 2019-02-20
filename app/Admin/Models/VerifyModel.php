<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/18
 * Time: 3:25 PM
 */

namespace App\Admin\Models;


use App\Components\ArrayUtil;
use App\Components\PFException;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFLoanLog;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Facades\DB;

class VerifyModel
{
    /**
     * 抢单
     * @param type $ids
     * @return
     * @throws PFException
     */
    public static function collect($ids)
    {
        //检查id是否都为整数
        foreach ($ids as $k => $v) {
            if (!is_numeric($v)) {
                unset($ids[$k]);
            }
        }
        if (empty($ids)) {
            throw new PFException('选择的id不能为空', ERR_SYS_PARAM);
        }
        $uid = Admin::user()->id;
        $sql = "UPDATE pf_loan SET `auditer` = {$uid} WHERE id IN (" . implode(',', $ids) . ") AND auditer = 0;";
        DB::update($sql);
        //查询这批单子中哪些被当前管理员选中
        $sql = "SELECT id FROM " . ARPFLoan::TABLE_NAME . " WHERE id IN (" . implode(',', $ids) . ") AND auditer = {$uid}";
        $res = DB::select($sql);
        $ret = ArrayUtil::getSomeKey($res, 'id');
        return $ret;
    }

    /**
     * 审核操作
     * @param array $loan
     * @param $result
     * @param $remarks
     * @return bool
     * @throws PFException
     */
    public static function checkLoan(array $loan, $result, $remarks)
    {
        switch ($result) {
            case 1:
                $update = ['status' => LOAN_3000_PF_CONFIRM];
                break;
            case 2:
                $update = ['status' => LOAN_3100_PF_REFUSE];
                break;
            case 3:
                $update = ['status' => LOAN_14000_FOREVER_REFUSE];
                break;
            case 4:
            case 5:
                $update = [];
                break;
            default:
                throw new PFException('审核结果异常', ERR_SYS_PARAM);
                break;
        }

        $update['audit_opinion'] = $remarks;

        if ($result == 4) {
            ARPFLoan::_update($loan['base']['id'], $update);
        } else if ($result == 5) {
            ARPFLoan::_update($loan['base']['id'], ['audit_opinion' => '', 'auditer' => 0]);
        } else {
            ARPFLoan::_update($loan['base']['id'], $update);
            ARPFLoanLog::insertLogForAdmin($loan['base'], $update['status'], '审核结果', $update);
        }
        return true;
    }

}
