<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:47 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\ArrayUtil;
use App\Components\PFException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ARPFLoanBill extends Model
{
    protected $table = "pf_loan_bill";
    const TABLE_NAME = 'pf_loan_bill';

    public $timestamps = false;

    /**
     * 待还款
     */
    const STATUS_NO_REPAY = 0;
    /**
     * 已还款
     */
    const STATUS_REPAY = 1;
    /**
     * 已逾期
     */
    const STATUS_OVERDUE = 2;
    /**
     * 提前还款
     */
    const STATUS_ADVANCE_REPAY = 3;
    /**
     * 退课
     */
    const STATUS_WITHDRAW = 4;
    /**
     * 主动还款待确认
     */
    const STATUS_REPAYING = 5;


    /**
     * 是否转为课栈划扣
     */
    const PF_DEDUCTION_TRUE = 2;
    const PF_DEDUCTION_FALSE = 1;

    public static $statusDesp = [
        self::STATUS_NO_REPAY => '待还款',
        self::STATUS_REPAY => '已还款',
        self::STATUS_OVERDUE => '已逾期',
        self::STATUS_ADVANCE_REPAY => '提前还款',
        self::STATUS_WITHDRAW => '退课'
    ];

    public static function getLoanBillByLidAndUid($lid, $uid)
    {
        $lists = DB::table(self::TABLE_NAME)->select('*')
            ->where('lid', $lid)
            ->where('uid', $uid)
            ->get()->toArray();
        return $lists;
    }

    public static function getLoanBillByLid($lid)
    {
        $lists = DB::table(self::TABLE_NAME)->select('*')
            ->where('lid', $lid)
            ->orderBy('installment_plan')
            ->get()->toArray();
        return $lists;
    }

    public static function insertData($info)
    {
        $info = ArrayUtil::trimArray($info);

        if (empty($info)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        if (empty($info['uid'])) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }
        $ar = new ARPFLoanBill();
        $columns = Schema::getColumnListing(self::TABLE_NAME);
        foreach ($columns as $key) {
            if (array_key_exists($key, $info)) {
                $ar->$key = $info[$key];
            }
        }
        $ar->create_time = date('Y-m-d H:i:s');

        if (!$ar->save()) {

        }
        return $ar->getAttributes();
    }

    public static function _update($id, $update)
    {
        return DB::table(self::TABLE_NAME)->where('id', $id)
            ->update($update);
    }
}
