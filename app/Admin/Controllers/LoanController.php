<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/28
 * Time: 3:54 PM
 */

namespace App\Admin\Controllers;


use App\Admin\AdminController;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Input;

class LoanController extends AdminController
{
    public function index(Content $content)
    {
        $data = [
            'page' => Input::get('page'),
            'oid' => Input::get('oid'),
            'cid' => Input::get('cid'),
            'identity_number' => Input::get('identity_number'),
            'status' => Input::get('status'),
            'phone' => Input::get('phone'),
            'resource' => Input::get('resource'),
        ];
        return $content->header('订单列表')
            ->description('订单信息列表')
            ->breadcrumb(
                ['text' => '订单管理', 'url' => '/admin/loan']
            )
            ->row(view('admin.loan.index', $data));
    }
}
