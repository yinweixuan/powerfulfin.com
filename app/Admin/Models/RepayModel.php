<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/21
 * Time: 2:32 PM
 */

namespace App\Admin\Models;


use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFLoanBill;
use App\Models\ActiveRecord\ARPFOrgHead;
use App\Models\ActiveRecord\ARPFUsersReal;
use Illuminate\Support\Facades\DB;

class RepayModel
{
    public static function repayLists(array $data)
    {
        $query = DB::table(ARPFLoanBill::TABLE_NAME . ' as lb')
            ->leftJoin(ARPFLoan::TABLE_NAME . ' as l', 'l.id', '=', 'lb.lid')
            ->leftJoin(ARPFUsersReal::TABLE_NAME . ' as ur', 'ur.uid', '=', 'lb.uid')
            ->leftJoin(ARPFOrgHead::TABLE_NAME . ' as oh', 'oh.hid', '=', 'l.hid')
            ->select(['lb.id', 'lb.lid', 'oh.full_name', 'ur.full_name as ur_full_name', 'l.resource', 'l.loan_time', 'l.borrow_money', 'lb.total', 'lb.principal', 'lb.interest', 'lb.overdue_days', 'lb.overdue_fine_interest', 'lb.overdue_fees', 'l.loan_product'])
            ->where('lb.bill_date', $data['bill_date']);

        if (!empty($data['docking_business']) && is_numeric($data['docking_business'])) {
            $query->where('oh.docking_business', $data['docking_business']);
        }

        if (!empty($data['resource']) && is_numeric($data['resource'])) {
            $query->where('l.resource', $data['resource']);
        }

        if (!empty($data['hasPayType'])) {
            if ($data['hasPayType'] == 1) {
                $query->whereIn('lb.status', [ARPFLoanBill::STATUS_REPAY, ARPFLoanBill::STATUS_ADVANCE_REPAY, ARPFLoanBill::STATUS_WITHDRAW]);
            } else if ($data['hasPayType'] == 2) {
                $query->whereIn('lb.status', [ARPFLoanBill::STATUS_NO_REPAY, ARPFLoanBill::STATUS_OVERDUE]);
            }
        }

        if (!empty($data['loan_product'])) {
            $query->where('l.loan_product', $data['loan_product']);
        }

        if (!empty($data['beginDate'])) {
            $query->where('l.loan_time', '>=', $data['beginDate'] . ' 00:00:00');
        }

        if (!empty($data['endDate'])) {
            $query->where('l.loan_time', '<=', $data['endDate'] . ' 23:59:59');
        }

        if (!empty($data['hid']) && is_numeric($data['hid'])) {
            $query->where('l.hid', $data['hid']);
        }

        $query->orderByDesc('lb.lid');
        $info = $query->paginate(10, ['lb.lid'], 'page', $data['page'])
            ->appends($data);
        return $info;
    }
}
