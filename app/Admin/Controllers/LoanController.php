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
use App\Components\AliyunOSSUtil;
use App\Components\OutputUtil;
use App\Models\ActiveRecord\ARPFAdminUsers;
use App\Models\ActiveRecord\ARPFAreas;
use App\Models\ActiveRecord\ARPFLoanLog;
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

        $loan['phonebook'] = OutputUtil::json_decode($loan['phonebook']['phonebook']);

        $loanLog = ARPFLoanLog::getLoanLogByLid($lid);
        foreach ($loanLog as &$log) {
            if (!empty($log['uid_op'])) {
                $admins = ARPFAdminUsers::getByIds([$log['uid_op']]);
                if (!empty($admins)) {
                    $admin = array_shift($admins);
                    $log['username'] = $admin['name'];
                }
            } else {
                $log['username'] = '';
            }
        }
        $loan['loan_log'] = $loanLog;
        $pic = ['scene_pic', 'person_pic', 'train_contract_pic', 'train_statement_pic'];
        $picArr = [];
        for ($i = 0; $i < count($pic); $i++) {
            if (!empty($loan['base'][$pic[$i]])) {
                $tmp = OutputUtil::json_decode($loan['base'][$pic[$i]]);
                foreach ($tmp as $item) {
                    $picArr[$pic[$i]][] = AliyunOSSUtil::getAccessUrl(AliyunOSSUtil::getLoanBucket(), $item);
                }

            }
        }

        if (!empty($loan['real']['idcard_information_pic'])) {
            $picArr['idcard_information_pic'] = AliyunOSSUtil::getAccessUrl(AliyunOSSUtil::getLoanBucket(), $loan['real']['idcard_information_pic']);
        } else {
            $picArr['idcard_information_pic'] = '';
        }

        if (!empty($loan['real']['idcard_national_pic'])) {
            $picArr['idcard_national_pic'] = AliyunOSSUtil::getAccessUrl(AliyunOSSUtil::getLoanBucket(), $loan['real']['idcard_national_pic']);
        } else {
            $picArr['idcard_national_pic'] = '';
        }

        if (!empty($loan['work']['edu_pic'])) {
            $picArr['edu_pic'] = AliyunOSSUtil::getAccessUrl(AliyunOSSUtil::getLoanBucket(), $loan['work']['edu_pic']);
        } else {
            $picArr['edu_pic'] = '';
        }

        $loan['pic'] = $picArr;
        if (!empty($loan['contact']['home_area'])) {
            $home = ARPFAreas::getArea($loan['contact']['home_area']);
            $loan['contact']['home_address'] = str_replace(',', '', $home['joinname']) . $loan['contact']['home_address'];
        }
        if (!empty($loan['work']['work_area'])) {
            $work = ARPFAreas::getArea($loan['work']['work_area']);
            $loan['work']['work_address'] = str_replace(',', '', $work['joinname']) . $loan['work']['work_address'];
        }
        if (!empty($loan['work']['school_area'])) {
            $school = ARPFAreas::getArea($loan['work']['school_area']);
            $loan['work']['school_area'] = str_replace(',', '', $school['joinname']) . $loan['work']['school_area'];
        }

        return $content->header('订单详情')
            ->description($loan['real']['full_name'])
            ->breadcrumb(
                ['text' => '订单管理', 'url' => 'loan/index'],
                ['text' => '订单详情', 'url' => 'loan/info?lid=' . $lid]
            )
            ->row(view('admin.loan.info', $loan));
    }

    public function bill(Content $content)
    {
        $data = [
            'lid' => Input::get('lid'),
            'uid' => Input::get('uid'),
        ];
        $loanBills = LoanModel::getLoanBill($data);
        $data['info'] = $loanBills;
        return $content->header('还款账期')
            ->description('还款账期详情')
            ->breadcrumb(
                ['text' => '订单管理', 'url' => 'loan/index'],
                ['text' => '还款账期', 'url' => '']
            )
            ->row(view('admin.loan.bill', $data));
    }
}
