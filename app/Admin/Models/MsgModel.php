<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/22
 * Time: 10:35 AM
 */

namespace App\Admin\Models;


use App\Components\CheckUtil;
use App\Models\ActiveRecord\ARPFSms;
use Illuminate\Support\Facades\DB;

class MsgModel
{
    public static function getSmsLists(array $data)
    {
        $query = DB::table(ARPFSms::TABLE_NAME)
            ->select('*');

        if (!empty($data['phone']) && CheckUtil::checkPhone($data['phone'])) {
            $query->where('phone', $data['phone']);
        }

        $query->orderByDesc('id');
        $info = $query->paginate(10, ['id'], 'page', $data['page'])
            ->appends($data);
        return $info;

    }
}
