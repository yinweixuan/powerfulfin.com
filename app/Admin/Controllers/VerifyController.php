<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/14
 * Time: 2:54 PM
 */

namespace App\Admin\Controllers;


use App\Admin\AdminController;
use App\Admin\Models\LoanModel;
use App\Models\Server\BU\BULoanProduct;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Input;

class VerifyController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function lists(Content $content)
    {
        $data = [
            'page' => Input::get('page', 1),
            'stuname' => Input::get('stuname', ''),
            'org_name' => Input::get('org_name', ''),
            'identity_number' => Input::get('identity_number', ''),
            'status' => LOAN_2000_SCHOOL_CONFIRM,
            'phone' => Input::get('phone', ''),
            'resource' => Input::get('resource', ''),
            'hid' => Input::get('hid', ''),
            'bank_code' => Input::get('bank_code', ''),
        ];
        $data['info'] = LoanModel::getLoanList($data);

        foreach ($data['info'] as $key => $datum) {
            $loanProducts = BULoanProduct::getLoanTypeByIds([$datum['loan_product']], true, true);
            $loanProduct = array_shift($loanProducts);
            $data['loan_product'][$datum['id']]['loan_product_name'] = $loanProduct['name'];
        }
        return $content->header('审核列表')
            ->description('审核订单信息列表')
            ->breadcrumb(
                ['text' => '审核列表', 'url' => '/admin/loan']
            )
            ->row(view('admin.verify.lists', $data));
    }
}
