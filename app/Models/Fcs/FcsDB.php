<?php

namespace App\Models\Fcs;

use App\Models\ActiveRecord\ARPFLoanLog;
use App\Models\ActiveRecord\ARPFLoanProduct;
use App\Models\ActiveRecord\ARPFOrg;
use App\Models\ActiveRecord\ARPFOrgClass;
use App\Models\ActiveRecord\ARPFOrgHead;
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
            ->join(ARPFUsersReal::TABLE_NAME . ' as ur', 'ur.uid', '=', 'l.uid')
            ->join(ARPFUsersBank::TABLE_NAME . ' as ub', 'ub.uid', '=', 'l.uid')
            ->join(ARPFUsersWork::TABLE_NAME . ' as uw', 'uw.uid', '=', 'l.uid')
            ->join(ARPFUsersContact::TABLE_NAME . ' as uc', 'uc.uid', '=', 'l.uid')
            ->join(ARPFOrgHead::TABLE_NAME . ' as oh', 'oh.hid', '=', 'l.hid')
            ->join(ARPFOrgClass::TABLE_NAME . ' as oc', 'oc.cid', '=', 'l.cid')
            ->select(['*', 'l.id', 'l.id as lid'])
            ->where('l.id', $lid)
            ->whereRaw('l.resource in(' . RESOURCE_FCS . ',' . RESOURCE_FCS_SC . ') and ub.type=1 '
                . 'and (l.resource_loan_id="" or l.resource_loan_id is null) ')
            ->first();
        if (!empty($data)) {
            //风险提示信息
            $data['otherRiskInfo'] = '无';
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
            $update['audit_opinion'] = substr($update['audit_opinion'], 0, 240);
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
        if (!$loan && !config('app.env') == 'local') {
            throw new \Exception('没有查到对应的贷款');
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


}
