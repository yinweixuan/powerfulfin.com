<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 3:38 PM
 */

namespace App\Http\Controllers\App\V1;


use App\Http\Controllers\App\AppController;
use App\Models\ActiveRecord\ARPfUsers;

class IndexController extends AppController
{
    public function index()
    {
        var_dump(ARPfUsers::test());
    }
}
