<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:46 PM
 */

namespace App\Models\ActiveRecord;

use Illuminate\Support\Facades\DB;

class ARPFUsersReal {

    const TABLE_NAME = 'pf_users_real';

    public static function getInfo($uid) {
        if (is_null($uid) || !is_numeric($uid) || $uid < 0) {
            return [];
        }
        $data = DB::table(self::TABLE_NAME . ' as ur')
                ->leftJoin(ARPfUsers::TABLE_NAME . ' as u', 'u.id', '=', 'ur.uid')
                ->select(['ur.*', 'u.phone'])
                ->where('ur.uid', $uid)
                ->first();
        return $data;
    }

}
