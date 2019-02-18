<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/14
 * Time: 2:54 PM
 */

namespace App\Admin\Controllers;


use App\Admin\AdminController;
use App\Admin\Models\AdminUsersModel;
use App\Admin\Models\LoanModel;
use App\Admin\Models\VerifyModel;
use App\Components\ArrayUtil;
use App\Components\OutputUtil;
use App\Components\PFException;
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
            'check_user' => Input::get('check_user', ''),
        ];
        $data['info'] = LoanModel::getLoanList($data);

        foreach ($data['info'] as $key => $datum) {
            $loanProducts = BULoanProduct::getLoanTypeByIds([$datum['loan_product']], true, true);
            $loanProduct = array_shift($loanProducts);
            $data['loan_product'][$datum['id']]['loan_product_name'] = $loanProduct['name'];
        }
        $checkUsers = AdminUsersModel::getCheckUsers();
        $data['check_users'] = $checkUsers;
        return $content->header('审核列表')
            ->description('审核订单信息列表')
            ->breadcrumb(
                ['text' => '审核列表', 'url' => '/admin/loan']
            )
            ->row(view('admin.verify.lists', $data));
    }

    /**
     * 抢单
     */
    public function collect()
    {
        $ids = Input::get('ids', '');
        $ids = ArrayUtil::trimArray(explode(',', $ids));
        try {
            $modifyIds = VerifyModel::collect($ids);
            OutputUtil::info(ERR_OK, '', array('ids' => implode(',', $modifyIds)));
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), ERR_SYS_PARAM);
        }
    }
}
