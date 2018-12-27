<?php

namespace App\Admin\Controllers;

use App\Admin\AdminController;
use Encore\Admin\Layout\Content;

class HomeController extends AdminController
{
    public function index(Content $content)
    {
        return $content
            ->header('首页')
            ->row(view('admin.home.index'));
    }
}
