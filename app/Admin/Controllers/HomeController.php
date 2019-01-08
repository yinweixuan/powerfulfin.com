<?php

namespace App\Admin\Controllers;

use App\Admin\AdminController;
use App\Admin\Models\HomeModel;
use Encore\Admin\Layout\Content;

class HomeController extends AdminController
{
    public function index(Content $content)
    {
        $info = HomeModel::getHomeData();
        return $content
            ->header('首页')
            ->row(view('admin.home.index', $info));
    }
}
