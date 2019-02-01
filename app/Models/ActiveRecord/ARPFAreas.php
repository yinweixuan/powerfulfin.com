<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:44 PM
 */

namespace App\Models\ActiveRecord;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFAreas extends Model
{
    protected $table = "pf_areas";
    const TABLE_NAME = 'pf_areas';

    protected $casts = [
        'areaid' => 'int'
    ];

    public $timestamps = false;

    public static function getAreas($parent_id = -1, $id = 0)
    {
        $query = DB::table(self::TABLE_NAME)->select('*');
        if ($id > 0 && is_numeric($id)) {
            $query->where('areaid', '=', $id);
        }

        if ($parent_id > -1) {
            $query->where('parentid', '=', $parent_id);
        }

        $info = $query->orderBy('name')->get()->toArray();
        return $info;
    }

    public static function getArea($areaId)
    {
        if (!is_numeric($areaId) || is_null($areaId)) {
            return [];
        }

        return DB::table(self::TABLE_NAME)->select('*')
            ->where('areaid', $areaId)
            ->first();
    }

    public static function getAreasByIds($ids)
    {
        if (is_numeric($ids)) {
            $ids = [$ids];
        } elseif (is_string($ids)) {
            $ids = explode(',', $ids);
        }
        $list = DB::table(self::TABLE_NAME)
            ->select()
            ->whereIn('areaid', $ids)
            ->get()->toArray();
        return $list;
    }
}
