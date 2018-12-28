<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/27
 * Time: 5:27 PM
 */

namespace App\Admin\Models;


use App\Models\ActiveRecord\ARPFOrg;
use App\Models\ActiveRecord\ARPFOrgHead;
use Illuminate\Support\Facades\DB;

class OrgModel
{
    /**
     * 机构列表
     * @param $data
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getOrgList($data)
    {
        $query = DB::table(ARPFOrg::TABLE_NAME)->select('*');

        if (array_key_exists('oid', $data) && !empty($data['oid'])) {
            $query->where('id', $data['oid']);
        }

        if (array_key_exists('hid', $data) && !empty($data['hid'])) {
            $query->where('hid', $data['hid']);
        }

        if (array_key_exists('org_name', $data) && !empty($data['org_name'])) {
            $query->where('org_name', 'like', '%' . $data['org_name'] . '%');
        }

        if (array_key_exists('status', $data) && !empty($data['status'])) {
            $query->where('status', $data['status']);
        }

        if (array_key_exists('can_loan', $data) && !empty($data['can_loan'])) {
            $query->where('can_loan', $data['can_loan']);
        }

        if (array_key_exists('province', $data) && !empty($data['province'])) {
            $query->where('province', $data['province']);
        }

        if (array_key_exists('city', $data) && !empty($data['city'])) {
            $query->where('city', $data['city']);
        }

        $query->orderByDesc('id');
        $info = $query->paginate(10, ['id'], 'page', $data['page'])
            ->appends($data);
        return $info;
    }

    public static function getOrgHeadList($data)
    {
        $query = DB::table(ARPFOrgHead::TABLE_NAME)->select('*');
        if (array_key_exists('hid', $data) && !empty($data['hid'])) {
            $query->where('hid', $data['hid']);
        }

        if (array_key_exists('full_name', $data) && !empty($data['full_name'])) {
            $query->where('full_name', 'like', '%' . $data['full_name'] . '%');
        }

        $query->orderByDesc('hid');
        $info = $query->paginate(10, ['hid'], 'page', $data['page'])
            ->appends($data);
        return $info;

    }
}
