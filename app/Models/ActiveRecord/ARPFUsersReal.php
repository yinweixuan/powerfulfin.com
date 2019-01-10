<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:46 PM
 */

namespace App\Models\ActiveRecord;

use App\Components\ArrayUtil;
use App\Components\PFException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ARPFUsersReal extends Model
{
    protected $table = 'pf_users_real';
    const TABLE_NAME = 'pf_users_real';

    public $timestamps = false;

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

    public static function addUserReal(array $info)
    {
        $info = ArrayUtil::trimArray($info);

        if (empty($info)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        if (empty($info['uid'])) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        $ar = new self();
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

    public static function updateInfo($uid, array $info)
    {
        if (is_null($uid) || !is_numeric($uid) || $uid < 0) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }
        $info = ArrayUtil::trimArray($info);

        $userReal = DB::table(self::TABLE_NAME)->select('*')
            ->where('uid', $uid)
            ->first();

        if (empty($userReal)) {
            $info['uid'] = $uid;
            self::addUserReal($info);
        } else {
            $info['update_time'] = date('Y-m-d H:i:s');
            $columns = Schema::getColumnListing(self::TABLE_NAME);

            $update = [];
            foreach ($columns as $key) {
                if (array_key_exists($key, $info) && $info[$key] != $userReal[$key]) {
                    $update[$key] = $info[$key];
                }
            }
            if (empty($update)) {
                return [];
            }
            return DB::table(self::TABLE_NAME)->where('uid', $uid)->update($update);
        }
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
            ->where('identity_number', '=', $identity_number)
            ->first();
        return $data;
    }

}
