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

class ARPFOrgClass extends Model
{
    protected $table = 'pf_org_class';
    const TABLE_NAME = 'pf_org_class';

    public $timestamps = false;

    public static function getClassByOidWhichCanLoan($oid)
    {
        return DB::table(self::TABLE_NAME)->select("*")
            ->where('oid', $oid)
            ->where('status', STATUS_SUCCESS)
            ->get()->toArray();
    }

    public static function getById($cid)
    {
        return DB::table(self::TABLE_NAME)->select('*')
            ->where('cid', $cid)
            ->first();
    }

}
