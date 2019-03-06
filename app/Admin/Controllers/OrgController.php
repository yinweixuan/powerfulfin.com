<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/27
 * Time: 5:15 PM
 */

namespace App\Admin\Controllers;


use App\Admin\AdminController;
use App\Admin\Models\AdminUsersModel;
use App\Admin\Models\OrgModel;
use App\Components\AliyunOpenSearchUtil;
use App\Components\PFException;
use App\Models\ActiveRecord\ARPFAreas;
use App\Models\ActiveRecord\ARPFOrg;
use App\Models\ActiveRecord\ARPFOrgHead;
use App\Models\Server\BU\BULoanProduct;
use Encore\Admin\Exception\Handler;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Redis;

class OrgController extends AdminController
{
    public function index(Content $content)
    {
        $data = [
            'page' => Input::get('page', 1),
            'oid' => Input::get('oid', ''),
            'hid' => Input::get('hid', ''),
            'org_name' => Input::get('org_name', ''),
            'full_name' => Input::get('full_name', ''),
            'status' => Input::get('status', ''),
            'can_loan' => Input::get('can_loan', ''),
            'org_province' => Input::get('org_province', ''),
            'org_city' => Input::get('org_city', ''),
        ];
        $info = OrgModel::getOrgList($data);
        $data['info'] = $info;
        $key = "PF_PROVINCE";
        if (Redis::exists($key)) {
            $tmp = Redis::get($key);
            $province = json_decode($tmp, true);
        } else {
            $province = ARPFAreas::getAreas(0);
            Redis::set($key, json_encode($province), 86400);
        }
        $data['province'] = $province;
        admin_toastr('查询成功...', 'success');
        return $content
            ->header('分校列表')
            ->description('分校管理')
            ->breadcrumb(
                ['text' => '分校列表', 'url' => '/admin/org']
            )
            ->row(view('admin.org.index', $data));
    }

    public function head(Content $content)
    {
        $data = [
            'page' => Input::get('page', 1),
            'hid' => Input::get('hid'),
            'full_name' => Input::get('full_name'),
            'org_bank_account' => Input::get('org_bank_account'),
            'org_bank_code' => Input::get('org_bank_code'),
            'business_license' => Input::get('business_license'),
            'business_type' => Input::get('business_type'),
            'legal_person' => Input::get('legal_person'),
            'legal_person_idcard' => Input::get('legal_person_idcard'),
        ];
        $data['info'] = OrgModel::getOrgHeadList($data);
        $data['business'] = AdminUsersModel::getBusinessUsers();
        admin_toastr('查询成功...', 'success');
        return $content
            ->header('商户列表')
            ->description('商户管理')
            ->breadcrumb(
                ['text' => '商户列表', 'url' => '/admin/org/head']
            )
            ->row(view('admin.org.head', $data));
    }

    public function addhead(Content $content)
    {
        $data = $_POST;
        $loanProducts = BULoanProduct::getAllLoanType(null, false, STATUS_SUCCESS);
        $business = AdminUsersModel::getBusinessUsers();
        $op = AdminUsersModel::getOpUsers();
        $view = $content->header('新增商户')
            ->description('添加商户信息')
            ->breadcrumb(
                ['text' => '商户列表', 'url' => '/admin/org/head'],
                ['text' => '新增商户', 'url' => '/admin/org/addhead']
            )
            ->row(view('admin.org.addhead', ['loanProducts' => $loanProducts, 'business' => $business, 'op' => $op]));

        if (!empty($data)) {
            try {
                $result = OrgModel::addHead($data);
                if ($result) {
                    return Redirect::to("/admin/org/head")->send();
                }
            } catch (PFException $exception) {
                return $view->withError($exception->getMessage());
            }
        } else {
            return $view;
        }
    }

    public function headinfo(Content $content)
    {
        try {
            $hid = Input::get('hid');
            if (empty($hid)) {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
            $info = OrgModel::getOrgHeadInfo($hid);
            return $content->header('商户详情')
                ->description($info['org_head']['full_name'])
                ->breadcrumb(
                    ['text' => '商户列表', 'url' => '/admin/org/head'],
                    ['text' => '商户详情', 'url' => '/admin/org/headinfo?hid=' . $hid]
                )
                ->row(view('admin.org.headinfo', $info));
        } catch (PFException $exception) {
            return Handler::renderException($exception);
        }
    }

    public function class(Content $content)
    {
        $data = [
            'page' => Input::get('page', 1),
            'cid' => Input::get('cid', ''),
            'oid' => Input::get('oid', ''),
            'org_name' => Input::get('org_name', ''),
            'hid' => Input::get('hid', ''),
            'full_name' => Input::get('full_name', ''),
            'class_name' => Input::get('class_name', ''),
            'status' => Input::get('status', ''),
            'class_price_max' => Input::get('class_price_max', ''),
            'class_price_min' => Input::get('class_price_min', ''),
            'class_online' => Input::get('class_online', ''),
            'class_type' => Input::get('class_type', ''),
            'class_days' => Input::get('class_days', ''),
        ];
        $data['info'] = OrgModel::getClassList($data);
        admin_toastr('查询成功...', 'success');
        return $content->header('课程管理')
            ->description('课程信息列表')
            ->breadcrumb(
                ['text' => '课程管理', 'url' => '/admin/org/class']
            )
            ->row(view('admin.org.class', $data));
    }

    public function users(Content $content)
    {
        $data = [
            'page' => Input::get('page', 1),
            'org_uid' => Input::get('org_uid', ''),
            'oid' => Input::get('oid', ''),
            'org_username' => Input::get('org_username', ''),
            'org_name' => Input::get('org_name', '')
        ];
        $data['info'] = OrgModel::getUsersList($data);
        admin_toastr('查询成功...', 'success');
        return $content->header('机构管理员')
            ->description('机构管理员信息列表')
            ->breadcrumb(
                ['text' => '机构管理员', 'url' => '/admin/org/class']
            )
            ->row(view('admin.org.users', $data));
    }

    public function edithead(Content $content)
    {
        try {
            $hid = Input::get('hid');
            if (empty($hid)) {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
            $type = Input::get('type');
            $loanProducts = BULoanProduct::getAllLoanType(null, false, STATUS_SUCCESS);
            $business = AdminUsersModel::getBusinessUsers();
            $op = AdminUsersModel::getOpUsers();
            $view = $content->header('新增商户')
                ->description('添加商户信息')
                ->breadcrumb(
                    ['text' => '商户列表', 'url' => '/admin/org/head'],
                    ['text' => '更新商户', 'url' => '/admin/org/addhead']
                );
            if ($type == 'updatehead') {
                try {
                    $data = $_POST;
                    $result = OrgModel::updateHead($data);
                    if ($result) {
                        return Redirect::to("/admin/org/head")->send();
                    }
                } catch (PFException $exception) {
                    return $view->withError($exception->getMessage())->row(view('admin.org.edithead', ['loanProducts' => $loanProducts]));
                }
            } else {
                $info = OrgModel::getOrgHeadInfo($hid);
                return $view->row(view('admin.org.edithead', ['loanProducts' => $loanProducts, 'org_head' => $info['org_head'], 'loan_product' => $info['loanProducts'], 'business' => $business, 'op' => $op]));;
            }
        } catch (PFException $exception) {
            return Handler::renderException($exception);
        }
    }

    public function addorg(Content $content)
    {
        try {
            $hid = Input::get('hid');
            if (empty($hid)) {
                throw new PFException(ERR_SYS_PARAM_CONTENT . ":未正确获取商户ID", ERR_SYS_PARAM);
            }
            $orgHead = ARPFOrgHead::getInfo($hid);
            if (empty($orgHead)) {
                throw new PFException(ERR_SYS_PARAM_CONTENT . ":未正确获取商户信息", ERR_SYS_PARAM);
            }
            $type = Input::get('type', '');

            $key = "PF_PROVINCE";
            if (Redis::exists($key)) {
                $data = Redis::get($key);
                $province = json_decode($data, true);
            } else {
                $province = ARPFAreas::getAreas(0);
                Redis::set($key, json_encode($province), 86400);
            }

            $view = $content->header('新增分校')
                ->description('添加分校信息')
                ->breadcrumb(
                    ['text' => '商户列表', 'url' => '/admin/org/head'],
                    ['text' => '新增分校', 'url' => '/admin/org/addorg']
                )
                ->row(view('admin.org.addorg', ['orgHead' => $orgHead, 'province' => $province]));
            if ($type == 'addorg') {
                try {
                    $data = $_POST;
                    $result = OrgModel::addOrg($data);
                    if ($result) {
                        AliyunOpenSearchUtil::pushOrgData($result['id']);
                        return Redirect::to("/admin/org/index")->send();
                    }
                } catch (PFException $exception) {
                    return $view->withError($exception->getMessage());
                }
            } else {
                return $view;
            }
        } catch (PFException $exception) {
            return Handler::renderException($exception);
        }
    }

    public function editorg(Content $content)
    {
        try {
            $oid = Input::get('oid');
            if (empty($oid)) {
                throw new PFException(ERR_SYS_PARAM_CONTENT . ":未正确获取分校ID", ERR_SYS_PARAM);
            }
            $org = OrgModel::getOrgInfo($oid);

            $key = "PF_PROVINCE";
            if (Redis::exists($key)) {
                $data = Redis::get($key);
                $province = json_decode($data, true);
            } else {
                $province = ARPFAreas::getAreas(0);
                Redis::set($key, json_encode($province), 86400);
            }

            $view = $content->header('更新分校')
                ->description('更新分校信息')
                ->breadcrumb(
                    ['text' => '分校列表', 'url' => '/admin/org/head'],
                    ['text' => '更新分校', 'url' => '/admin/org/editorg?oid=' . $oid]
                )
                ->row(view('admin.org.editorg', ['province' => $province, 'org' => $org]));

            $type = Input::get('type', '');
            if ($type == 'updateorg') {
                try {
                    $data = $_POST;
                    $result = OrgModel::updateOrg($data);
                    if ($result) {
                        AliyunOpenSearchUtil::pushOrgData($oid);
                        return Redirect::to("/admin/org/index")->send();
                    }
                } catch (PFException $exception) {
                    return $view->withError($exception->getMessage());
                }
            } else {
                return $view;
            }
        } catch (PFException $exception) {
            return Handler::renderException($exception);
        }
    }

    public function addorgclass(Content $content)
    {
        try {
            $hid = Input::get('hid', 0);
            $oid = Input::get('oid', 0);
            if (empty($hid) && empty($oid)) {
                throw new PFException(ERR_SYS_PARAM_CONTENT . ":未正确获取商户ID", ERR_SYS_PARAM);
            }
            if (!empty($hid)) {
                $orgHead = ARPFOrgHead::getInfo($hid);
                if (empty($orgHead)) {
                    throw new PFException(ERR_SYS_PARAM_CONTENT . ":未正确获取商户信息", ERR_SYS_PARAM);
                }
                $org = [];
            }

            if (!empty($oid)) {
                $org = ARPFOrg::getOrgById($oid);
                if (empty($org)) {
                    throw new PFException(ERR_SYS_PARAM_CONTENT . ":未正确获取分校信息", ERR_SYS_PARAM);
                }
                if (!empty($hid) && $hid != $org['hid']) {
                    throw new PFException(ERR_SYS_PARAM_CONTENT . ":商户信息和分校信息不对等", ERR_SYS_PARAM);
                }
                $orgHead = ARPFOrgHead::getInfo($hid);
                if (empty($orgHead)) {
                    throw new PFException(ERR_SYS_PARAM_CONTENT . ":未正确获取商户信息", ERR_SYS_PARAM);
                }
            }
            $info = ['orgHead' => $orgHead, 'org' => $org, 'hid' => $hid, 'oid' => $oid];
            $type = Input::get('type', '');

            $view = $content->header('新增课程')
                ->description('添加课程信息')
                ->breadcrumb(
                    ['text' => '课程列表', 'url' => '/admin/org/class'],
                    ['text' => '新增课程', 'url' => '/admin/org/addclass']
                )
                ->row(view('admin.org.addclass', $info));
            if ($type == 'addclass') {
                try {
                    $data = $_POST;
                    $result = OrgModel::addClass($data);
                    if ($result) {
                        return Redirect::to("/admin/org/class")->send();
                    }
                } catch (PFException $exception) {
                    return $view->withError($exception->getMessage());
                }
            } else {
                return $view;
            }
        } catch (PFException $exception) {
            return Handler::renderException($exception);
        }
    }

    public function editclass(Content $content)
    {
        try {
            $cid = Input::get('cid');
            if (empty($cid)) {
                throw new PFException(ERR_SYS_PARAM_CONTENT . ":未正确获取课程ID", ERR_SYS_PARAM);
            }
            $type = Input::get('type');
            $class = OrgModel::getOrgClassInfo($cid);
            $view = $content->header('更新课程')
                ->description('更新课程信息')
                ->breadcrumb(
                    ['text' => '课程列表', 'url' => '/admin/org/head'],
                    ['text' => '更新课程', 'url' => '/admin/org/editclass?cid=' . $cid]
                )
                ->row(view('admin.org.editclass', ['class' => $class]));
            if ($type == 'updateclass') {
                try {
                    $data = $_POST;
                    $result = OrgModel::updateOrgClass($data);
                    if ($result) {
                        return Redirect::to("/admin/org/class")->send();
                    }
                } catch (PFException $exception) {
                    return $view->withError($exception->getMessage());
                }
            } else {
                return $view;
            }
        } catch (PFException $exception) {
            return Handler::renderException($exception);
        }
    }

    public function adduser(Content $content)
    {
        try {
            $type = Input::get('type');
            $view = $content->header('新增机构管理员')
                ->description('添加机构管理员信息')
                ->breadcrumb(
                    ['text' => '机构管理员列表', 'url' => '/admin/org/user'],
                    ['text' => '新增管理员', 'url' => '/admin/org/adduser']
                )
                ->row(view('admin.org.adduser'));
            if ($type == 'adduser') {
                try {
                    $data = $_POST;
                    $result = OrgModel::addOrgUser($data);
                    if ($result) {
                        return Redirect::to("/admin/org/users")->send();
                    }
                } catch (PFException $exception) {
                    return $view->withError($exception->getMessage());
                }
            } else {
                return $view;
            }
        } catch (PFException $exception) {
            return Handler::renderException($exception);
        }
    }
}
