<?php

namespace App\Models\Fcs;

use App\Components\AliyunOSSUtil;
use App\Components\CheckUtil;
use App\Components\MapUtil;
use App\Models\ActiveRecord\ARPFAreas;
use App\Models\ActiveRecord\ARPFUsersLocation;

class FcsField {

    const MANAGER_CQ = 'VRO0004';
    const MANAGER_CD = 'VRO0005';
    const REGION_CQ = '重庆直销部';
    const REGION_CD = '成都直销部';


    public static function checkApplyParams($data) {
        //年龄：财会，学历：18-40，语言：18-45
        $age = CheckUtil::getAgeByIdCard($data['identity_number']);
        if ($age < 18 || $age > 45) {
            throw new \Exception('年龄超过资金方要求');
        }
        if (in_array($data['business_type'], array('财会', '学历'))) {
            if ($age > 40) {
                throw new \Exception('年龄超过资金方要求');
            }
        }
        if (in_array($data['business_type'], array('IT'))) {
            if ($age > 35) {
                throw new \Exception('年龄超过资金方要求');
            }
        }
        //最低学历要求：高中/中专
        if ($data['highest_education'] == '初中及以下') {
            throw new \Exception('学历低于资金方要求');
        }
        //月收入大于2000元
        if ($data['monthly_income'] < 2000) {
            throw new \Exception('收入低于资金方要求');
        }
        //不要学生
        if ($data['working_status'] == 2 || $data['profession'] == '学生') {
            throw new \Exception('在校学生无法在该资金方进行申请');
        }
        //身份证有效期：必须在有效期内；
        if ($data['end_date'] < date('Y-m-d H:i:s')) {
            throw new \Exception('身份证不在有效期内');
        }
        //人脸识别必须通过
        if ($data['face_recognition'] != STATUS_SUCCESS) {
            throw new \Exception('未通过人脸识别,请调整光线并露出全脸后重试');
        }
        //限制分校
        $school_arr = config('fcs.available_branch_school');
        foreach ($school_arr as $hid => $sid_arr) {
            if ($data['hid'] == $hid) {
                if (!in_array($data['sid'], $sid_arr)) {
                    throw new \Exception('非资金方支持机构');
                }
            }
        }
        //关闭的分校
        $closed_school = config('fcs.closed_branch_school');
        if (in_array($data['sid'], $closed_school)) {
            throw new \Exception('非资金方支持机构');
        }
        //判断永久被拒
        $refused_loan = FcsDB::getRefusedLoan($data['uid']);
        if ($refused_loan) {
            throw new \Exception('资金方不接受该用户申请');
        }
        //gps白名单
        $gps_whitelist = config('fcs.gps_whitelist');
        //现场授课的订单判断距离
        if ($data['class_online'] != 'SUCCESS') {
            $location = explode(',', $data['location']);
            $lng = $location[0];
            $lat = $location[1];
            //检查经纬度，没有的使用ip补全
            if ($lng < 1 || $lat < 1) {
                $position = MapUtil::getPosByIp($data['ip_address']);
                if ($position['lng'] && $position['lat']) {
                    $lng = round($position['lng'], 6);
                    $lat = round($position['lat'], 6);
                    $update = array();
                    $update['location'] = $lng . ',' . $lat;
                    $update['channel'] = 'IP';
                    $update['distance'] = $distance = MapUtil::getDistance($data['org_lng'], $data['org_lat'], $lng, $lat);
                    $data['distance'] = $update['distance'];
                    ARPFUsersLocation::_update($data['location_id'], $update);
                    pflog('fcs_gps', '补充gps：' . $data['id'] . PHP_EOL . print_r($update, true));
                }
            }
            //检测距离（米）
            $max_distance = config('fcs.max_distance');
            if ($data['distance'] > config('fcs.rule_distance')) {
                $log = array();
                $log['lid'] = $data['id'];
                $log['uid'] = $data['uid'];
                $log['oid'] = $data['oid'];
                $log['school_lat'] = $data['org_lat'];
                $log['school_lng'] = $data['org_lng'];
                $log['old_lat'] = $lat;
                $log['old_lng'] = $lng;
                $log['old_distance'] = $data['distance'];
                if ($data['distance'] <= $max_distance || in_array($data['oid'], $gps_whitelist)) {
                    $range = MapUtil::getNearPoint($data['org_lng'], $data['org_lat'], 0.4);
                    $new_lat = round(mt_rand($range['left-bottom']['lat'] * 1000000, $range['right-top']['lat'] * 1000000) / 1000000, 6);
                    $new_lng = round(mt_rand($range['left-bottom']['lng'] * 1000000, $range['right-top']['lng'] * 1000000) / 1000000, 6);
                    $data['distance'] = MapUtil::getDistance($data['org_lng'], $data['org_lat'], $new_lng, $new_lat);
                    $update = array();
                    $update['location'] = $new_lng . ',' . $new_lat;
                    $update['distance'] = $data['distance'];
                    ARPFUsersLocation::_update($data['location_id'], $update);
                    $log['new_lat'] = $new_lat;
                    $log['new_lng'] = $new_lng;
                    $log['new_distance'] = $data['distance'];
                    pflog('fcs_gps', print_r($log, true));
                } else {
                    $log['remark'] = '距离超过限制:' . $max_distance;
                    pflog('fcs_gps', print_r($log, true));
                    throw new \Exception('请在校区进行申请');
                }
            }
        }
        return $data;
    }

    /**
     * 进件接口字段转化
     */
    public static function getApplyParams($data) {
        /*
         * 缺少的字段
         * course_open_time
         */

        $lid = $data['id'];
        //日期类字段转格式
        $date_fields = array(
            'idcard_start', 'idcard_expire', 'birthday',
            'entrance_time', 'work_entry_time', 'created_at'
        );
        foreach ($date_fields as $v) {
            if (array_key_exists($v, $data) && $data[$v]) {
                if ($data[$v] == '0000') {
                    $data[$v] = null;
                } elseif ($data[$v] == '0000-00-00 00:00:00') {
                    $data[$v] = null;
                } elseif ($data[$v] == '0000-00-00') {
                    $data[$v] = null;
                } else {
                    $data[$v] = date('m/d/Y', strtotime($data[$v]));
                }
            }
        }
        //地区类的字段由编号转为文字
        $area_fields = array(
            'home_area', 'home_city', 'home_province',
            'work_area', 'work_city', 'work_province'
        );
        $area_ids = array();
        foreach ($area_fields as $v) {
            $area_ids[] = $data[$v];
        }
        $areas = ARPFAreas::getAreasByIds(array_unique(array_filter($area_ids)));
        foreach ($areas as $area) {
            if (strpos($area['name'], '-') !== 0) {
                $areaNames = explode('-', $area['name']);
                $area['name'] = array_pop($areaNames);
            }
            foreach ($area_fields as $f) {
                if ($data[$f] == $area['areaid']) {
                    $data[$f] = $area['name'];
                }
            }
        }
        //联系人关系
        $relations = array(
            '父母' => '父母',
            '配偶' => '配偶',
            '监护人' => '父母',
            '子女' => '亲属',
            '兄弟姐妹' => '亲属',
            '亲属' => '亲属',
            '同事' => '朋友',
            '朋友' => '朋友',
            '同学' => '朋友',
            '其他' => '朋友',
        );
        $relation_fields = array('contact_person_relation');
        foreach ($relation_fields as $rf) {
            if (array_key_exists($rf, $data)) {
                $data[$rf] = $relations[$data[$rf]];
            }
        }
        //云慧眼的照片优先
        if ($data['front_card']) {
            $data['idcard_pic'] = $data['front_card'];
        }
        if ($data['back_card']) {
            $data['idcard_pic_back'] = $data['back_card'];
        }
        $img_fields = array(
            'training_contract_first', 'photo_grid',
            'idcard_pic', 'idcard_pic_back', 'idcard_person_pic',
            'photo_living', 'school_pic', 'bank_account_pic',
            'pic_education'
        );
        foreach ($img_fields as $imgf) {
            if ($data[$imgf]) {
                $data[$imgf] = FcsFtp::uploadFromOss($lid, $data[$imgf]);
            }
        }
        //已取消的字段，但是因为是必填，所以补充白图。
        $data['training_contract_first'] = config('fcs.blank_pic');
        $data['school_pic'] = config('fcs.blank_pic');
        $data['idcard_person_pic'] = config('fcs.blank_pic');
        $data['bank_account_pic'] = config('fcs.blank_pic');
        //富登参数
        $fcs_params = array();
        //资金方信息
        if ($data['resource'] == RESOURCE_FCS_SC) {
            $fcs_params['manager'] = self::MANAGER_CD;
            $fcs_params['region'] = self::REGION_CD;
        } else {
            $fcs_params['manager'] = self::MANAGER_CQ;
            $fcs_params['region'] = self::REGION_CQ;
        }
        //固定字段
        $fcs_params['channel'] = config('fcs.channel');
        $fcs_params['custChannel'] = config('fcs.cust_channel');
        $fcs_params['appProduct'] = config('fcs.product');
        $fcs_params['intendProduct'] = config('fcs.product');
        //性别
//        $fcs_params['gender'] = $data['sex'] == 2 ? '女' : '男';
        //申请时地理位置
//        $fcs_params['applyPosition'] = round($data['lng'], 6) . ',' . round($data['lat'], 6);
        //机构地理位置
        $fcs_params['mechanismPosition'] = round($data['org_lng'], 6) . ',' . round($data['org_lat'], 6);
        //定位距离
//        $fcs_params['locationDistance'] = MapUtil::getDistance($data['org_lng'], $data['org_lat'], $data['lng'], $data['lat']);
        //人脸识别是否通过
        $fcs_params['passFlag'] = $data['result_auth'] == 2 ? '是' : '否';
        //月收入
//        $fcs_params['monthlyIncome'] = $data['monthly_income'] > 0 ? $data['monthly_income'] : 0;
        //工作状态
        $work_type = array(
            1 => '在职',
            2 => '学生',
            3 => '待业',
        );
        $fcs_params['YLZD03'] = $work_type[$data['working_status']] ? $work_type[$data['working_status']] : '--';
        //客户类型
        $cust_type = array(
            1 => '工薪人士', //在职
            2 => '学生', //学生
            3 => '其他', //待业
        );
        $fcs_params['custType'] = $cust_type[$data['working_status']] ? $cust_type[$data['working_status']] : '其他';
        //课程周期（月）
        $fcs_params['courseDuration'] = ceil($data['class_days'] / 30);
        //征信授权书
        $fcs_params['attachment6'] = $data['contract_credit'];
        //学历
        if (array_key_exists('degree', $data)) {
            $degree = array(
                '初中及以下' => '初中',
                '中专' => '中专/高中/技校',
                '高中' => '高中',
                '大专' => '大专',
                '本科' => '本科',
                '硕士' => '硕士及以上学历',
                '博士及以上' => '硕士及以上学历',
            );
            $fcs_params['degree'] = $degree[$data['degree']];
        }
        //婚姻状况
        if (array_key_exists('marriage_status', $data)) {
            $marriage = array(
                '1' => '已婚', //已婚有子女
                '2' => '已婚', //已婚无子女
                '3' => '未婚', //未婚
                '4' => '离婚', //离异
                '5' => '未婚', //其他
            );
            $fcs_params['maritalStatus'] = $marriage[$data['marriage_status']];
        }
        //住房状况
        if (array_key_exists('house_status', $data)) {
            $house_status = array(
                '1' => '宿舍', //宿舍
                '2' => '租房', //租房
                '3' => '与父母同住', //与父母同住
                '4' => '与其他亲属同住', //与其他亲属同住
                '5' => '自有住房', //自有住房
                '6' => '其他', //其他
            );
            $fcs_params['housingStatus'] = $house_status[$data['house_status']];
        }
        //还款账户-开户银行
        $fcs_params['hkOpenBank'] = $data['bank_name'];
        //贷款类型
        if ($data['loan_type'] == 2) {
            //贴息
            $fcs_params['KZType'] = '01';
        } elseif ($data['loan_type'] == 3) {
            //等额本息
            $fcs_params['KZType'] = '02';
        } elseif ($data['loan_type'] == 1) {
            //弹性
            $fcs_params['KZType'] = '03';
        }
        $fcs_params['loanPeriod'] = $data['rate_time_x'] . '+' . $data['rate_time_y'];
        //申请期限
        $fcs_params['applyPeriod'] = $data['rate_time_x'] + $data['rate_time_y'];
        //课程分类，优先用课程的分类
        $fcs_params['YLZD02'] = $data['class_type'] ? $data['class_type'] : $data['business_type'];
        //规范数据，防止富登出错
        $data['identity_number'] = strtoupper($data['identity_number']);
        $data['work_contact'] = str_replace(array('-', '_', '#', '+', ' ', '(', ')', '（', '）'), '', $data['work_phone']);
        //富登-课栈字段对应关系
        $map = array(
            'merchantAddrPhone' => 'contact_mobile', //机构地址电话
            'merchantAddress' => 'reg_address', //机构地址
            'coursePrice' => 'class_price', //课程价格
            'custName' => 'full_name', //姓名
            'cardNum' => 'identity_number', //身份证号
            'analysisCardNum' => 'id_card', //身份证解析结果-身份证号
            'cardStartDate' => 'start_date', //身份证有效起始
            'analysisStartDate' => 'idcard_start', //身份证解析结果-身份证有效起始
            'cardEndDate' => 'end_date', //身份证有效截止
            'analysisEndDate' => 'idcard_expired', //身份证解析结果-身份证有效截止
            'birth' => 'birthday', //出生日期
            'phone' => 'phone', //手机号码
            'applyAmount' => 'borrow_money', //申请金额
            'attachment1' => 'idcard_pic', //身份证正面照片
            'attachment2' => 'idcard_pic_back', //身份证反面照片
            'attachment3' => 'idcard_person_pic', //手持身份证照片
            'attachment4' => 'school_pic', //申请人与销售人员合影
            'attachment5' => 'bank_account_pic', //申请人银行卡照片
            'attachment7' => 'training_contract_first', //合同照片1
            'attachment8' => 'photo_living', //人脸识别采集照片
            'attachment9' => 'photo_grid', //公安网格照
//            'YLZD01' => 'training_contract_end', //合同照片2
//            'YLZD02' => 'busi_type', //课程分类，用机构的分类
            'YLZD04' => 'work_entry_time', //入职日期
            'YLZD05' => 'work_contact', //单位电话
            'YLZD06' => 'id', //课栈贷款唯一标识
//            'YLZD07' => '',//多张合同图
//            'YLZD08' => 'pic_education', //学历证明照片
            'emergCon1Relation' => 'contact_person_relation', //联系人-与主申请人关系1
            'emergCon1Name' => 'contact_person', //联系人-姓名1
            'emergCon1Phone' => 'contact_person_phone', //联系人-手机号码1
//            'emergCon2Relation' => 'contact2_relation', //联系人-与主申请人关系2
//            'emergCon2Name' => 'contact2', //联系人-姓名2
//            'emergCon2Phone' => 'contact2_phone', //联系人-手机号码2
            'professional' => 'profession', //职业
            'liveAddressProvience' => 'home_province', //现居住址-省
            'liveAddressCity' => 'home_city', //现居住址-市
            'liveAddressBorough' => 'home_area', //现居住址-区
            'liveDetailAddress' => 'home_address', //现居住址-详细地址
            'phoneId' => 'phoneid', //手机id
            'deviceFingerprint' => 'phoneid', //设备指纹
            'applyCourse' => 'class_name', //课程名称
            'courseStartDate' => 'course_open_time', //开课时间
            'courseLength' => 'class_days', //课程时长
            'merchantName' => 'org_full_name', //机构名称
            'tkAccountName' => 'org_full_name', //提款账户-开户名
            'tkOpenBank' => 'org_bank_name', //提款账户-开户银行
            'tkBankCard' => 'org_bank_account', //提款账户-银行卡卡号
            'hkAccountName' => 'full_name', //还款账户-开户名
            'hkBankCard' => 'bank_account', //还款账户-银行卡卡号
            'workUnit' => 'work_name', //单位名称
            'unitAddressProvience' => 'work_province', //单位地址-省
            'unitAddressCity' => 'work_city', //单位地址-市
            'unitAddressBorough' => 'work_area', //单位地址-区
            'unitDetailAddress' => 'work_address', //单位地址-详细地址
            'Position' => 'work_profession', //现任职务
            'school' => 'school_name', //学校名称
            'schoolAddress' => 'school_address', //学校地址
            'schoolPhone' => 'school_contact', //学校电话
            'enrolmentDate' => 'entrance_time', //入学时间
            'faceResult' => 'be_idcard', //人脸识别结果
            'WeChat' => 'wechat', //微信号
//            'JD' => 'jd_id', //京东号
//            'taobao' => 'tmall_id', //淘宝号
            'QQ' => 'qq', //qq号
            'email' => 'email', //邮箱
            'branchName' => 'org_name', //分校名称
//            'bossWorkLandline' => 'no_jrd', //是否为赊销
            'nickName' => 'username', //昵称
            'registerDate' => 'created_at', //注册时间
            'major' => 'school_major', //专业名称
            'schoolSystem' => 'education_system', //学制
            'gender' => 'gender',//性别
            'applyPosition' => 'location',//申请时地理位置
            'monthlyIncome' => 'monthly_income',//月收入
            'locationDistance' => 'distance',//定位距离
        );
        foreach ($map as $fcs_k => $kz_k) {
            if (!isset($fcs_params[$fcs_k])) {
                if (isset($data[$kz_k])) {
                    $fcs_params[$fcs_k] = $data[$kz_k];
                } else {
                    $fcs_params[$fcs_k] = '';
                }
            }
        }
        //第二联系人
        $fcs_params['emergCon2Relation'] = '朋友';
        $fcs_params['emergCon2Name'] = '大圣';
        $fcs_params['emergCon2Phone'] = '13800138000';
        //风险提示信息
        $fcs_params['otherRiskInfo'] = '无';
        //兼容富登（不必要的必填字段给默认值）
        $fcs_keys = array(
            'workUnit', 'unitAddressProvience', 'unitAddressCity',
            'unitAddressBorough', 'unitDetailAddress', 'Position'
        );
        foreach ($fcs_keys as $k) {
            if (!$fcs_params[$k]) {
                $fcs_params[$k] = '无';
            }
        }
        return $fcs_params;
    }

}
