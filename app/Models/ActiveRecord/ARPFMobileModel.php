<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/20
 * Time: 6:12 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\ArrayUtil;
use Illuminate\Support\Facades\DB;

class ARPFMobileModel
{
    const TABLE_NAME = 'pf_mobile_model';

    public static function addInfo($info = [])
    {
        $info = ArrayUtil::trimArray($info);
        return DB::table(self::TABLE_NAME)->insertGetId($info);
    }
}
