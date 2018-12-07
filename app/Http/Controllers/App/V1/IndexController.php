<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 3:38 PM
 */

namespace App\Http\Controllers\App\V1;


use App\Components\RedisUtil;
use App\Http\Controllers\App\AppController;
use App\Models\ActiveRecord\ARPfUsers;
use App\Models\DataBus;

class IndexController extends AppController
{
    public function index()
    {
        var_dump(DataBus::get('11'));
    }
}
