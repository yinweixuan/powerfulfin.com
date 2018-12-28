<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/28
 * Time: 10:19 AM
 */

namespace App\Admin\Models;


use App\Models\ActiveRecord\ARPfUsers;
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

        $info = $query->paginate(2, ['u.id'], 'page', $page)
            ->appends(['uid' => $uid, 'phone' => $phone, 'full_name' => $full_name]);
        return $info;
    }

    public static function getUsersReal($page = 1, $uid = '', $phone = '', $full_name = '', $identity_number = '')
    {
        $query = DB::table(ARPFUsersReal::TABLE_NAME . ' as ur')
            ->select(['ur.*', 'u.phone'])
            ->leftJoin(ARPfUsers::TABLE_NAME . ' as u', 'ur.uid', '=', 'u.id');

        if (!empty($uid) && is_numeric($uid)) {
            $query->where('ur.id', $uid);
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

        $query->orderByDesc('u.id');

        $info = $query->paginate(2, ['u.id'], 'page', $page)
            ->appends(['uid' => $uid, 'phone' => $phone, 'full_name' => $full_name, 'identity_number' => $identity_number]);
        return $info;
    }
}
