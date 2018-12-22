<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:45 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\ArrayUtil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFUsersBank extends Model
{
    protected $table = 'pf_users_bank';
    const TABLE_NAME = 'pf_users_bank';

    public static function addUserBank($info = array())
    {
        $info = ArrayUtil::trimArray($info);
        return DB::table(self::TABLE_NAME)->insertGetId($info);
    }

    public static function getUserBanksByUid($uid)
    {
        if (!is_numeric($uid) || is_null($uid)) {
            return [];
        }

        $lists = DB::table(self::TABLE_NAME)->select('*')
            ->where("uid", $uid)->get()->toArray();
        return $lists;
    }

    public static function getUserBanksByUidAndBankAccount($uid, $bank_account)
    {
        if (!is_numeric($uid) || is_null($uid) || is_null($bank_account)) {
            return [];
        }

        $info = DB::table(self::TABLE_NAME)->select('*')
            ->where("uid", $uid)
            ->where("bank_account", $bank_account)
            ->first();
        return $info;
    }

    public static function updateBankInfo($id, $update = [])
    {
        $update = ArrayUtil::trimArray($update);

        return DB::table(self::TABLE_NAME)->where('id', $id)->update($update);
    }
}
