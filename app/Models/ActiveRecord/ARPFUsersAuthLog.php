<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:46 PM
 */

namespace App\Models\ActiveRecord;


use Illuminate\Support\Facades\DB;

class ARPFUsersAuthLog
{
    const TABLE_NAME = 'pf_users_auth_log';

    /**
     * 认证结果
     */
    const RESULT_AUTH_FALSE = 1;    //认真不通过
    const RESULT_AUTH_TRUE = 2; //认证通过
    /**
     * 性别
     */
    const SEX_MALE = 1;     //男
    const SEX_FEMALE = 2;   //女

    const USER_ID_SUFFIX = 'PF_';
    const REDIS_KEY = 'PF_UDCREDIT_';

    const SAFE_MODE_HIGH = 0;
    const SAFE_MODE_MIDDLE = 1;
    const SAFE_MODE_LOW = 2;

    public static function getUserAuthSuccessLast($uid)
    {
        $info = DB::table(self::TABLE_NAME)->select('*')
            ->where(
                ['uid' => $uid],
                ['result_auth' => self::RESULT_AUTH_TRUE],
                ['be_idcard', '>=', '0.8']
            )
            ->orderBy('id DESC')
            ->first();
        if (empty($info)) {
            return [];
        } else {
            return $info;
        }
    }
}
