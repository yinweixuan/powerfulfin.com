<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/28
 * Time: 3:54 PM
 */

namespace App\Admin\Controllers;


use App\Admin\AdminController;
use App\Admin\Models\LoanModel;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Input;

class LoanController extends AdminController
{
    public function index(Content $content)
    {
        $data = [
            'page' => Input::get('page', 1),
            'stuname' => Input::get('stuname', ''),
            'org_name' => Input::get('org_name', ''),
            'resource_loan_id' => Input::get('resource_loan_id', ''),
            'identity_number' => Input::get('identity_number', ''),
            'status' => Input::get('status', ''),
            'phone' => Input::get('phone', ''),
            'resource' => Input::get('resource', ''),
            'beginDate' => Input::get('beginDate', ''),
            'endDate' => Input::get('endDate', ''),
            'hid' => Input::get('hid', ''),
            'bank_code' => Input::get('bank_code', ''),
        ];
        $data['info'] = LoanModel::getLoanList($data);
        return $content->header('订单列表')
            ->description('订单信息列表')
            ->breadcrumb(
                ['text' => '订单管理', 'url' => '/admin/loan']
            )
            ->row(view('admin.loan.index', $data));
    }
}
