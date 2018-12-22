<?php

namespace App\Models\ActiveRecord;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFZhifuOrder extends Model
{
    protected $table = 'pf_zhifu_order';
    const TABLE_NAME = 'pf_zhifu_order';

    public static function updateByOrderid($order_id, $data)
    {
        $data['utime'] = date('Y-m-d H:i:s');
        $n = DB::table(self::TABLE_NAME)->where('order_id', $order_id)->update($data);
        return $n;
    }

    public static function getByOrderid($order_id, $fields = ['*'])
    {
        $row = DB::table(self::TABLE_NAME)
            ->select($fields)
            ->where('order_id', $order_id)
            ->first();
        return $row;
    }

    public static function add($data)
    {
        $data['ctime'] = date('Y-m-d H:i:s');
        $data['utime'] = $data['ctime'];
        if (!isset($data['input'])) {
            $data['utime'] = '';
        }
        if (!isset($data['output'])) {
            $data['output'] = '';
        }
        $r = DB::table(self::TABLE_NAME)->insert($data);
        return $r;
    }

}
