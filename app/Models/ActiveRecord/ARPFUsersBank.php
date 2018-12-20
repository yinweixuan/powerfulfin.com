<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:45 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\ArrayUtil;
use Illuminate\Support\Facades\DB;

class ARPFUsersBank
{
    const TABLE_NAME = 'pf_users_bank';

    public static function addUserBank($info = array())
    {
        $info = ArrayUtil::trimArray($info);
        return DB::table(self::TABLE_NAME)->insertGetId($info);
    }
}
