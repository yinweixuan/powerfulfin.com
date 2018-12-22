<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/22
 * Time: 2:13 PM
 */

namespace App\Models\ActiveRecord;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFLoan extends Model
{
    protected $table = 'pf_loan';

    const TABLE_NAME = 'pf_loan';

    public static function getLoanById($id)
    {
        if (is_null($id) || !is_numeric($id)) {
            return [];
        }

        return DB::table(self::TABLE_NAME)->select('*')
            ->where('id', $id)
            ->first();
    }

    public static function getLoanByUid($uid)
    {
        if (is_null($uid) || !is_numeric($uid)) {
            return [];
        }

        return DB::table(self::TABLE_NAME)->select('*')
            ->where('uid', $uid)
            ->get()->toArray();
    }
}
