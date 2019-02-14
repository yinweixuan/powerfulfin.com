<?php

namespace App\Admin\Controllers;

use App\Admin\AdminController;
use App\Admin\Models\HomeModel;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Input;

class HomeController extends AdminController
{
    public function index(Content $content)
    {
        $info = HomeModel::getHomeData();
        return $content
            ->header('首页')
            ->row(view('admin.home.index', $info));
    }

    public function success(Content $content)
    {
        $info = [
            'message' => Input::get('message'),
            'url' => Input::get('url'),
        ];
        return $content
            ->header('首页')
            ->row(view('admin.home.success', $info));
    }

    public function err(Content $content)
    {
        $info = [
            'message' => Input::get('message'),
            'url' => Input::get('url'),
        ];

        return $content
            ->header('操作异常')
            ->row(view('admin.home.err', $info));
    }
}
