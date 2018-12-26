<?php
/**
 * 根据需要拼装展示数据
 * User: haoxiang
 * Date: 2018/12/25
 * Time: 7:40 PM
 */

namespace App\Models\Org;

use App\Components\ArrayUtil;
use App\Models\ActiveRecord\ARPFLoanProduct;
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
        $sql = "SELECT l.id id,l.id lid,ur.full_name full_name,ur.identity_number identity_number,oc.class_name class_name,oc.class_price class_price,l.borrow_money borrow_money,l.create_time create_time,l.resource resource, l.status status,l.loan_product loan_product FROM pf_loan l left join pf_org_class oc on l.class = oc.cid left join pf_users_real ur on l.uid = ur.uid where l.id in (" . implode(',', $ids) . ")";
        $result = DB::select($sql);
        $result = ArrayUtil::addKeyToArray($result, 'id');
        $ret = [];
        //调整下各种展示,产品类型,资金方,并且按照id传入顺序重整数据
        $loanTypes = BULoanProduct::getAllLoanType();
        foreach ($ids as $id) {
            if (!array_key_exists($id, $result)) {
                continue;
            }
            $tmp = $result[$id];
            if (array_key_exists($tmp['loan_product'], $loanTypes)) {
                $tmp['loan_product_desc'] = $loanTypes[$v['loan_product']];
            } else {
                $tmp['loan_product_desc'] = '未知产品';
            }
            $tmp['resource_desc'] = BULoanProduct::getResourceCompany($tmp['resource'], true);
            $ret[$id] = $tmp;
        }
        return $ret;
    }
}