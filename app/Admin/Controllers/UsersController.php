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
use App\Models\ActiveRecord\ARPfUsers;
use Encore\Admin\Controllers\HasResourceActions;
use Encore\Admin\Grid;
use Encore\Admin\Layout\Content;
use Encore\Admin\Show;
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

        return $content
            ->header('用户列表')
            ->description('注册用户')
            ->breadcrumb(
                ['text' => '用户列表', 'url' => '/admin/users']
            )
            ->row(view('admin.user.index', ['info' => $info, 'uid' => $uid, 'phone' => $phone, 'full_name' => $full_name, 'page' => $page]));
    }

    /**
     * Show interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function show($id, Content $content)
    {
        return $content
            ->header('Detail')
            ->description('description')
            ->body($this->detail($id));
    }

    /**
     * Edit interface.
     *
     * @param mixed $id
     * @param Content $content
     * @return Content
     */
    public function edit($id, Content $content)
    {
        return $content
            ->header('Edit')
            ->description('description')
            ->body($this->form()->edit($id));
    }

    /**
     * Create interface.
     *
     * @param Content $content
     * @return Content
     */
    public function create(Content $content)
    {
        return $content
            ->header('创建')
            ->description('新增用户')
            ->body($this->form());
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        $grid = new Grid(new ARPfUsers());

        $grid->id('用户UID');
        $grid->username('用户名');
        $grid->phone('手机号');
        $grid->created_at('注册时间');
        $grid->updated_at('更新时间');

        return $grid;
    }

    /**
     * Make a show builder.
     *
     * @param mixed $id
     * @return Show
     */
    protected function detail($id)
    {
        $show = new Show(ARPfUsers::findOrFail($id));

        $show->id('用户UID');
        $show->username('用户名');
        $show->phone('手机号');
        $show->email_verified_at('Email verified at');
        $show->password('Password');
        $show->remember_token('Remember token');
        $show->created_at('Created at');
        $show->updated_at('Updated at');

        return $show;
    }

}
