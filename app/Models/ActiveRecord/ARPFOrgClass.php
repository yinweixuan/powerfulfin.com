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

class ARPFOrgClass extends Model
{
    protected $table = 'pf_org_class';
    const TABLE_NAME = 'pf_org_class';

    public $timestamps = false;

    public static function getClassByOidWhichCanLoan($oid)
    {
        return DB::table(self::TABLE_NAME)->select("*")
            ->where('oid', $oid)
            ->where('status', STATUS_SUCCESS)
            ->get()->toArray();
    }

    public static function getById($cid)
    {
        return DB::table(self::TABLE_NAME)->select('*')
            ->where('cid', $cid)
            ->first();
    }

    public static function addClass($info)
    {
        $info = ArrayUtil::trimArray($info);

        if (empty($info)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        $ar = new ARPFOrgClass();
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
