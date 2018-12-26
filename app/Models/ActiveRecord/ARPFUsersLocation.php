<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:45 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\PFException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFUsersLocation extends Model
{
    protected $table = 'pf_users_location';
    const TABLE_NAME = 'pf_users_location';
    public $timestamps = false;

    public static function addUserLocation(array $info)
    {
        if (empty($info)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        return DB::table(self::TABLE_NAME)->insertGetId($info);
    }
}
