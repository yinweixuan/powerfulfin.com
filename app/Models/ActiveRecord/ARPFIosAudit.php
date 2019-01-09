<?php
/**
 * Created by PhpStorm.
 * User: xuyang
 * Date: 2019/1/9
 * Time: 15:20
 */

namespace App\Models\ActiveRecord;


use Illuminate\Support\Facades\DB;

class ARPFIosAudit {

    const TABLE_NAME = 'pf_ios_audit';

    public static function add($data) {
        $data['create_time'] = date('Y-m-d H:i:s');
        $r = DB::table(self::TABLE_NAME)->insert($data);
        return $r;
    }

    public static function getList($uid) {
        $list = DB::table(self::TABLE_NAME . ' as ios')
            ->select('ios.*')
            ->where('uid', $uid)
            ->get()
            ->toArray();
        return $list;
    }

    public static function getCourseList($oid) {
        $list = DB::table(ARPFOrgClass::TABLE_NAME)
            ->select('*')
            ->limit(10)
            ->get()
            ->toArray();
        return $list;
    }

}