<?php

namespace App\Models\ActiveRecord;

use App\Components\ArrayUtil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFOrgWifi extends Model
{

    protected $table = 'pf_org_wifi';

    const TABLE_NAME = 'pf_org_wifi';

    public $timestamps = false;

    public static function getByMac($mac)
    {
        $row = DB::table(self::TABLE_NAME)
            ->select(['*'])
            ->where('mac', $mac)
            ->orderBy('update_time', 'desc')
            ->first();
        return $row;
    }

    public static function addMac($info)
    {
        $info = ArrayUtil::trimArray($info);
        if (empty($info)) {
            return false;
        }

        return DB::table(self::TABLE_NAME)->insertGetId($info);
    }

}
