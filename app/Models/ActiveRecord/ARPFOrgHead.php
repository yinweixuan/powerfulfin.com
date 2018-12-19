<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:47 PM
 */

namespace App\Models\ActiveRecord;

use Illuminate\Support\Facades\DB;

class ARPFOrgHead {

    const TABLE_NAME = 'pf_org_head';

    public static function getInfo($hid) {
        if (is_null($hid) || !is_numeric($hid) || $hid < 0) {
            return [];
        }
        $data = DB::table(self::TABLE_NAME)
                ->select(['*'])
                ->where('hid', $hid)
                ->first();
        return $data;
    }
    
     public static function update($hid, $update) {
        if (is_null($hid) || !is_numeric($hid) || $hid < 0) {
            return false;
        }
        $update['update_time'] = date('Y-m-d H:i:s');
        return DB::table(self::TABLE_NAME)->where('hid', $hid)->update($update);
    }

}
