<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 2:44 PM
 */

namespace App\Admin\Controllers;


use App\Admin\AdminController;
use App\Admin\Models\UsersModel;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Input;

class UsersController extends AdminController
{
    use HasResourceActions;

    /**
     * Index interface.
     *
     * @param Content $content
     * @return Content
     */
    public function index(Content $content)
    {
        $page = Input::get('page', 1);
        $uid = Input::get('uid', '');
        $phone = Input::get('phone', '');
        $full_name = Input::get('full_name', '');
        $info = UsersModel::getUsers($page, $uid, $phone, $full_name);
        admin_toastr('查询成功...', 'success');
        return $content
            ->header('用户列表')
            ->description('注册用户')
            ->breadcrumb(
                ['text' => '用户管理', 'url' => '/admin/users'],
                ['text' => '注册用户', 'url' => '/admin/users']
            )
            ->row(view('admin.user.index', ['info' => $info, 'uid' => $uid, 'phone' => $phone, 'full_name' => $full_name, 'page' => $page]));
    }

    public function real(Content $content)
    {
        $page = Input::get('page', 1);
        $uid = Input::get('uid', '');
        $phone = Input::get('phone', '');
        $full_name = Input::get('full_name', '');
        $identity_number = Input::get('identity_number', '');
        $info = UsersModel::getUsersReal($page, $uid, $phone, $full_name, $identity_number);
        admin_toastr('查询成功...', 'success');
        return $content->header('实名用户')
            ->description('用户信息')
            ->breadcrumb(
                ['text' => '用户管理', 'url' => '/admin/users'],
                ['text' => '实名用户', 'url' => '/admin/users/real']
            )
            ->row(view('admin.user.real', ['info' => $info, 'uid' => $uid, 'phone' => $phone, 'full_name' => $full_name, 'page' => $page, 'identity_number' => $identity_number]));
    }

    public function banks(Content $content)
    {

        $data = [
            'page' => Input::get('page', 1),
            'uid' => Input::get('uid', ''),
            'phone' => Input::get('phone', ''),
            'bank_account' => Input::get('bank_account', ''),
            'bank_code' => Input::get('bank_code', ''),
            'full_name' => Input::get('full_name', '')
        ];
        $info = UsersModel::getUserBanks($data);
        $data['info'] = $info;
        admin_toastr('查询成功...', 'success');
        return $content->header("银行卡")->description('银行卡列表')
            ->breadcrumb(
                ['text' => '用户管理', 'url' => '/admin/users'],
                ['text' => '银行卡', 'url' => '/admin/users/banks']
            )
            ->row(view('admin.user.banks', $data));
    }


}
