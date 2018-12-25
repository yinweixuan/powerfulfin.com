<?php
/**
 * 根据需要拼装展示数据
 * User: haoxiang
 * Date: 2018/12/25
 * Time: 7:40 PM
 */

namespace App\Models\Org;

use App\Models\Server\BU\BULoanProduct;
use Illuminate\Support\Facades\DB;

class OrgDataHelper
{
    /**
     * 查询订单所需展示的列表
     * @param $ids
     */
    public static function getLoanByIds($ids)
    {
        $ret = [];
        if (empty($ids)) {
            return $ret;
        }
        $sql = "SELECT l.id id,l.id lid,ur.full_name full_name,ur.identity_number identity_number,oc.class_name class_name,oc.class_price class_price,l.borrow_money borrow_money,l.create_time create_time,l.resource resource, l.status status,l.loan_product loan_product FROM pf_loan l, pf_org_class oc, pf_users_real ur where l.id in ( :ids ) and l.class = oc.cid and l.uid = ur.uid";
        $ret = DB::select($sql, [':ids' => implode(',', $ids)]);
        //调整下各种展示,产品类型,资金方
        $loanTypes = BULoanProduct::getAllLoanType();
        foreach ($ret as $k => $v) {
            if (array_key_exists($v['loan_product'], $loanTypes)) {
                $ret[$k]['loan_product_desc'] = $loanTypes[$v['loan_product']];
            } else {
                $ret[$k]['loan_product_desc'] = '分期产品测试';
            }
            $ret[$k]['resource_desc'] = '资方';
        }
        return $ret;
    }
}