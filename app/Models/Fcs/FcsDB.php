<?php

/**
 * 富登数据库操作
 */

namespace App\Models\Fcs;

use App\Models\ActiveRecord\ARPFLoanBill;
use App\Models\ActiveRecord\ARPFLoanLog;
use App\Models\ActiveRecord\ARPFLoanProduct;
use App\Models\ActiveRecord\ARPFOrg;
use App\Models\ActiveRecord\ARPFOrgClass;
use App\Models\ActiveRecord\ARPFOrgHead;
use App\Models\ActiveRecord\ARPfUsers;
use App\Models\ActiveRecord\ARPFUsersAuthLog;
use App\Models\ActiveRecord\ARPFUsersLocation;
use Illuminate\Support\Facades\DB;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFUsersReal;
use App\Models\ActiveRecord\ARPFUsersBank;
use App\Models\ActiveRecord\ARPFUsersWork;
use App\Models\ActiveRecord\ARPFUsersContact;

/**
 * 查询富登所需数据
 */
class FcsDB {

    /**
     * 获取分期申请数据
     */
    public static function getApplyData($lid) {
        $data = DB::table(ARPFLoan::TABLE_NAME . ' as l')
            ->join(ARPfUsers::TABLE_NAME . ' as u', 'u.id', '=', 'l.uid')
            ->join(ARPFUsersReal::TABLE_NAME . ' as ur', 'ur.uid', '=', 'l.uid')
            ->join(ARPFUsersBank::TABLE_NAME . ' as ub', 'ub.uid', '=', 'l.uid')
            ->join(ARPFUsersWork::TABLE_NAME . ' as uw', 'uw.uid', '=', 'l.uid')
            ->join(ARPFUsersContact::TABLE_NAME . ' as uc', 'uc.uid', '=', 'l.uid')
            ->join(ARPFUsersLocation::TABLE_NAME . ' as ul', 'ul.uid', '=', 'l.uid')
            ->join(ARPFUsersAuthLog::TABLE_NAME . ' as ua', 'ua.uid', '=', 'l.uid')
            ->join(ARPFOrg::TABLE_NAME . ' as o', 'o.id', '=', 'l.oid')
            ->join(ARPFOrgHead::TABLE_NAME . ' as oh', 'oh.hid', '=', 'l.hid')
            ->join(ARPFOrgClass::TABLE_NAME . ' as oc', 'oc.cid', '=', 'l.cid')
            ->join(ARPFLoanProduct::TABLE_NAME . ' as lp', 'lp.loan_product', '=', 'l.loan_product')
            ->select([
                '*', 'l.id', 'l.id as lid', 'oh.full_name as org_full_name', 'ua.id as uaid',
                'ur.full_name as full_name', 'l.status as status', 'ul.id as location_id'
            ])
            ->where('l.id', $lid)
            ->whereRaw('l.resource in(' . RESOURCE_FCS . ',' . RESOURCE_FCS_SC . ') and ub.type=1 '
                . 'and (l.resource_loan_id="" or l.resource_loan_id is null) '
                . 'and ua.result_auth=2 and ua.be_idcard>0.8')
            ->orderByDesc('uaid')
            ->orderByDesc('location_id')
            ->first();
        if (!empty($data)) {
            //历史拒绝次数
            $reject_count = DB::table(ARPFLoan::TABLE_NAME)
                ->select(DB::raw('count(*) as c'))
                ->where('uid', $data['uid'])
                ->first();
            $data['historyRejectNum'] = $reject_count['c'] - 1 < 0 ? 0 : $reject_count['c'] - 1;
        }
        return $data;
    }

    /**
     * 获取合同数据
     */
    public static function getLoanContractData($lid) {
        $data = DB::table(ARPFLoan::TABLE_NAME . ' as l')
            ->join(ARPFUsersReal::TABLE_NAME . ' as ur', 'ur.uid', '=', 'l.uid')
            ->join(ARPFUsersBank::TABLE_NAME . ' as ub', 'ub.uid', '=', 'l.uid')
            ->join(ARPFUsersWork::TABLE_NAME . ' as uw', 'uw.uid', '=', 'l.uid')
            ->join(ARPFUsersContact::TABLE_NAME . ' as uc', 'uc.uid', '=', 'l.uid')
            ->join(ARPFOrg::TABLE_NAME . ' as o', 'o.id', '=', 'l.oid')
            ->join(ARPFOrgHead::TABLE_NAME . ' as oh', 'oh.hid', '=', 'l.hid')
            ->join(ARPFLoanProduct::TABLE_NAME . ' as lp', 'lp.loan_product', '=', 'l.loan_product')
            ->select([
                '*', 'l.id', 'l.id as lid', 'oh.full_name as org_full_name',
                'ur.full_name as full_name', 'l.status as status'
            ])
            ->where('l.id', $lid)
            ->whereRaw('l.resource in(' . RESOURCE_FCS . ',' . RESOURCE_FCS_SC . ') and ub.type=1')
            ->first();
        return $data;
    }

    /**
     * 更新分期信息，记录文件日志，记录数据库分期操作日志
     */
    public static function updateLoan($lid, $update, $loan = [], $reason = '') {
        if (isset($update['audit_opinion'])) {
            if (!$reason) {
                $reason = $update['audit_opinion'];
            }
        }
        $update['update_time'] = date('Y-m-d H:i:s');
        $where_str = 'resource in(' . RESOURCE_FCS . ',' . RESOURCE_FCS_SC . ') ';
        if (!empty($update['resource_loan_id'])) {
            $where_str .= 'and (resource_loan_id="" or resource_loan_id is null)';
        }
        DB::table(ARPFLoan::TABLE_NAME)
            ->where('id', '=', $lid)
            ->whereRaw($where_str)
            ->update($update);
        FcsUtil::log('lid:' . $lid . '：' . print_r($update, true) . PHP_EOL);
        if (!empty($loan)) {
            if (!empty($update['status'])) {
                $status = $update['status'];
            } else {
                $status = $loan['status'];
            }
            ARPFLoanLog::insertLog($loan, $status, $reason);
        }
    }

    /**
     * 根据富登订单号查询分期信息
     */
    public static function getLoanByFcsLoanId($fcs_loan_id) {
        $loan = DB::table(ARPFLoan::TABLE_NAME)
            ->select()
            ->where('resource_loan_id', $fcs_loan_id)
            ->whereRaw('resource in(' . RESOURCE_FCS . ',' . RESOURCE_FCS_SC . ') ')
            ->first();
        if (!$loan) {
            throw new \Exception('没有查到对应的分期');
        }
        return $loan;
    }

    /**
     * 根据富登订单号查询分期信息
     */
    public static function getLoanByLid($lid) {
        $loan = DB::table(ARPFLoan::TABLE_NAME)
            ->select()
            ->where('id', $lid)
            ->whereRaw('resource in(' . RESOURCE_FCS . ',' . RESOURCE_FCS_SC . ') '
                . 'and resource_loan_id != ""')
            ->first();
        return $loan;
    }

    /**
     * 获取最大合同号
     */
    public static function getLargestContractNo() {
        $loan = DB::table(ARPFLoan::TABLE_NAME)
            ->select()
            ->whereRaw('resource in(' . RESOURCE_FCS . ',' . RESOURCE_FCS_SC . ') '
                . 'and resource_contract_id like "富登%"')
            ->orderBy('resource_contract_id', 'desc')
            ->first();
        if (!empty($loan['resource_contract_id'])) {
            $no = substr($loan['resource_contract_id'], strpos($loan['resource_contract_id'], config('fcs.partner_no')) + 2, 7);
        } else {
            $no = 0;
        }
        return $no;
    }

    /**
     * 获取永久拒绝单
     */
    public static function getRefusedLoan($uid) {
        $loan = DB::table(ARPFLoan::TABLE_NAME)
            ->select()
            ->where('uid', $uid)
            ->whereRaw('resource in(' . RESOURCE_FCS . ',' . RESOURCE_FCS_SC . ') '
                . 'and status=' . LOAN_14000_FOREVER_REFUSE)
            ->first();
        return $loan;
    }

    /**
     * 获取前一日新增已放款分期
     */
    public static function getNewLoanList() {
        $yesterday = date('Y-m-d 00:00:00', time() - 86400);
        $list = DB::table(ARPFLoan::TABLE_NAME)
            ->select()
            ->whereRaw('loan_time>="' . $yesterday . '" and status = ' . LOAN_10000_REPAY . ' and resource in (' . RESOURCE_FCS . ',' . RESOURCE_FCS_SC . ') and resource_loan_id != ""')
            ->get()->toArray();
        return $list;
    }

    /**
     * 获取待更新还款计划表的分期
     */
    public static function getUpdateBillList() {
        $bill_date = FcsUtil::getCurrentBillDate();
        $list = DB::table(ARPFLoan::TABLE_NAME . ' as l')
            ->join(ARPFLoanBill::TABLE_NAME . ' as b', 'b.lid', '=', 'l.id')
            ->select(['l.id'])
            ->whereRaw('l.status in (' . LOAN_10000_REPAY . ',' . LOAN_11100_OVERDUE . ') '
                . 'and l.resource in (' . RESOURCE_FCS . ',' . RESOURCE_FCS_SC . ') '
                . 'and l.resource_loan_id != "" '
                . 'and b.bill_date="' . $bill_date . '"'
                . 'and b.status in (' . ARPFLoanBill::STATUS_NO_REPAY . ',' . ARPFLoanBill::STATUS_OVERDUE . ')')
            ->get()->toArray();
        return $list;
    }

    /**
     * 获取夜间审核分期列表
     */
    public static function getNightAuditList() {
        $list = DB::table(ARPFLoan::TABLE_NAME)
            ->select()
            ->whereRaw('hid=124704 and status=' . LOAN_4200_DATA_P2P_SEND . ' and resource in (' . RESOURCE_FCS . ',' . RESOURCE_FCS_SC . ') and resource_loan_id != ""')
            ->get()->toArray();
        return $list;
    }

    /**
     * 删除现有的还款计划表
     */
    public static function deleteLoanBill($lid) {
        return DB::table(ARPFLoanBill::TABLE_NAME)->where('lid', $lid)->delete();
    }

}
