<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/20
 * Time: 5:14 PM
 */

namespace App\Admin\Controllers;


use App\Admin\AdminController;
use App\Admin\Models\RepayModel;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Input;

class RepayController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function lists(Content $content)
    {
        $data = [
            'page' => Input::get('page', 1),
            'bill_date' => Input::get('bill_date', ''),
            'docking_business' => Input::get('docking_business', ''),
            'resource' => Input::get('resource', ''),
            'hasPayType' => Input::get('hasPayType', ''),
            'loan_product' => Input::get('loan_product', ''),
            'beginDate' => Input::get('beginDate', ''),
            'hid' => Input::get('hid', ''),
            'endDate' => Input::get('endDate', ''),
        ];
        if (!empty($data['bill_date'])) {
            $data['info'] = RepayModel::repayLists($data);
        } else {
            $data['info'] = [];
        }
        return $content->header('账期应收')
            ->description('账期应收详情')
            ->breadcrumb(
                ['text' => '账期应收', 'url' => 'repay/lists']
            )
            ->row(view('admin.repay.lists', $data));
    }

    public function overdue(Content $content)
    {
        return $content->header('逾期数据')
            ->description('逾期数据详情')
            ->breadcrumb(
                ['text' => '逾期数据', 'url' => 'repay/overdue']
            )
            ->row(view('admin.repay.overdue'));
    }
}
