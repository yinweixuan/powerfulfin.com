<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:45 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\ArrayUtil;
use App\Components\PFException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

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

        if (empty($info['uid'])) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        $info = ArrayUtil::trimArray($info);

        $ar = new self();

        $columns = Schema::getColumnListing(self::TABLE_NAME);
        foreach ($columns as $key) {
            if (array_key_exists($key, $info)) {
                $ar->$key = $info[$key];
            }
        }
        $ar->create_time = date('Y-m-d H:i:s');
        $ar->save();
        return $ar->getAttributes();
    }

    public static function getUserLocation($uid)
    {
        return DB::table(self::TABLE_NAME)->select('*')
            ->where('uid', $uid)
            ->orderByDesc('id')
            ->first();
    }

    public static function _update($id, $update) {
        return DB::table(self::TABLE_NAME)
            ->where('id', $id)
            ->update($update);
    }
}
