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
use App\Components\AliyunOSSUtil;
use App\Components\ArrayUtil;
use App\Components\OutputUtil;
use App\Components\PFException;
use App\Models\ActiveRecord\ARPFAdminUsers;
use App\Models\ActiveRecord\ARPFAreas;
use App\Models\ActiveRecord\ARPFLoanLog;
use App\Models\Server\BU\BULoanApply;
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
}
