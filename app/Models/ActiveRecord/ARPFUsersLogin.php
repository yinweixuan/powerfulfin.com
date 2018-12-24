<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:45 PM
 */

namespace App\Models\ActiveRecord;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFUsersLogin extends Model {

    protected $table = 'pf_users_login';

    const TABLE_NAME = 'pf_users_login';

    public static function add($data) {
        $data['create_time'] = date('Y-m-d H:i:s');
        $r = DB::table(self::TABLE_NAME)->insert($data);
        return $r;
    }

}
