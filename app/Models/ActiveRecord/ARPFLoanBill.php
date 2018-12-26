<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:47 PM
 */

namespace App\Models\ActiveRecord;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFLoanBill extends Model
{
    protected $table = "pf_loan_bill";
    const TABLE_NAME = 'pf_loan_bill';

    public $timestamps = false;

    public static function getLoanBillByLidAndUid($lid, $uid)
    {
        $lists = DB::table(self::TABLE_NAME)->select('*')
            ->where('lid', $lid)
            ->where('uid', $uid)
            ->get()->toArray();
        return $lists;
    }
}
