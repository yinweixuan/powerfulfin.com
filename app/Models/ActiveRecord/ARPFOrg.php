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

class ARPFOrg extends Model
{
    protected $table = 'pf_org';
    const TABLE_NAME = 'pf_org';

    public $timestamps = false;

    public static function getOrgById($id)
    {
        if (is_null($id) || !is_numeric($id)) {
            return [];
        }

        return DB::table(self::TABLE_NAME)->select('*')
            ->where('id', $id)
            ->first();
    }

    public static function getOrgByHid($hid)
    {
        if (is_null($hid) || !is_numeric($hid)) {
            return [];
        }

        return DB::table(self::TABLE_NAME)->select('*')
            ->where('hid', $hid)
            ->get()->toArray();
    }

    public static function getOrgByOrgName($org_name)
    {
        if (empty($org_name)) {
            return false;
        }

        return DB::table(self::TABLE_NAME)->select('*')
            ->where('org_name', $org_name)
            ->first();
    }

    public static function addOrg($info)
    {
        $info = ArrayUtil::trimArray($info);

        if (empty($info)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        $ar = new ARPFOrg();
        $columns = Schema::getColumnListing(self::TABLE_NAME);
        foreach ($columns as $key) {
            if (array_key_exists($key, $info)) {
                $ar->$key = $info[$key];
            }
        }

        if (!$ar->save()) {

        }
        return $ar->getAttributes();
    }

    public static function updateInfo($id, $update)
    {
        if (is_null($id) || !is_numeric($id) || $id < 0) {
            return false;
        }
        $update['update_time'] = date('Y-m-d H:i:s');
        return DB::table(self::TABLE_NAME)->where('id', $id)->update($update);
    }
}
