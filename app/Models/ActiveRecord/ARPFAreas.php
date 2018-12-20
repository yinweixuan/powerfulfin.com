<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:44 PM
 */

namespace App\Models\ActiveRecord;


use Illuminate\Support\Facades\DB;

class ARPFAreas
{
    const TABLE_NAME = 'pf_areas';

    public static function getAreas($parent_id = -1, $id = 0)
    {
        $info = DB::table(self::TABLE_NAME)->select('*');

        if ($id > 0 && is_numeric($id)) {
            $info->where('areaid', '=', $id);
        }

        if ($parent_id > -1) {
            $info->where('parentid', '=', $parent_id);
        }

        $info->orderBy('name')->get()->toArray();
        var_dump($info);
        return $info;
    }
}
