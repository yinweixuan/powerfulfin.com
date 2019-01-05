<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/27
 * Time: 5:27 PM
 */

namespace App\Admin\Models;


use App\Components\PFException;
use App\Models\ActiveRecord\ARPFOrg;
use App\Models\ActiveRecord\ARPFOrgClass;
use App\Models\ActiveRecord\ARPFOrgHead;
use App\Models\ActiveRecord\ARPFOrgUsers;
use App\Models\Calc\CalcMoney;
use App\Models\Server\BU\BUBanks;
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
        $query = DB::table(ARPFOrg::TABLE_NAME)->select('*');

        if (array_key_exists('oid', $data) && !empty($data['oid'])) {
            $query->where('id', $data['oid']);
        }

        if (array_key_exists('hid', $data) && !empty($data['hid'])) {
            $query->where('hid', $data['hid']);
        }

        if (array_key_exists('org_name', $data) && !empty($data['org_name'])) {
            $query->where('org_name', 'like', '%' . $data['org_name'] . '%');
        }

        if (array_key_exists('status', $data) && !empty($data['status'])) {
            $query->where('status', $data['status']);
        }

        if (array_key_exists('can_loan', $data) && !empty($data['can_loan'])) {
            $query->where('can_loan', $data['can_loan']);
        }

        if (array_key_exists('province', $data) && !empty($data['province'])) {
            $query->where('province', $data['province']);
        }

        if (array_key_exists('city', $data) && !empty($data['city'])) {
            $query->where('city', $data['city']);
        }

        $query->orderByDesc('id');
        $info = $query->paginate(10, ['id'], 'page', $data['page'])
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

        $query->orderByDesc('hid');
        $info = $query->paginate(10, ['hid'], 'page', $data['page'])
            ->appends($data);
        return $info;
    }

    public static function getClassList($data)
    {
        $query = DB::table(ARPFOrgClass::TABLE_NAME . ' as oc')
            ->select(['oc.*', 'o.org_name'])
            ->leftJoin(ARPFOrg::TABLE_NAME . ' as o', 'o.id', '=', 'oc.oid');


        if (array_key_exists('id', $data) && !empty($data['id'])) {
            $query->where('oc.cid', $data['id']);
        }

        if (array_key_exists('oid', $data) && !empty($data['oid'])) {
            $query->where('oc.oid', $data['oid']);
        }

        if (array_key_exists('class_name', $data) && !empty($data['class_name'])) {
            $query->where('oc.class_name', 'like', '%' . $data['class_name'] . '%');
        }

        if (array_key_exists('status', $data) && !empty($data['status'])) {
            $query->where('oc.status', $data['status']);
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

}
