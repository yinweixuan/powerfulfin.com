<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/28
 * Time: 3:58 PM
 */

namespace App\Admin\Models;


use App\Components\CheckUtil;
use App\Components\OutputUtil;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFLoanBill;
use App\Models\ActiveRecord\ARPFOrg;
use App\Models\ActiveRecord\ARPFOrgClass;
use App\Models\ActiveRecord\ARPfUsers;
use App\Models\ActiveRecord\ARPFUsersBank;
use App\Models\ActiveRecord\ARPFUsersReal;
use App\Models\Calc\CalcMoney;
use App\Models\Server\BU\BULoanStatus;
use Illuminate\Support\Facades\DB;

class LoanModel
{
    public static function getLoanList($data)
    {
        $query = DB::table(ARPFLoan::TABLE_NAME . ' as l')
            ->select(['l.*', 'u.phone', 'ur.full_name', 'ur.identity_number', 'ub.bank_account', 'ub.bank_name', 'o.org_name'])
            ->leftJoin(ARPfUsers::TABLE_NAME . ' as u', 'u.id', '=', 'l.uid')
            ->leftJoin(ARPFUsersReal::TABLE_NAME . ' as ur', 'ur.uid', '=', 'l.uid')
            ->leftJoin(ARPFUsersBank::TABLE_NAME . ' as ub', 'ub.uid', '=', 'l.uid')
            ->leftJoin(ARPFOrg::TABLE_NAME . ' as o', 'o.id', '=', 'l.oid')
            ->where('ub.type', 1);
        if (!empty($data['stuname'])) {
            if (is_numeric($data['stuname'])) {
                $query->where('l.id', $data['stuname'])->orWhere('l.uid', $data['stuname']);
            } else {
                $query->where('ur.full_name', 'like', '%' . $data['stuname'] . '%');
            }
        }

        if (!empty($data['org_name'])) {
            if (is_numeric($data['org_name'])) {
                $query->where('l.oid', $data['org_name']);
            } else {
                $query->where('o.org_name', 'like', '%' . $data['org_name'] . '%');
            }
        }

        if (!empty($data['hid']) && is_numeric($data['hid'])) {
            $query->where('l.hid', $data['hid']);
        }

        if (!empty($data['resource_loan_id'])) {
            $query->where('l.resource_loan_id', $data['resource_loan_id']);
        }

        if (!empty($data['identity_number'])) {
            $query->where('ur.identity_number', 'like', '%' . $data['identity_number'] . '%');
        }

        if (!empty($data['status']) && is_numeric($data['status'])) {
            $query->where('l.status', $data['status']);
        }
        if (!empty($data['phone']) && CheckUtil::phone($data['phone'])) {
            $query->where('u.phone', $data['phone']);
        }

        if (!empty($data['resource']) && is_numeric($data['resource'])) {
            $query->where('l.resource', $data['resource']);
        }

        if (!empty($data['beginDate'])) {
            $query->where('l.loan_time', '>=', $data['beginDate'] . ' 00:00:00');
        }

        if (!empty($data['endDate'])) {
            $query->where('l.loan_time', '<=', $data['endDate'] . ' 23:59:59');
        }

        if (!empty($data['bank_code'])) {
            $query->where('ub.bank_code', $data['bank_code']);
        }

        if (!empty($data['check_user'])) {
            $query->where('l.auditer', $data['check_user']);
        }

        $query->orderByDesc('l.id');
        $info = $query->paginate(10, ['l.id'], 'page', $data['page'])
            ->appends($data);
        return $info;
    }

    public static function getLoanBill($data)
    {
        if (empty($data['lid']) && empty($data['uid'])) {
            return [];
        }
        $query = DB::table(ARPFLoanBill::TABLE_NAME)
            ->select('*');

        if (!empty($data['lid']) && is_numeric($data['lid'])) {
            $query->where('lid', $data['lid']);
        }

        if (!empty($data['uid']) && is_numeric($data['uid'])) {
            $query->where('uid', $data['uid']);
        }
        return $query->get()->toArray();
    }

    /**
     * 数据汇总
     * @return array
     */
    public static function summary()
    {
        $ret = array();
        //学校总数
        $sql = "SELECT can_loan,count(id) as 'count' FROM " . ARPFOrg::TABLE_NAME . " WHERE status='" . STATUS_SUCCESS . "' group by can_loan;";
        $res = DB::select($sql);

        $ret['school_count'] = 0;
        $ret['school_count_can_loan'] = 0;
        foreach ($res as $r) {
            if ($r['can_loan'] == STATUS_SUCCESS) {
                $ret['school_count_can_loan'] += $r['count'];
            }
            $ret['school_count'] += $r['count'];
        }
        //课程总数
        $orgClassCount = DB::table(ARPFOrgClass::TABLE_NAME)
            ->where('status', STATUS_SUCCESS)
            ->count('cid');
        $ret['course_count'] = $orgClassCount;
        //用户统计
        $userCount = DB::table(ARPfUsers::TABLE_NAME)->count('id');
        $ret['user_count'] = $userCount;

        //分期统计
        $sql = "select status,count(id) as 'count',sum(borrow_money) as 'sum_apply',sum(org_receivable) as 'sum_school' from " . ARPFLoan::TABLE_NAME . " group by status order by status asc";
        $res = DB::select($sql);
        $ret['loan'] = array();
        $ret['loan_count'] = 0;
        $ret['loan_sum_apply'] = 0;
        $ret['loan_sum_school'] = 0;
        $ret['loan_sum_get_money'] = 0;

        $ret['tiexi_count'] = 0;
        $ret['tiexi_sum_apply'] = 0;
        $ret['tiexi_sum_school'] = 0;
        $ret['tiexi_sum_get_money'] = 0;

        $ret['tanxing_count'] = 0;
        $ret['tanxing_sum_apply'] = 0;
        $ret['tanxing_sum_school'] = 0;
        $ret['tanxing_sum_get_money'] = 0;

        $ret['reject_loan_count'] = 0;
        $ret['reject_loan_sum_apply'] = 0;
        $ret['reject_loan_sum_school'] = 0;
        $ret['reject_loan_sum_get_money'] = 0;
        $combineStatusArr = array(LOAN_5000_SCHOOL_BEGIN, LOAN_6000_NOTICE_MONEY);
        $rejectStatusArr = array(LOAN_2100_SCHOOL_REFUSE, LOAN_3100_PF_REFUSE, LOAN_4100_P2P_REFUSE, LOAN_5100_SCHOOL_REFUSE, LOAN_5200_SCHOOL_STOP, LOAN_12000_DROP);
        foreach ($res as $r) {
            if (in_array($r['status'], $rejectStatusArr)) {
                $ret['reject_loan_count'] += $r['count'];
                $ret['reject_loan_sum_apply'] += $r['sum_apply'];
                $ret['reject_loan_sum_school'] += $r['sum_school'];
            } else {
                $ret['loan_count'] += $r['count'];
                $ret['loan_sum_apply'] += $r['sum_apply'];
                $ret['loan_sum_school'] += $r['sum_school'];
            }
            $r['sum_get_money_desp'] = 0;
            if ($r['status'] >= 100 && $r['status'] != LOAN_12000_DROP) {
                $ret['loan_sum_get_money'] += $r['sum_school'];
                $r['sum_get_money_desp'] = CalcMoney::calcMoney($r['sum_school']);
            }
            $r['status_desp'] = BULoanStatus::getStatusDescriptionForAdmin($r['status']);
            $r['sum_apply_desp'] = CalcMoney::calcMoney($r['sum_apply']);
            $r['sum_school_desp'] = CalcMoney::calcMoney($r['sum_school']);
            if (in_array($r['status'], $combineStatusArr)) {
                $r['status_desp'] = BULoanStatus::getStatusDescriptionForAdmin($r['status']);
                if (array_key_exists(LOAN_STAT_NO_1001, $ret['loan'])) {
                    foreach ($r as $k => $v) {
                        if (is_numeric($v)) {
                            $ret['loan'][LOAN_STAT_NO_1001][$k] += $v;
                        }
                    }
                } else {
                    $ret['loan'][LOAN_STAT_NO_1001] = $r;
                }
                $ret['loan'][LOAN_STAT_NO_1001]['status'] = LOAN_STAT_NO_1001;
            } else if ($r['status'] == LOAN_11100_OVERDUE) {
                $ret['loan'][$r['status']] = $r;
                $ret['loan'][$r['status']]['sum_apply_desp'] = $r['count'] - 3909;
            } else {
                $ret['loan'][$r['status']] = $r;
            }
        }
        $ret['loan_sum_apply'] = CalcMoney::calcMoney($ret['loan_sum_apply']);
        $ret['loan_sum_school'] = CalcMoney::calcMoney($ret['loan_sum_school']);
        $ret['loan_sum_get_money'] = CalcMoney::calcMoney($ret['loan_sum_get_money']);
        return $ret;
    }
}
