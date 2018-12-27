<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/27
 * Time: 5:27 PM
 */

namespace App\Admin\Models;


use App\Models\ActiveRecord\ARPFOrg;
use App\Models\ActiveRecord\ARPfUsers;
use Illuminate\Support\Facades\DB;

class OrgModel
{
    public static function getOrgList()
    {
        $orgs = DB::table(ARPfUsers::TABLE_NAME)->paginate(2);
        var_dump($orgs);exit;
        return $orgs;
    }
}
