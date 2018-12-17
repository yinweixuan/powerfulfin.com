<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 2:53 PM
 */

namespace App\Http\Controllers\App;


use App\Http\Controllers\Controller;

class AppController extends Controller
{
    public function __construct()
    {

        config("app.env");

        env("DB_CONNECTION");
    }
}
