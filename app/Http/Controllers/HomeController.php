<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 11:42 AM
 */

namespace App\Http\Controllers;


class HomeController extends Controller
{
    public function index()
    {
        return view('web.home.index');
    }
}
