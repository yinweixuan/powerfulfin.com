<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:47 PM
 */

namespace App\Models\ActiveRecord;

use App\Components\ArrayUtil;
use App\Components\PFException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ARPFOrgHead extends Model
{
    protected $table = 'pf_org_head';
    const TABLE_NAME = 'pf_org_head';

    public $timestamps = false;

    public static function getInfo($hid)
    {
        if (is_null($hid) || !is_numeric($hid) || $hid < 0) {
            return [];
        }
        $data = DB::table(self::TABLE_NAME)
            ->select(['*'])
            ->where('hid', $hid)
            ->first();
        return $data;
    }

    public static function updateInfo($hid, $update)
    {
        if (is_null($hid) || !is_numeric($hid) || $hid < 0) {
            return false;
        }
        $update['update_time'] = date('Y-m-d H:i:s');
        return DB::table(self::TABLE_NAME)->where('hid', $hid)->update($update);
    }

    public static function getInfoByFullName($full_name)
    {
        if (empty($full_name)) {
            return false;
        }
        return DB::table(self::TABLE_NAME)->select('*')
            ->where('full_name', $full_name)
            ->get()->toArray();
    }

    public static function getInfoByBusinessLicense($business_license)
    {
        if (empty($business_license)) {
            return false;
        }
        return DB::table(self::TABLE_NAME)->select('*')
            ->where('business_license', $business_license)
            ->get()->toArray();

    }

    public static function addHead($info)
    {
        $info = ArrayUtil::trimArray($info);

        if (empty($info)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }
        
        $ar = new ARPFOrgHead();
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

}
