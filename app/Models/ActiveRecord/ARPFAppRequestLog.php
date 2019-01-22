<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/1/22
 * Time: 11:15 AM
 */

namespace App\Models\ActiveRecord;


use App\Components\ArrayUtil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFAppRequestLog extends Model
{
    protected $table = 'pf_app_request_log';
    const TABLE_NAME = 'pf_app_request_log';

    public static function addInfo($info)
    {
        $info = ArrayUtil::trimArray($info);
        return DB::table(self::TABLE_NAME)->insertGetId($info);
    }
}
