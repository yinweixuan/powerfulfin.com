<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/27
 * Time: 5:15 PM
 */

namespace App\Admin\Controllers;


use App\Admin\AdminController;
use App\Models\ActiveRecord\ARPFOrg;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\DB;

class OrgController extends AdminController
{
    public function index(Content $content)
    {
        $info = DB::table(ARPFOrg::TABLE_NAME . ' as o')
            ->select('*')
            ->paginate(10);

        return $content
            ->header('机构列表')
            ->description('机构管理')
            ->breadcrumb(
                ['text' => '机构列表', 'url' => '/admin/org']
            )
            ->row(view('admin.org.index', ['info' => $info]));
    }
}
