<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/19
 * Time: 3:16 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\ArrayUtil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFAdminUsers extends Model
{
    protected $table = 'pf_admin_users';
    const TABLE_NAME = 'pf_admin_users';

    /**
     * 根据ids批量获取
     * @param $ids
     * @return array
     */
    public static function getByIds($ids)
    {
        if (empty($ids) || !is_array($ids)) {
            return [];
        }

        return DB::table(self::TABLE_NAME)->select('*')
            ->where('id', $ids)
            ->get()->toArray();
    }
}
