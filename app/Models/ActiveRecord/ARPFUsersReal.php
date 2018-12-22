<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:46 PM
 */

namespace App\Models\ActiveRecord;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFUsersReal extends Model
{
    protected $table = 'pf_users_real';
    const TABLE_NAME = 'pf_users_real';

    public static function getInfo($uid)
    {
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

    public static function _update($uid, $update)
    {
        if (is_null($uid) || !is_numeric($uid) || $uid < 0) {
            return false;
        }

        $update['update_time'] = date('Y-m-d H:i:s');
        return DB::table(self::TABLE_NAME)->where('uid', $uid)->update($update);
    }

    public static function checkErrorUserInfo($uid, $identity_number)
    {
        if (is_null($uid) || !is_numeric($uid) || $uid < 0) {
            return false;
        }

        if (is_null($identity_number) || strlen($identity_number) != 18) {
            return false;
        }

        $data = DB::table(self::TABLE_NAME)
            ->select('*')
            ->where('uid', '!=', $uid)
            ->where('identity_number', '!=', $identity_number)
            ->first();
        if (empty($data)) {
            return true;
        } else {
            return false;
        }
    }

}
