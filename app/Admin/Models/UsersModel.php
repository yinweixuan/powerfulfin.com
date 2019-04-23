<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/28
 * Time: 10:19 AM
 */

namespace App\Admin\Models;


use App\Models\ActiveRecord\ARPfUsers;
use App\Models\ActiveRecord\ARPFUsersBank;
use App\Models\ActiveRecord\ARPFUsersReal;
use Illuminate\Support\Facades\DB;

class UsersModel
{
    public static function getUsers($page = 1, $uid = '', $phone = '', $full_name = '')
    {
        $query = DB::table(ARPfUsers::TABLE_NAME . ' as u')->select('*')
            ->leftJoin(ARPFUsersReal::TABLE_NAME . ' as ur', 'ur.uid', '=', 'u.id');

        if (!empty($uid) && is_numeric($uid)) {
            $query->where('u.id', $uid);
        }

        if (!empty($phone)) {
            $query->where('u.phone', $phone);
        }

        if (!empty($full_name)) {
            $query->where('ur.full_name', 'like', '%' . $full_name . '%');
        }
        $query->orderByDesc('u.id');

        $info = $query->paginate(10, ['u.id'], 'page', $page)
            ->appends(['uid' => $uid, 'phone' => $phone, 'full_name' => $full_name]);
        return $info;
    }

    public static function getUsersReal($page = 1, $uid = '', $phone = '', $full_name = '', $identity_number = '')
    {
        $query = DB::table(ARPFUsersReal::TABLE_NAME . ' as ur')
            ->select(['ur.*', 'u.phone'])
            ->leftJoin(ARPfUsers::TABLE_NAME . ' as u', 'ur.uid', '=', 'u.id');

        if (!empty($uid) && is_numeric($uid)) {
            $query->where('ur.uid', $uid);
        }

        if (!empty($phone)) {
            $query->where('u.phone', $phone);
        }

        if (!empty($full_name)) {
            $query->where('ur.full_name', 'like', '%' . $full_name . '%');
        }

        if (!empty($identity_number)) {
            $query->where('ur.identity_number', $identity_number);
        }

        $query->orderByDesc('ur.uid');

        $info = $query->paginate(10, ['ur.uid'], 'page', $page)
            ->appends(['ur.uid' => $uid, 'phone' => $phone, 'full_name' => $full_name, 'identity_number' => $identity_number]);
        return $info;
    }

    public static function getUserBanks($data)
    {
        $query = DB::table(ARPFUsersBank::TABLE_NAME . ' as ub')
            ->select(['ub.*', 'ur.full_name'])
            ->leftJoin(ARPFUsersReal::TABLE_NAME . ' as ur', 'ur.uid', '=', 'ub.uid');

        if (array_key_exists('uid', $data) && !empty($data['uid']) && is_numeric($data['uid'])) {
            $query->where('ub.uid', $data['uid']);
        }

        if (array_key_exists('phone', $data) && !empty($data['phone'])) {
            $query->where('ub.phone', $data['phone']);
        }

        if (array_key_exists('bank_account', $data) && !empty($data['bank_account'])) {
            $query->where('ub.bank_account', $data['bank_account']);
        }

        if (array_key_exists('bank_code', $data) && !empty($data['bank_code'])) {
            $query->where('ub.bank_code', $data['bank_code']);
        }

        if (array_key_exists('full_name', $data) && !empty($data['full_name'])) {
            $query->where('ur.full_name', $data['full_name']);
        }

        $query->orderByDesc('ub.id');
        $query->orderByDesc('ub.uid');
        $info = $query->paginate(10, ['ub.id'], 'page', $data['page'])
            ->appends($data);
        return $info;

    }
}
