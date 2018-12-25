<?php

namespace App\Models\Fcs;

use App\Models\Fcs\FcsCommon;
use App\Models\Fcs\FcsContract;
use App\Models\Fcs\FcsField;
use App\Models\Fcs\FcsFtp;
use App\Models\Fcs\FcsHttp;
use App\Models\Fcs\FcsLoan;
use App\Models\Fcs\FcsQueue;
use App\Models\Fcs\FcsSoap;

class FcsField {

    const MANAGER_CQ = 'VRO0004';
    const MANAGER_CD = 'VRO0005';
    const REGION_CQ = '重庆直销部';
    const REGION_CD = '成都直销部';
    const PRODUCT = '课栈教育分期合作方案';
    const CUST_CHANNEL = '北京弟傲思时代信息技术有限公司';

    /**
     * 进件接口字段转化
     */
    public static function getApplyParams($data) {
        //日期类字段转格式
        $date_fields = array(
            'idcard_start', 'idcard_expire', 'birthday',
            'entrance_time', 'course_open_time', 'entry_date', 'add_time'
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
        $areas = ARArea::getAreasByIds(implode(',', array_unique(array_filter($area_ids))));
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
            '母子' => '父母',
            '配偶' => '配偶',
            '监护人' => '父母',
            '子女' => '亲属',
            '兄弟姐妹' => '亲属',
            '亲属' => '亲属',
            '老师' => '朋友',
            '同事' => '朋友',
            '朋友' => '朋友',
            '同学' => '朋友',
            '其他' => '朋友',
        );
        $relation_fields = array('contact1_relation', 'contact2_relation');
        foreach ($relation_fields as $rf) {
            if (array_key_exists($rf, $data)) {
                $data[$rf] = $relations[$data[$rf]];
            }
        }
        //文件上传列表
        $file_list = array();
        //添加征信授权书
        $file_list[] = $data['contract_credit'];
        //添加单张图片
//        $training_contract = json_decode($data['training_contract'], true);
//        $data['training_contract_first'] = $training_contract[0];
//        $data['training_contract_end'] = $training_contract[1];
        //已取消的字段，但是因为是必填，所以补充白图。
        $data['training_contract_first'] = 'http://simg.kezhanwang.cn/201805/15/blank.jpg';
        $data['school_pic'] = 'http://simg.kezhanwang.cn/201805/15/blank.jpg';
        $data['idcard_person_pic'] = 'http://simg.kezhanwang.cn/201805/15/blank.jpg';
        $data['bank_account_pic'] = 'http://simg.kezhanwang.cn/201805/15/blank.jpg';
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
                $full_local_path = PATH_UPLOAD_SECRET . UrlUtil::urlForLinuxPath($data[$imgf]);
                if (is_file($full_local_path)) {
                    $file_list[] = $full_local_path;
                    $data[$imgf] = FcsFtp::getFile($data['id'], $full_local_path);
                } else {
                    //不存在的图设置为空
                    $data[$imgf] = '';
                }
            }
        }
        //添加多张合同图
//        $training_contract_ftp = array();
//        foreach ($training_contract as $cimg) {
//            $full_local_path = PATH_UPLOAD_SECRET . UrlUtil::urlForLinuxPath($cimg);
//            if (is_file($full_local_path)) {
//                $file_list[] = $full_local_path;
//                $training_contract_ftp[] = FcsFtp::getFile($data['id'], $full_local_path);
//            }
//        }
        //合同，单张图，多张图，一起上传
        FcsFtp::upload($data['id'], array_unique($file_list));
        //富登参数
        $fcs_params = array();
        //资金方信息
        if ($data['resource'] == ARPayLoanType::RESOURCE_FCS_SC) {
            $fcs_params['manager'] = self::MANAGER_CD;
            $fcs_params['region'] = self::REGION_CD;
        } else {
            $fcs_params['manager'] = self::MANAGER_CQ;
            $fcs_params['region'] = self::REGION_CQ;
        }
        //固定字段
        $fcs_params['channel'] = FcsLoan::CHANNEL;
        $fcs_params['custChannel'] = self::CUST_CHANNEL;
        $fcs_params['appProduct'] = self::PRODUCT;
        $fcs_params['intendProduct'] = self::PRODUCT;
        //性别
        $fcs_params['gender'] = $data['sex'] == 2 ? '女' : '男';
        //gps白名单
        $gps_whitelist = array(
            11192, 11104, 11251, 11166, 11101, 11088, 11161, 11066, 300424,
            11090, 11164, 11191, 11252, 11262, 11095, 11195, 11196, 11060,
            300278, 12415, 13106, 11198, 300029, 11163, 11058, 11160, 11096,
            11056, 300081, 300027, 300265, 11097, 11055, 11057, 11265, 11059,
            300547, 300026, 11162, 11100, 300404, 300277, 11266, 11256, 300646,
            11207, 11098, 11236, 13079, 11133, 11139, 11240, 300647, 300213,
            11074, 300085, 11082, 11151, 300409, 11146, 11167, 11089, 11193,
            300022, 11112, 11246, 11203, 11080, 11099, 11084, 11185, 11201,
            11073, 11134, 11092, 300035, 11206, 11234, 11241, 11255, 11168,
            11105, 11086, 11063, 11085, 300030, 300446, 11259, 11094, 11260,
            300648, 300441, 11194, 11202, 11065, 11070, 11071, 11271, 11204,
            11108, 11235, 11054, 11093, 11199, 300222, 11268, 11153, 11143,
            11102, 300574, 300202, 11132, 300033, 11214, 11087, 12780, 300032,
            11249, 11091, 11264, 11225, 11075, 11243, 300425, 11141, 11233,
            11062, 11205, 300378, 11064, 300384, 11182, 11178, 300369, 300411,
            11226, 11149, 12858, 11140, 11254, 11144, 11263, 11209, 11142, 11107,
            11137, 11117, 11109, 300418, 11269, 11175, 11145, 11217, 11267, 300018,
            11111, 11169, 11152, 11067, 300080, 11258, 300037, 11197, 300025,
            11113, 300298, 300031, 300028, 11222, 11110, 11061, 11216, 11221,
            12450, 11238, 11272, 11115, 11173, 11077, 300268, 300234, 11106,
            11253, 11227, 11076, 11118, 11190, 11116, 11237, 11078, 11125, 11239,
            11069, 11261, 11126, 11079, 300382, 11119, 11218, 11184, 11208,
            11179, 11083, 11131, 13069, 11174, 11219, 11257, 300241, 11148,
        );
        //现场授课的订单判断距离
        if ($data['teach_type'] == 1) {
            //检查经纬度，没有的使用ip补全
            if (!$data['lng'] || !$data['lat'] || $data['lng'] == '5e-324' || $data['lat'] == '5e-324') {
                $position = MapUtil::getPosByIp($data['ip']);
                if ($position['lng'] && $position['lat']) {
                    $data['lng'] = round($position['lng'], 6);
                    $data['lat'] = round($position['lat'], 6);
                    $stu_update = array();
                    $stu_update['lat'] = $data['lat'];
                    $stu_update['lng'] = $data['lng'];
                    ARStuAddition::_update($data['id'], $stu_update);
                    \Yii::log('补充gps：' . $data['id'] . PHP_EOL . print_r($stu_update, true),  'fcs_gps.op');
                }
            }
            //检测距离（米）
            $max_distance = 20000;
            $distance = Nearby::getDistance($data['s_lng'], $data['s_lat'], $data['lng'], $data['lat']);
            if ($distance > 500) {
                $log = array();
                $log['lid'] = $data['id'];
                $log['uid'] = $data['uid'];
                $log['sid'] = $data['sid'];
                $log['school_lat'] = $data['s_lat'];
                $log['school_lng'] = $data['s_lng'];
                $log['old_lat'] = $data['lat'];
                $log['old_lng'] = $data['lng'];
                $log['old_distance'] = $distance;
                if ($distance <= $max_distance || in_array($data['sid'], $gps_whitelist)) {
                    $range = Nearby::getNearPoint($data['s_lng'], $data['s_lat'], 0.4);
                    $new_lat = round(mt_rand($range['left-bottom']['lat'] * 1000000, $range['right-top']['lat'] * 1000000) / 1000000, 6);
                    $new_lng = round(mt_rand($range['left-bottom']['lng'] * 1000000, $range['right-top']['lng'] * 1000000) / 1000000, 6);
                    $stu_update = array();
                    $stu_update['lat'] = $new_lat;
                    $stu_update['lng'] = $new_lng;
                    ARStuAddition::_update($data['id'], $stu_update);
                    $data['lng'] = $new_lng;
                    $data['lat'] = $new_lat;
                    $distance = Nearby::getDistance($data['s_lng'], $data['s_lat'], $new_lng, $new_lat);
                    $log['new_lat'] = $new_lat;
                    $log['new_lng'] = $new_lng;
                    $log['new_distance'] = $distance;
                    \Yii::log(print_r($log, true),  'fcs_gps.op');
                } else {
                    $log['remark'] = '距离超过限制:' . $max_distance;
                    \Yii::log(print_r($log, true),  'fcs_gps.op');
                    throw new Exception('请在校区进行申请', FcsLoan::CODE_KZ_REFUSE);
                }
            }
        }
        //申请时地理位置
        $fcs_params['applyPosition'] = round($data['lng'], 6) . ',' . round($data['lat'], 6);
        //机构地理位置
        $fcs_params['mechanismPosition'] = round($data['s_lng'], 6) . ',' . round($data['s_lat'], 6);
        //定位距离
        $fcs_params['locationDistance'] = $distance;
        //人脸识别是否通过
        $fcs_params['passFlag'] = $data['result_auth'] == 2 ? '是' : '否';
        //月收入
        $fcs_params['monthlyIncome'] = $data['monthly_income'] > 0 ? $data['monthly_income'] / 100 : 0;
        //工作状态
        $work_type = array(
            1 => '在职',
            2 => '学生',
            3 => '待业',
        );
        $fcs_params['YLZD03'] = $work_type[$data['work_type']] ? $work_type[$data['work_type']] : '--';
        //客户类型
        $cust_type = array(
            1 => '工薪人士', //在职
            2 => '学生', //学生
            3 => '其他', //待业
        );
        $fcs_params['custType'] = $cust_type[$data['work_type']] ? $cust_type[$data['work_type']] : '其他';
        //课程周期（月）（对方未确认）
        if ($data['course_period_property'] == '天') {
            $fcs_params['courseDuration'] = ceil($data['course_period'] / 22);
        } elseif ($data['course_period_property'] == '课时') {
            $fcs_params['courseDuration'] = ceil($data['course_period'] / 88);
        } else {
            $fcs_params['courseDuration'] = 0;
        }
        //征信授权书
        $fcs_params['attachment6'] = FcsFtp::getFile($data['id'], $data['contract_credit']);
        //多张合同图
//        $fcs_params['YLZD07'] = json_encode($training_contract_ftp);
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
        if (array_key_exists('bank_id', $data)) {
            $bank_info = require PATH_CONFIG . '/bu/bank_info.php';
            $bank = $bank_info[$data['bank_id']];
            $fcs_params['hkOpenBank'] = $bank['bankname'];
        }
        //贷款类型
        $loan_type = Yii::app()->db->createCommand()
                ->select()
                ->from(ARPayLoanType::TABLE_NAME)
                ->where('rateid=:rateid', array(':rateid' => $data['loan_type']))
                ->queryRow();
        if ($loan_type['ratetype'] == 2) {
            //贴息
            $fcs_params['KZType'] = '01';
            $fcs_params['loanPeriod'] = $data['repay_need'];
        } elseif ($loan_type['ratetype'] == 3) {
            //等额本息
            $fcs_params['KZType'] = '02';
            $fcs_params['loanPeriod'] = $data['repay_need'];
        } elseif ($loan_type['ratetype'] == 1) {
            //弹性
            $fcs_params['KZType'] = '03';
            $fcs_params['loanPeriod'] = $loan_type['ratetimex'] . '+' . $loan_type['ratetimey'];
        }
        //课程分类，优先用课程的分类
        $fcs_params['YLZD02'] = $data['course_busi_type'] ? $data['course_busi_type'] : $data['busi_type'];
        //规范数据，防止富登出错
        $data['idcard'] = strtoupper($data['idcard']);
        $data['work_phone'] = str_replace(array('-', '_', '#', '+', ' ', '(', ')', '（', '）'), '', $data['work_phone']);
        //富登-课栈字段对应关系
        $map = array(
            'merchantAddrPhone' => 'contact_mobile', //机构地址电话
            'merchantAddress' => 'reg_address', //机构地址
            'coursePrice' => 'tuition', //课程价格
            'custName' => 'idcard_name', //姓名
            'cardNum' => 'idcard', //身份证号
            'analysisCardNum' => 'idcard', //身份证解析结果-身份证号
            'cardStartDate' => 'idcard_start', //身份证有效起始
            'analysisStartDate' => 'idcard_start', //身份证解析结果-身份证有效起始
            'cardEndDate' => 'idcard_expire', //身份证有效截止
            'analysisEndDate' => 'idcard_expire', //身份证解析结果-身份证有效截止
            'birth' => 'birthday', //出生日期
            'phone' => 'phone', //手机号码
            'applyAmount' => 'money_apply', //申请金额
            'applyPeriod' => 'repay_need', //申请期限
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
            'YLZD04' => 'entry_date', //入职日期
            'YLZD05' => 'work_phone', //单位电话
            'YLZD06' => 'id', //课栈贷款唯一标识
//            'YLZD08' => 'pic_education', //学历证明照片
            'emergCon1Relation' => 'contact1_relation', //联系人-与主申请人关系1
            'emergCon1Name' => 'contact1', //联系人-姓名1
            'emergCon1Phone' => 'contact1_phone', //联系人-手机号码1
            'emergCon2Relation' => 'contact2_relation', //联系人-与主申请人关系2
            'emergCon2Name' => 'contact2', //联系人-姓名2
            'emergCon2Phone' => 'contact2_phone', //联系人-手机号码2
            'professional' => 'work_desc', //职业
            'liveAddressProvience' => 'home_province', //现居住址-省
            'liveAddressCity' => 'home_city', //现居住址-市
            'liveAddressBorough' => 'home_area', //现居住址-区
            'liveDetailAddress' => 'home_address', //现居住址-详细地址
            'phoneId' => 'phoneid', //手机id
            'deviceFingerprint' => 'phoneid', //设备指纹
            'applyCourse' => 'course_name', //课程名称
            'courseStartDate' => 'course_open_time', //开课时间
            'courseLength' => 'course_period', //课程时长
            'merchantName' => 'school_name', //机构名称
            'tkAccountName' => 'school_bank_account_name', //提款账户-开户名
            'tkOpenBank' => 'school_bank_name', //提款账户-开户银行
            'tkBankCard' => 'school_bank_account', //提款账户-银行卡卡号
            'hkAccountName' => 'idcard_name', //还款账户-开户名
            'hkBankCard' => 'bank_account', //还款账户-银行卡卡号
            'workUnit' => 'work_name', //单位名称
            'unitAddressProvience' => 'work_province', //单位地址-省
            'unitAddressCity' => 'work_city', //单位地址-市
            'unitAddressBorough' => 'work_area', //单位地址-区
            'unitDetailAddress' => 'work_address', //单位地址-详细地址
            'Position' => 'position', //现任职务
            'school' => 'university', //学校名称
            'schoolAddress' => 'university_address', //学校地址
            'schoolPhone' => 'university_contact', //学校电话
            'enrolmentDate' => 'entrance_time', //入学时间
            'faceResult' => 'be_idcard', //人脸识别结果
            'WeChat' => 'wechat', //微信号
            'JD' => 'jd_id', //京东号
            'taobao' => 'tmall_id', //淘宝号
            'QQ' => 'qq', //qq号
            'email' => 'email', //邮箱
            'branchName' => 'branch_school_name', //分校名称
            'bossWorkLandline' => 'no_jrd', //是否为赊销
            'nickName' => 'nickname', //昵称
            'registerDate' => 'add_time', //注册时间
            'major' => 'major', //专业名称
            'schoolSystem' => 'education_system', //学制
        );
        foreach ($map as $fcs_k => $kz_k) {
            if (isset($data[$kz_k])) {
                $fcs_params[$fcs_k] = $data[$kz_k];
            } else {
                $fcs_params[$fcs_k] = '';
            }
        }
        $params = array_merge($data, $fcs_params);
        //如果第二联系人，默认填写课栈
        $params['emergCon2Relation'] = '朋友';
        $params['emergCon2Name'] = '课栈';
        $params['emergCon2Phone'] = '13800138000';
        //兼容富登（不必要的必填字段给默认值）
        $fcs_keys = array(
            'workUnit', 'unitAddressProvience', 'unitAddressCity',
            'unitAddressBorough', 'unitDetailAddress', 'Position'
        );
        foreach ($params as $key => $val) {
            if (!$val && in_array($key, $fcs_keys)) {
                $params[$key] = '无';
            }
        }
        return $params;
    }

}
