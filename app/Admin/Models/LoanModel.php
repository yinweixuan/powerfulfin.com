<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/28
 * Time: 3:58 PM
 */

namespace App\Admin\Models;


use App\Components\CheckUtil;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFLoanBill;
use App\Models\ActiveRecord\ARPFOrg;
use App\Models\ActiveRecord\ARPfUsers;
use App\Models\ActiveRecord\ARPFUsersBank;
use App\Models\ActiveRecord\ARPFUsersReal;
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
}
