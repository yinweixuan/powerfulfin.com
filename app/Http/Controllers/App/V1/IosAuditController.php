<?php
/**
 * Created by PhpStorm.
 * User: xuyang
 * Date: 2019/1/9
 * Time: 15:10
 */

namespace App\Http\Controllers\App\V1;


use App\Components\OutputUtil;
use App\Models\ActiveRecord\ARPFIosAudit;
use App\Models\DataBus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class IosAuditController {

    public function apply() {
        $name = Input::get('name');
        $phone = Input::get('phone');
        $org_name = Input::get('org_name');
        $class_name = Input::get('class_name');
        $uid = DataBus::getUid();
        try {
            if (!$uid) {
                throw new \Exception('请先登录');
            }
            if (!$name) {
                throw new \Exception('请填写姓名');
            }
            if (!$phone) {
                throw new \Exception('请填写手机号码');
            }
            if (!$class_name) {
                throw new \Exception('请选择课程');
            }
            $data = [];
            $data['name'] = $name;
            $data['phone'] = $phone;
            $data['org_name'] = $org_name;
            $data['class_name'] = $class_name;
            $data['uid'] = $uid;
            ARPFIosAudit::add($data);
            OutputUtil::out();
        } catch (\Exception $ex) {
            OutputUtil::out($ex);
        }
    }

    public function classList(){
        $oid = Input::get('oid');
        $list = ARPFIosAudit::getCourseList($oid);
        OutputUtil::out($list);
    }

    public function applyList(){
        $uid = DataBus::getUid();
        $list = ARPFIosAudit::getList($uid);
        OutputUtil::out($list);
    }

}