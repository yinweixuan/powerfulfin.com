<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:46 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\ArrayUtil;
use Illuminate\Support\Facades\DB;

class ARPFUsersPhonebook
{
    const TABLE_NAME = 'pf_users_phonebook';

    public static function addUserPhoneBook($info = array())
    {
        $info = ArrayUtil::trimArray($info);
        return DB::table(self::TABLE_NAME)->insertGetId($info);
    }
}
