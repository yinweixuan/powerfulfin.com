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
use Illuminate\Support\Facades\Schema;

class ARPFUsersBank extends Model
{
    protected $table = 'pf_users_bank';
    const TABLE_NAME = 'pf_users_bank';
    public $timestamps = false;

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

    public static function updateBankInfo($id, $info = [])
    {
        $info = ArrayUtil::trimArray($info);
        $columns = Schema::getColumnListing(self::TABLE_NAME);

        $update = [];
        foreach ($info as $key => $value) {
            if (array_key_exists($key, $columns)) {
                $update[$key] = $value;
            }
        }
        if (empty($update)) {
            return [];
        }

        return DB::table(self::TABLE_NAME)->where('id', $id)->update($update);
    }

    public static function getUserRepayBankByUid($uid)
    {
        return DB::table(self::TABLE_NAME)->select('*')
            ->where('uid', $uid)
            ->where('type', 1)
            ->first();
    }
}
