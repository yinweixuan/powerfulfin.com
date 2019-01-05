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

class ARPFOrgUsers extends Model
{
    protected $table = 'pf_org_users';
    const TABLE_NAME = 'pf_org_users';

    public $timestamps = false;

    public static function getOrgUserInfoByOrgUserName($org_username)
    {
        if (empty($org_username)) {
            return [];
        }

        return DB::table(self::TABLE_NAME)
            ->select('*')
            ->where('org_username', $org_username)
            ->first();
    }

    public static function addOrgUser($info)
    {
        $info = ArrayUtil::trimArray($info);

        if (empty($info)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        $ar = new ARPFOrgUsers();
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
