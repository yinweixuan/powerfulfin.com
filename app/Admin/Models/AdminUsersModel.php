<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/18
 * Time: 3:01 PM
 */

namespace App\Admin\Models;


use App\Components\ArrayUtil;
use Illuminate\Support\Facades\DB;

class AdminUsersModel
{
    /**
     * 获取风控人员名单
     * @return array
     */
    public static function getCheckUsers()
    {
        $sql = "select au.id,au.name,au.username from pf_admin_users au left join pf_admin_role_users aru on aru.user_id=au.id where aru.role_id=5";
        $list = DB::select($sql);
        $list = ArrayUtil::addKeyToArray($list, 'id');
        return $list;
    }

    /**
     * 获取商务部人员名单
     * @return array
     */
    public static function getBusinessUsers()
    {
        $sql = "select au.id,au.name,au.username from pf_admin_users au left join pf_admin_role_users aru on aru.user_id=au.id where aru.role_id=2";
        $list = DB::select($sql);
        $list = ArrayUtil::addKeyToArray($list, 'id');
        return $list;
    }

    /**
     * 获取运营部人员
     * @return array
     */
    public static function getOpUsers()
    {
        $sql = "select au.id,au.name,au.username from pf_admin_users au left join pf_admin_role_users aru on aru.user_id=au.id where aru.role_id=4";
        $list = DB::select($sql);
        $list = ArrayUtil::addKeyToArray($list, 'id');
        return $list;
    }

}
