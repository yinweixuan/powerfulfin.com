<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/27
 * Time: 5:27 PM
 */

namespace App\Admin\Models;


use App\Components\PFException;
use App\Models\ActiveRecord\ARPFAreas;
use App\Models\ActiveRecord\ARPFOrg;
use App\Models\ActiveRecord\ARPFOrgClass;
use App\Models\ActiveRecord\ARPFOrgHead;
use App\Models\ActiveRecord\ARPFOrgUsers;
use App\Models\Calc\CalcMoney;
use App\Models\Server\BU\BUBanks;
use App\Models\Server\BU\BULoanProduct;
use Illuminate\Support\Facades\DB;

class OrgModel
{
    /**
     * 机构列表
     * @param $data
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public static function getOrgList($data)
    {
        $query = DB::table(ARPFOrg::TABLE_NAME . ' as o')
            ->select(['o.*', 'oh.full_name'])
            ->leftJoin(ARPFOrgHead::TABLE_NAME . ' as oh', 'oh.hid', '=', 'o.hid');

        if (array_key_exists('oid', $data) && !empty($data['oid'])) {
            $query->where('o.id', $data['oid']);
        }

        if (array_key_exists('hid', $data) && !empty($data['hid'])) {
            $query->where('o.hid', $data['hid']);
        }

        if (array_key_exists('org_name', $data) && !empty($data['org_name'])) {
            $query->where('o.org_name', 'like', '%' . $data['org_name'] . '%');
        }

        if (array_key_exists('full_name', $data) && !empty($data['full_name'])) {
            $query->where('oh.full_name', 'like', '%' . $data['full_name'] . '%');
        }

        if (array_key_exists('status', $data) && !empty($data['status'])) {
            $query->where('o.status', $data['status']);
        }

        if (array_key_exists('can_loan', $data) && !empty($data['can_loan'])) {
            $query->where('o.can_loan', $data['can_loan']);
        }

        if (array_key_exists('org_province', $data) && !empty($data['org_province'])) {
            $query->where('o.org_province', $data['org_province']);
        }

        if (array_key_exists('org_city', $data) && !empty($data['org_city'])) {
            $query->where('o.org_city', $data['org_city']);
        }

        $query->orderByDesc('o.id');
        $info = $query->paginate(10, ['o.id'], 'page', $data['page'])
            ->appends($data);
        return $info;
    }

    public static function getOrgHeadList($data)
    {
        $query = DB::table(ARPFOrgHead::TABLE_NAME)->select('*');
        if (array_key_exists('hid', $data) && !empty($data['hid'])) {
            $query->where('hid', $data['hid']);
        }

        if (array_key_exists('full_name', $data) && !empty($data['full_name'])) {
            $query->where('full_name', 'like', '%' . $data['full_name'] . '%');
        }

        if (array_key_exists('org_bank_code', $data) && !empty($data['org_bank_code'])) {
            $query->where('org_bank_code', $data['org_bank_code']);
        }

        if (array_key_exists('org_bank_account', $data) && !empty($data['org_bank_account'])) {
            $query->where('org_bank_account', $data['org_bank_account']);
        }

        if (array_key_exists('business_license', $data) && !empty($data['business_license'])) {
            $query->where('business_license', 'like', '%' . $data['business_license'] . '%');
        }

        if (array_key_exists('business_type', $data) && !empty($data['business_type'])) {
            $query->where('business_type', $data['business_type']);
        }

        if (array_key_exists('legal_person', $data) && !empty($data['legal_person'])) {
            $query->where('legal_person', 'like', '%' . $data['legal_person'] . '%');
        }
        if (array_key_exists('legal_person_idcard', $data) && !empty($data['legal_person_idcard'])) {
            $query->where('legal_person_idcard', 'like', '%' . $data['legal_person_idcard'] . '%');
        }

        $query->orderByDesc('hid');
        $info = $query->paginate(10, ['hid'], 'page', $data['page'])
            ->appends($data);
        return $info;
    }

    public static function getClassList($data)
    {
        $query = DB::table(ARPFOrgClass::TABLE_NAME . ' as oc')
            ->select(['oc.*', 'o.org_name', 'oh.full_name'])
            ->leftJoin(ARPFOrg::TABLE_NAME . ' as o', 'o.id', '=', 'oc.oid')
            ->leftJoin(ARPFOrgHead::TABLE_NAME . ' as oh', 'oh.hid', '=', 'oc.hid');


        if (array_key_exists('cid', $data) && !empty($data['cid'])) {
            $query->where('oc.cid', $data['cid']);
        }

        if (array_key_exists('oid', $data) && !empty($data['oid'])) {
            $query->where('oc.oid', $data['oid']);
        }

        if (array_key_exists('hid', $data) && !empty($data['hid'])) {
            $query->where('oc.hid', $data['hid']);
        }

        if (array_key_exists('org_name', $data) && !empty($data['org_name'])) {
            $query->where('o.org_name', 'like', '%' . $data['org_name'] . '%');
        }

        if (array_key_exists('full_name', $data) && !empty($data['full_name'])) {
            $query->where('oh.full_name', 'like', '%' . $data['full_name'] . '%');
        }

        if (array_key_exists('class_name', $data) && !empty($data['class_name'])) {
            $query->where('oc.class_name', 'like', '%' . $data['class_name'] . '%');
        }

        if (array_key_exists('status', $data) && !empty($data['status'])) {
            $query->where('oc.status', $data['status']);
        }

        if (array_key_exists('class_online', $data) && !empty($data['class_online'])) {
            $query->where('oc.class_online', $data['class_online']);
        }

        if (array_key_exists('class_type', $data) && !empty($data['class_type'])) {
            $query->where('oc.class_type', $data['class_type']);
        }

        if (array_key_exists('class_days', $data) && !empty($data['class_days'])) {
            $query->where('oc.class_days', $data['class_days']);
        }

        if (array_key_exists('class_price_min', $data) && !empty($data['class_price_min'])) {
            $query->where('oc.class_price', '>=', $data['class_price_min']);
        }

        if (array_key_exists('class_price_max', $data) && !empty($data['class_price_max'])) {
            $query->where('oc.class_price', '<=', $data['class_price_max']);
        }

        $query->orderByDesc('oc.cid');
        $info = $query->paginate(10, ['oc.cid'], 'page', $data['page'])
            ->appends($data);
        return $info;
    }

    public static function getUsersList($data)
    {
        $query = DB::table(ARPFOrgUsers::TABLE_NAME . ' as ou')
            ->select('*')
            ->leftJoin(ARPFOrg::TABLE_NAME . ' as o', 'ou.org_id', '=', 'o.id');

        if (array_key_exists('org_uid', $data) && !empty($data['org_uid'])) {
            $query->where('ou.org_uid', $data['org_uid']);
        }
        if (array_key_exists('oid', $data) && !empty($data['oid'])) {
            $query->where('ou.org_id', $data['oid']);
        }
        if (array_key_exists('org_username', $data) && !empty($data['org_username'])) {
            $query->where('ou.org_username', $data['org_username']);
        }
        if (array_key_exists('org_name', $data) && !empty($data['org_name'])) {
            $query->where('o.org_name', 'like', '%' . $data['org_name'] . '%');
        }
        $query->orderByDesc('ou.org_uid');
        $info = $query->paginate(10, ['ou.org_uid'], 'page', $data['page'])
            ->appends($data);
        return $info;
    }


    public static function addHead($data)
    {
        $params = [
            'full_name',
            'business_license',
            'register_address',
            'legal_person',
            'legal_person_idcard',
            'org_bank_code',
            'org_bank_branch',
            'org_bank_account',
            'contact_name',
            'contact_phone',
            'business_type',
            'credit_line',
            'security_deposit',
            'loan_product',
            'docking_business',
            'docking_op',
        ];

        foreach ($params as $param) {
            if (!array_key_exists($param, $data) || empty($data[$param])) {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
        }

        $checkFullName = ARPFOrgHead::getInfoByFullName($data['full_name']);
        if (!empty($checkFullName)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT . ":商户已存在", ERR_SYS_PARAM);
        }

        $checkBusinessLicense = ARPFOrgHead::getInfoByBusinessLicense($data['business_license']);
        if (!empty($checkBusinessLicense)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT . ":营业执照号已存在", ERR_SYS_PARAM);
        }

        $loanProducts = implode(',', $data['loan_product']);
        $data['loan_product'] = $loanProducts;
        $data['org_bank_name'] = BUBanks::getBankName($data['org_bank_code']);
        $data['status'] = STATUS_SUCCESS;
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');
        $data['security_deposit'] = CalcMoney::fenToYuan($data['security_deposit']);
        $data['credit_line'] = CalcMoney::yuanToFen($data['credit_line']);

        try {
            $result = ARPFOrgHead::addHead($data);
            return $result;
        } catch (PFException $exception) {
            throw new PFException($exception->getMessage(), $exception->getCode());
        }
    }

    public static function addOrg($data)
    {
        $params = [
            'hid',
            'org_name',
            'short_name',
            'status',
            'can_loan',
            'org_province',
            'org_province_select',
            'org_city',
            'org_city_select',
            'org_area',
            'org_area_select',
            'org_address',
            'org_lng',
            'org_lat',
        ];
        foreach ($params as $param) {
            if (!array_key_exists($param, $data) || empty($data[$param])) {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
        }
        $checkOrg = ARPFOrg::getOrgByOrgName($data['org_name']);
        if (!empty($checkOrg)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT . ":分校名称已存在或重复", ERR_SYS_PARAM);
        }

        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');
        try {
            return ARPFOrg::addOrg($data);
        } catch (PFException $exception) {
            throw new PFException($exception->getMessage(), $exception->getCode());
        }
    }

    public static function addClass($data)
    {
        $params = [
            'class_name',
            'status',
            'class_online',
            'class_type',
            'class_price',
            'class_days'
        ];

        foreach ($params as $param) {
            if (!array_key_exists($param, $data) || empty($data[$param])) {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
        }

        if (empty($data['hid']) && empty($data['oid'])) {
            throw new PFException(ERR_SYS_PARAM_CONTENT . ":商户信息获取错误", ERR_SYS_PARAM);
        }

        if (empty($data['hid'])) {
            $org = ARPFOrg::getOrgById($data['oid']);
            $data['hid'] = $org['hid'];
        }

        try {
            $data['create_time'] = date('Y-m-d H:i:s');
            $data['update_time'] = date('Y-m-d H:i:s');

            return ARPFOrgClass::addClass($data);
        } catch (PFException $exception) {
            throw new PFException($exception->getMessage(), $exception->getCode());
        }
    }

    public static function getOrgHeadInfo($hid)
    {
        $orgHead = ARPFOrgHead::getInfo($hid);
        $orgs = ARPFOrg::getOrgByHid($hid);
        $loanProducts = [];
        $orgHead['loan_product'] = !empty($orgHead['loan_product']) ? explode(',', $orgHead['loan_product']) : [];
        if ($orgHead['loan_product']) {
            $loanProducts = BULoanProduct::getLoanTypeByIds($loanProducts);
        }

        foreach ($orgs as &$org) {
            $city = ARPFAreas::getArea($org['org_city']);
            $org['org_city'] = !empty($city) ? $city['joinname'] : '';
        }

        $info = [
            'org_head' => $orgHead,
            'orgs' => $orgs,
            'loanProducts' => $loanProducts
        ];
        return $info;
    }

    public static function updateHead($data)
    {
        $info = self::getOrgHeadInfo($data['hid']);
        if (empty($info['org_head'])) {
            throw new PFException(ERR_SYS_PARAM_CONTENT . ':未获取商户信息', ERR_SYS_PARAM);
        }

        $orgHead = $info['org_head'];
        $diff = [];
        $hid = $data['hid'];
        unset($data['hid']);
        $data['security_deposit'] = CalcMoney::fenToYuan($data['security_deposit']);
        $data['credit_line'] = CalcMoney::yuanToFen($data['credit_line']);
        if (!empty($data['loan_product'])) {
            $data['loan_product'] = implode(',', $data['loan_product']);
        }
        foreach ($data as $key => $val) {
            if (array_key_exists($key, $orgHead) && $val != $orgHead[$key]) {
                $diff[$key] = $val;
            }
        }

        if (empty($diff)) {
            return true;
        }

        if (array_key_exists('full_name', $diff)) {
            $checkFullName = ARPFOrgHead::getInfoByFullName($diff['full_name']);
            if (!empty($checkFullName)) {
                throw new PFException(ERR_SYS_PARAM_CONTENT . ":商户已存在", ERR_SYS_PARAM);
            }
        }


        if (array_key_exists('business_license', $diff)) {
            $checkBusinessLicense = ARPFOrgHead::getInfoByBusinessLicense($diff['business_license']);
            if (!empty($checkBusinessLicense)) {
                throw new PFException(ERR_SYS_PARAM_CONTENT . ":营业执照号已存在", ERR_SYS_PARAM);
            }
        }

        return ARPFOrgHead::updateInfo($hid, $diff);
    }


    public static function getOrgInfo($oid)
    {
        if (empty($oid)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        $org = DB::table(ARPFOrg::TABLE_NAME . ' as o')
            ->select(['o.*', 'oh.full_name'])
            ->leftJoin(ARPFOrgHead::TABLE_NAME . ' as oh', 'oh.hid', '=', 'o.hid')
            ->where('o.id', $oid)
            ->first();
        if (empty($org)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT . ":未获取到分校相关信息", ERR_SYS_PARAM);
        }
        $org['org_province_array'] = ARPFAreas::getArea($org['org_province']);
        $org['org_city_array'] = ARPFAreas::getArea($org['org_city']);
        $org['org_area_array'] = ARPFAreas::getArea($org['org_area']);
        return $org;
    }

    public static function updateOrg($data)
    {
        $org = self::getOrgInfo($data['oid']);
        if (empty($org)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT . ':未获取分校信息', ERR_SYS_PARAM);
        }

        $diff = [];
        unset($data['hid']);
        $oid = $data['oid'];
        unset($data['oid']);
        foreach ($data as $key => $val) {
            if (array_key_exists($key, $org) && $val != $org[$key]) {
                $diff[$key] = $val;
            }
        }
        if (empty($diff)) {
            return true;
        }

        return ARPFOrg::updateInfo($oid, $diff);
    }

    public static function getOrgClassInfo($cid)
    {
        if (empty($cid)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT . ':未获取课程信息', ERR_SYS_PARAM);
        }

        $class = DB::table(ARPFOrgClass::TABLE_NAME . ' as oc')
            ->select(['oc.*', 'o.org_name', 'oh.full_name'])
            ->leftJoin(ARPFOrg::TABLE_NAME . ' as o', 'o.id', '=', 'oc.oid')
            ->leftJoin(ARPFOrgHead::TABLE_NAME . ' as oh', 'oh.hid', '=', 'oc.hid')
            ->where('oc.cid', $cid)->first();

        if (empty($class)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT . ':未获取课程信息', ERR_SYS_PARAM);
        }
        return $class;
    }

    public static function updateOrgClass($data)
    {
        if (empty($data['cid'])) {
            throw new PFException(ERR_SYS_PARAM_CONTENT . ':未获取课程信息', ERR_SYS_PARAM);
        }
        $class = self::getOrgClassInfo($data['cid']);
        unset($data['cid']);
        foreach ($data as $key => $val) {
            if (array_key_exists($key, $class) && $val != $class[$key]) {
                $diff[$key] = $val;
            }
        }
        if (empty($diff)) {
            return true;
        }

        return ARPFOrgClass::updateInfo($class['cid'], $diff);
    }

    public static function addOrgUser($data)
    {
        $params = [
            'org_username',
            'org_password',
            'org_id',
            'status',
            'right'
        ];
        foreach ($params as $param) {
            if ($param == 'right') {
                if (!array_key_exists($param, $data)) {
                    throw new PFException(ERR_SYS_PARAM_CONTENT . ':' . $param . '=' . $data[$param], ERR_SYS_PARAM);
                }
            } else {
                if (!array_key_exists($param, $data) || empty($data[$param])) {
                    throw new PFException(ERR_SYS_PARAM_CONTENT . ':' . $param . '=' . $data[$param], ERR_SYS_PARAM);
                }
            }

        }

        $checkUserName = ARPFOrgUsers::getOrgUserInfoByOrgUserName($data['org_username']);
        if (!empty($checkUserName)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT . ":登录名已存在", ERR_SYS_PARAM);
        }
        $data['org_password'] = md5($data['org_password']);
        $data['create_time'] = date('Y-m-d H:i:s');
        $data['update_time'] = date('Y-m-d H:i:s');
        return ARPFOrgUsers::addOrgUser($data);
    }
}
