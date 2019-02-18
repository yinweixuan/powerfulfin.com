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

}
