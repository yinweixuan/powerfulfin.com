<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:46 PM
 */

namespace App\Models\ActiveRecord;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFOrg extends Model
{
    protected $table = 'pf_org';
    const TABLE_NAME = 'pf_org';

    public static function getOrgById($id)
    {
        if (is_null($id) || !is_numeric($id)) {
            return [];
        }

        return DB::table(self::TABLE_NAME)->select('*')
            ->where('id', $id)
            ->first();
    }
}
