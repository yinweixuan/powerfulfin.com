<?php

namespace App\Models\ActiveRecord;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFOrgWifi extends Model {

    protected $table = 'pf_org_wifi';

    const TABLE_NAME = 'pf_org_wifi';

    public static function getByMac($mac) {
        $row = DB::table(self::TABLE_NAME)
                ->select(['*'])
                ->where('mac', $mac)
                ->orderBy('update_time', 'desc')
                ->first();
        return $row;
    }

}
