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
use App\Models\Server\BU\BULoanApply;
use App\Models\Server\BU\BULoanProduct;
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

        foreach ($data['info'] as $key => $datum) {
            $loanProducts = BULoanProduct::getLoanTypeByIds([$datum['loan_product']], true, true);
            $loanProduct = array_shift($loanProducts);
            $data['loan_product'][$datum['id']]['loan_product_name'] = $loanProduct['name'];
        }
        return $content->header('订单列表')
            ->description('订单信息列表')
            ->breadcrumb(
                ['text' => '订单管理', 'url' => '/admin/loan']
            )
            ->row(view('admin.loan.index', $data));
    }

    public function info(Content $content)
    {
        $lid = Input::get('lid');
        $loan = BULoanApply::getDetailById($lid);
        return $content->header('订单详情')
            ->description($loan['real']['full_name'])
            ->breadcrumb(
                ['text' => '订单管理', 'url' => 'loan/index'],
                ['text' => '订单详情', 'url' => 'loan/info?lid=' . $lid]
            )
            ->row(view('admin.loan.info', $loan));
    }
}
