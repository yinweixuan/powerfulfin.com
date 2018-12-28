<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/27
 * Time: 5:15 PM
 */

namespace App\Admin\Controllers;


use App\Admin\AdminController;
use App\Admin\Models\OrgModel;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Input;

class OrgController extends AdminController
{
    public function index(Content $content)
    {
        $data = [
            'page' => Input::get('page', 1),
            'oid' => Input::get('oid', ''),
            'hid' => Input::get('hid', ''),
            'org_name' => Input::get('org_name', ''),
            'status' => Input::get('status', ''),
            'can_loan' => Input::get('can_loan', ''),
            'province' => Input::get('province', ''),
            'city' => Input::get('city', '')
        ];
        $info = OrgModel::getOrgList($data);
        $data['info'] = $info;
        return $content
            ->header('机构列表')
            ->description('机构管理')
            ->breadcrumb(
                ['text' => '机构列表', 'url' => '/admin/org']
            )
            ->row(view('admin.org.index', $data));
    }

    public function head(Content $content)
    {
        $data = [
            'page' => Input::get('page', 1),
            'hid' => Input::get('hid'),
            'full_name' => Input::get('full_name')
        ];
        $data['info'] = OrgModel::getOrgHeadList($data);
        return $content
            ->header('商户列表')
            ->description('商户管理')
            ->breadcrumb(
                ['text' => '商户列表', 'url' => '/admin/org/head']
            )
            ->row(view('admin.org.head', $data));
    }

    public function class(Content $content)
    {
        $data = [
            'page' => Input::get('page', 1),
            'id' => Input::get('id', ''),
            'oid' => Input::get('oid', ''),
            'class_name' => Input::get('class_name', ''),
            'status' => Input::get('status'),
        ];
        $data['info'] = OrgModel::getClassList($data);
        return $content->header('课程管理')
            ->description('课程信息列表')
            ->breadcrumb(
                ['text' => '课程管理', 'url' => '/admin/org/class']
            )
            ->row(view('admin.org.class', $data));
    }

    public function users(Content $content)
    {
        $data = [
            'page' => Input::get('page', 1),
            'org_uid' => Input::get('org_uid', ''),
            'oid' => Input::get('oid', ''),
            'org_username' => Input::get('org_username', ''),
            'org_name' => Input::get('org_name', '')
        ];
        $data['info'] = OrgModel::getUsersList($data);
        return $content->header('机构管理员')
            ->description('机构管理员信息列表')
            ->breadcrumb(
                ['text' => '机构管理员', 'url' => '/admin/org/class']
            )
            ->row(view('admin.org.users', $data));
    }
}
