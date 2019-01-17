<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/19
 * Time: 5:19 PM
 */

namespace App\Models\Server\BU;


use App\Components\AliyunOSSUtil;
use App\Components\CheckUtil;
use App\Components\MapUtil;
use App\Components\NearbyUtil;
use App\Components\PFException;
use App\Models\ActiveRecord\ARPFAreas;
use App\Models\ActiveRecord\ARPFOrg;
use App\Models\ActiveRecord\ARPFUsersAuthLog;
use App\Models\ActiveRecord\ARPFUsersBank;
use App\Models\ActiveRecord\ARPFUsersContact;
use App\Models\ActiveRecord\ARPFUsersLocation;
use App\Models\ActiveRecord\ARPFUsersPhonebook;
use App\Models\ActiveRecord\ARPFUsersReal;
use App\Models\ActiveRecord\ARPFUsersWork;
use App\Models\DataBus;
use Illuminate\Support\Facades\DB;

class BUUserInfo
{
    private static $user;
    private static $data;

    /**
     * 获取用户配置
     * @param $user
     * @param $data
     * @param $part
     * @return array|bool
     * @throws PFException
     */
    public static function getUConfig($user, $data, $part)
    {
        self::$user = $user;
        self::$data = $data;
        switch ($part) {
            case 1:
                $result = self::getUserRealConfig();
                break;
            case 2:
                $result = self::getUserBanksConfig();
                break;
            case 3:
                $result = self::getUserContactConfig();
                break;
            case 4:
                $result = self::getUserWorkConfig();
                break;
            case 5:
                $result = [];
                break;
            case 6:
                $result = [];
                break;
            default:
                $result = false;
                break;
        }
        if ($result) {
            return $result;
        } else {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }
    }

    /**
     * 获取云慧眼认证配置
     * @return array
     */
    public static function getUserRealConfig()
    {
        $order = self::$user['id'] . '_' . DataBus::orderid();

        $info = ARPFUsersAuthLog::getUserAuthSuccessLast(self::$user['id']);
        if (!empty($info)) {
            $verified = 1;
        } else {
            $verified = 0;
        }

        return $data = array(
            'key' => env('UDCREDIT_MERCHANT_KEY'),
            'order' => $order,
            'notify_url' => 'http://www.' . DOMAIN_WEB . '/inner/udcredit/notify',
            'user_id' => ARPFUsersAuthLog::USER_ID_SUFFIX . self::$user['id'],
            'safe_mode' => ARPFUsersAuthLog::SAFE_MODE_HIGH,
            'verified' => $verified,
        );
    }

    /**
     * 获取银行卡配置
     * @return array
     */
    public static function getUserBanksConfig()
    {
        $banks = BUBanks::getBanksInfo();
        foreach ($banks as &$bank) {
            unset($bank['jcfc_bank_code']);
            unset($bank['jcfc_bank_code_tl']);
        }
        return array('bank_list' => array_values($banks));
    }

    /**
     * 获取联系人配置
     * @return array
     */
    public static function getUserContactConfig()
    {
        $data = [
            'relations' => BULoanConfig::getRelationsOne(),
            'housing_situation' => BULoanConfig::getHouseStatus(),
            'marital_status' => BULoanConfig::getMarriageStatus(),
        ];
        return $data;
    }

    public static function getUserWorkConfig()
    {
        return [
            'highest_education' => BULoanConfig::getHighestEducation(),
            'work_profession' => BULoanConfig::getPosition(),
            'profession' => BULoanConfig::getWorkDesc(),
        ];
    }


    /**
     * 个人用户信息
     * @param array $data
     * @param array $user
     * @return bool
     * @throws PFException
     */
    public static function userReal($data = array(), $user = array())
    {
        if (empty($data) || empty($user)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }
        try {
            BULoanApply::checkDoingLoan($user['id']);
        } catch (PFException $exception) {
            throw new PFException("存在贷中订单，请不要更改个人信息，谢谢！", ERR_SYS_PARAM);
        }

        $params = array(
            'full_name' => '姓名',
            'identity_number' => '身份证号码',
            'start_date' => '身份证有效期起始日',
            'end_date' => '身份证有效期失效日期',
            'address' => '身份证地址',
            'idcard_information_pic' => '身份证正面照片',
            'idcard_national_pic' => '身份证背面照片',
            'nationality' => '民族',
            'issuing_authority' => '签发机关'
        );

        self::supplyUserStepCheckParams($params, $data);

        if (!CheckUtil::checkFullName($data['full_name'])) {
            throw new PFException('身份证姓名必须为汉字，请重新认证', ERR_SYS_PARAM);
        }

        if (!CheckUtil::checkIDCard($data['identity_number'])) {
            throw new PFException('身份证号码检测错误，请重新认证', ERR_SYS_PARAM);
        }

        if (CheckUtil::getAgeByIdCard($data['identity_number']) < 18) {
            throw new PFException('未满18周岁，请监护人进行实名认证', ERR_SYS_PARAM);
        }

        //检查用户是否使用错误账户
        $check = ARPFUsersReal::checkErrorUserInfo($user['id'], $data['identity_number']);
        if (!empty($check)) {
            throw new PFException("该身份信息已经绑定其他账户，请更换登录账户", ERR_SYS_PARAM);
        }

        $userReal = ARPFUsersReal::getInfo($user['id']);
        if (!empty($userReal['identity_number']) && $userReal['identity_number'] != $data['identity_number']) {
            throw new PFException("该账号已实名，请勿使用他人账号实名");
        }

        $data['gender'] = CheckUtil::getSexByIDCard($data['identity_number']);
        ARPFUsersReal::updateInfo($user['id'], $data);

        if (!empty($data['udcredit_order'])) {
            $userAuthResult = true;
            $errorNumber = 0;
            while ($userAuthResult) {
                $userAuth = ARPFUsersAuthLog::getInfoByOrder($data['udcredit_order']);
                if (empty($userAuth)) {
                    if ($errorNumber >= 3) {
                        break;
                    }
                    sleep(1);
                    $errorNumber++;
                } else {
                    $userAuthResult = false;
                }
            }
            if (empty($userAuth)) {
                throw new PFException('授信结果查询为空，请稍后再试', ERR_SYS_PARAM);
            } else {
                $update = [
                    'face_recognition' => $userAuth['result_auth'] == ARPFUsersAuthLog::RESULT_AUTH_TRUE ? STATUS_SUCCESS : STATUS_FAIL,
                    'face_similarity' => $userAuth['be_idcard'],
                    'face_fail_reason' => $userAuth['fail_reason'],
                    'face_living_pic' => $userAuth['photo_living'],
                    'face_idcard_portrait_pic' => $userAuth['photo_grid'],
                    'birthday' => $userAuth['birthday']
                ];
                $userReal = ARPFUsersReal::getInfo($user['id']);
                if ($userReal['face_recognition'] == STATUS_SUCCESS && $update['face_recognition'] == STATUS_FAIL) {
                    return true;
                } else {
                    $result = ARPFUsersReal::updateInfo($user['id'], $update);
                    return $result;
                }
            }
        } else {
            return true;
        }
    }


    /**
     * 个人联系方式
     * @param array $data
     * @param array $user
     * @return bool
     * @throws PFException
     */
    public static function userContact($data = array(), $user = array())
    {
        if (empty($data) || empty($user)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }
        try {
            BULoanApply::checkDoingLoan($user['id']);
        } catch (PFException $exception) {
            throw new PFException("存在贷中订单，请不要更改个人信息，谢谢！", ERR_SYS_PARAM);
        }
        $params = [
            'house_status' => '住房性质',
            'home_province' => '现居地所在省',
            'home_city' => '现居地所在市',
            'home_area' => '现居地所在区',
            'home_address' => '现居地详细地址',
            'email' => '邮箱',
            'contact_person' => '紧急联系人',
            'contact_person_relation' => '紧急联系人关系',
            'contact_person_phone' => '紧急联系人联系方式',
            'marriage_status' => '婚姻状态',
        ];

        self::supplyUserStepCheckParams($params, $data);

        if (CheckUtil::getNameLength($data['home_address']) < 5) {
            throw new PFException("现居地详细地址不能少于5个字符，请重新填写", ERR_SYS_PARAM);
        }

        if (!CheckUtil::checkEmail($data['email'])) {
            throw new PFException("请正确填写邮箱地址", ERR_SYS_PARAM);
        }

        $status = self::getUserContactConfig();

        if (!in_array($data['marriage_status'], $status['marital_status'])) {
            throw new PFException("婚姻状态异常，请正确选择婚姻状态", ERR_SYS_PARAM);
        }

        if (in_array($data['marriage_status'], array('已婚有子女', '已婚无子女')) && $data['contact_person_relation'] != '配偶') {
            throw new PFException("已婚状态下第一联系人需要填写配偶信息", ERR_SYS_PARAM);
        }
        $userReal = ARPFUsersReal::getInfo($user['id']);

        $strLength = CheckUtil::getNameLength($data['home_address']);
        if ($strLength < 5) {
            throw new PFException("详细地址不少于5字符", ERR_SYS_PARAM);
        }

        if (CheckUtil::getNameLength($data['contact_person']) < 2) {
            throw new PFException("姓名格式有误,请重新填写", ERR_SYS_PARAM);
        }

        if (!CheckUtil::checkFullName($data['contact_person'])) {
            throw new PFException("联系人姓名必须为汉字", ERR_SYS_PARAM);
        }

        if (!CheckUtil::checkPhone($data['contact_person_phone'])) {
            throw new PFException("手机号格式有误,请重新填写", ERR_SYS_PARAM);
        }

        if ($data['contact_person'] == $userReal['full_name']) {
            throw new PFException("contact_person:联系人不能是本人", ERR_SYS_PARAM);
        }

        //检查联系人手机号是否重复
        if ($data['contact_person_phone'] == $userReal['phone']) {
            throw new PFException("contact_person_phone:联系人电话不能是本人电话", ERR_SYS_PARAM);
        }
        $data['marital_status'] = $data['marriage_status'];

        return ARPFUsersContact::updateInfo($user['id'], $data);
    }

    /**
     * 学历工作信息
     * @param array $data
     * @param array $user
     * @return bool
     * @throws PFException
     */
    public static function userWork($data = array(), $user = array())
    {
        if (empty($data) || empty($user)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }
        try {
            BULoanApply::checkDoingLoan($user['id']);
        } catch (PFException $exception) {
            throw new PFException("存在贷中订单，请不要更改个人信息，谢谢！", ERR_SYS_PARAM);
        }
        $params = array('highest_education' => '学历', 'profession' => '工作描述', 'working_status' => '工作状态', 'edu_pic' => '学历证明', 'monthly_income' => '月收入',);
        self::supplyUserStepCheckParams($params, $data);
        switch ($data['working_status']) {
            case ARPFUsersWork::WORKING_CONDITION_WORKING:
                $params2 = array(
                    'work_name' => '单位名称',
                    'work_province' => '单位地址所在省',
                    'work_city' => '单位地址所在市',
                    'work_area' => '单位地址所在区',
                    'work_address' => '单位详细地址',
                    'work_contact' => '单位联系电话',
                    'work_profession' => '职位名称',

                    'work_entry_time' => '入职时间'
                );
                break;
            case ARPFUsersWork::WORKING_CONDITION_READING:
                $params2 = array(
                    'school_name' => '大学名称',
                    'school_province' => '大学所在省',
                    'school_city' => '大学所在市',
                    'school_address' => '大学详细地址',
                    'school_contact' => '学校电话',
                    'entrance_time' => '入学年份',
                    'school_major' => '专业名称',
                    'education_system' => '学制',
                    'edu_pic' => '学生证',
                );

                if (!array_key_exists('school_contact', $data) || !CheckUtil::phone($data['school_contact'])) {
                    throw new PFException('请正确填写学校电话');
                }
                //判断学校地址是否填写过短。
                if (array_key_exists('school_address', $data) && CheckUtil::getNameLength($data['school_address']) < 10) {
                    throw new PFException('请填写完整的学校地址，精确到省市区、道路及具体地址，不少于10字');
                }

                if (($data['education_system'] <= 0 || $data['education_system'] > 8)) {
                    throw new PFException("学制年数异常", ERR_SYS_PARAM);
                }
                break;
            case ARPFUsersWork::WORKING_CONDITION_UNEMPLOYED:
                $params2 = array(
                    'train_contact' => '机构联系电话',
                );
                if (!array_key_exists('train_contact', $data) || !CheckUtil::phone($data['train_contact'])) {
                    throw new PFException('请正确填写机构联系电话');
                }
                break;
            default:
                throw new PFException('工作状态信息异常', ERR_SYS_PARAM);
                break;
        }
        self::supplyUserStepCheckParams($params2, $data);

        if ($data['working_status'] == ARPFUsersWork::WORKING_CONDITION_WORKING && $data['monthly_income'] > 100000) {
            throw new PFException("您挣得比俺老孙还多呢，真的吗？", ERR_SYS_PARAM);
        }

        return ARPFUsersWork::updateInfo($user['id'], $data);
    }

    /**
     * 设备定位信息
     * @param $uid
     * @param $lng
     * @param $lat
     * @param string $oid
     * @return array
     * @throws PFException
     */
    public static function userLocation($uid, $lng, $lat, $oid = '')
    {
        if (empty($uid)) {
            throw new PFException(ERR_NOLOGIN_CONTENT, ERR_NOLOGIN);
        }

        if (empty($lat) || empty($lng)) {
            throw new PFException(ERR_GPS_CONTENT, ERR_GPS);
        }
        try {
            BULoanApply::checkDoingLoan($uid);
        } catch (PFException $exception) {
            throw new PFException("存在贷中订单，请不要更改个人信息，谢谢！", ERR_SYS_PARAM);
        }
        $info = [
            'ip_address' => DataBus::get('ip'),
            'create_time' => date('Y-m-d H:i:s'),
            'location' => $lng . ',' . $lat,
            'distance' => '0.00',
            'uid' => (string)$uid,
            'channel' => '',
            'address' => '',
            'org_name' => '',
            'oid' => $oid,
        ];
        try {
            $gps = MapUtil::getPosInfo($lng, $lat);
            if ($gps['formatted_address']) {
                $info['address'] = $gps['formatted_address'];
                $info['channel'] = 'GPS';
            } else {
                $ip = MapUtil::getPosByIp($info['ip_address']);
                $gps = MapUtil::getPosInfo($ip['lng'], $ip['lat']);
                $info['address'] = $gps['formatted_address'];
                $info['channel'] = 'IP';
            }
        } catch (PFException $exception) {
            \Yii::log($exception->getMessage(), 'map.op');
        }

        if ($oid) {
            $org = ARPFOrg::getOrgById($oid);
            if ($org) {
                $info['org_name'] = $org['org_name'];
                if (!empty($org['org_lng']) && !empty($org['org_lat'])) {
                    $distance = NearbyUtil::getDistance($lng, $lat, $org['org_lng'], $org['org_lat']);
                    $info['distance'] = (string)($distance / 1000);
                }
            }
        }

        ARPFUsersLocation::addUserLocation($info);
        return $info;
    }

    /**
     * 检查每步的必填参数
     * @param array $params
     * @param array $data
     * @throws PFException
     */
    public static function supplyUserStepCheckParams($params = array(), $data = array())
    {
        foreach ($params as $key => $value) {
            if (!array_key_exists($key, $data) || trim($data[$key]) === '' || empty($data[$key])) {
                throw new PFException("请正确填写{$value}信息", ERR_SYS_PARAM);
            }
        }
    }

    public static function getUserRealInfo($uid)
    {
        $userReal = ARPFUsersReal::getInfo($uid);
        if (empty($userReal)) {
            $params = [
                'full_name' => '',
                'identity_number' => '',
                'nationality' => '',
                'start_date' => '',
                'end_date' => '',
                'address' => '',
                'issuing_authority' => '',
                'idcard_information_pic' => '',
                'idcard_national_pic' => '',
                'uid' => (string)$uid,
                'idcard_information_pic_url' => '',
                'idcard_national_pic_url' => '',
                'user_real' => self::checkUserReal($uid),
            ];
            return $params;
        } else {
            $params = [
                'full_name' => $userReal['full_name'],
                'identity_number' => $userReal['identity_number'],
                'nationality' => $userReal['nationality'],
                'start_date' => $userReal['start_date'],
                'end_date' => $userReal['end_date'],
                'address' => $userReal['address'],
                'issuing_authority' => $userReal['issuing_authority'],
                'idcard_information_pic' => $userReal['idcard_information_pic'],
                'idcard_national_pic' => $userReal['idcard_national_pic'],
                'uid' => (string)$uid,
                'idcard_information_pic_url' => '',
                'idcard_national_pic_url' => '',
                'user_real' => self::checkUserReal($uid),
            ];
            if (!empty($userReal['idcard_information_pic'])) {
                $params['idcard_information_pic_url'] = AliyunOSSUtil::getAccessUrl(AliyunOSSUtil::getLoanBucket(), $userReal['idcard_information_pic']);
            }

            if (!empty($userReal['idcard_national_pic'])) {
                $params['idcard_national_pic_url'] = AliyunOSSUtil::getAccessUrl(AliyunOSSUtil::getLoanBucket(), $userReal['idcard_national_pic']);
            }
            return $params;
        }
    }

    public static function getUserContact($uid)
    {
        $userContact = ARPFUsersContact::getContractInfo($uid);
        if (empty($userContact)) {
            $params = [
                'email' => '',
                'home_province' => '',
                'home_province_name' => '',
                'home_city' => '',
                'home_city_name' => '',
                'home_area' => '',
                'home_area_name' => '',
                'home_address' => '',
                'housing_situation' => '',
                'marital_status' => '',
                'contact_person' => '',
                'contact_person_relation' => '',
                'contact_person_phone' => '',
                'wechat' => '',
                'qq' => '',
                'uid' => (string)$uid,
            ];
            return $params;
        } else {
            $userContact['uid'] = (string)$userContact['uid'];
            $userContact['home_province'] = (string)$userContact['home_province'];
            $userContact['home_city'] = (string)$userContact['home_city'];
            $userContact['home_area'] = (string)$userContact['home_area'];
            if (!empty($userContact['home_area'])) {
                $home = ARPFAreas::getArea($userContact['home_area']);
                if ($home) {
                    list($userContact['home_province_name'], $userContact['home_city_name'], $userContact['home_area_name']) = explode(',', $home['joinname']);
                } else {
                    $userContact['home_province_name'] = '';
                    $userContact['home_city_name'] = '';
                    $userContact['home_area_name'] = '';
                }
            } else {
                $userContact['home_province_name'] = '';
                $userContact['home_city_name'] = '';
                $userContact['home_area_name'] = '';
            }
            unset($userContact['create_time']);
            unset($userContact['update_time']);
            return $userContact;
        }

    }

    public static function getUserWork($uid)
    {
        $userWork = ARPFUsersWork::getUserWork($uid);
        if (empty($userWork)) {
            $params = [
                'uid' => (string)$uid,
                'highest_education' => '',
                'profession' => '',
                'working_status' => '',
                'monthly_income' => '',
                'edu_pic' => '',
                'edu_pic_url' => '',
                'work_name' => '',
                'work_province' => '',
                'work_province_name' => '',
                'work_city' => '',
                'work_city_name' => '',
                'work_area' => '',
                'work_area_name' => '',
                'work_address' => '',
                'work_entry_time' => '',
                'work_profession' => '',
                'work_contact' => '',
                'school_name' => '',
                'school_province' => '',
                'school_province_name' => '',
                'school_city' => '',
                'school_city_name' => '',
                'school_area' => '',
                'school_area_name' => '',
                'school_address' => '',
                'school_contact' => '',
                'school_major' => '',
                'education_system' => '',
                'entrance_time' => '',
                'train_contact' => '',
            ];
            return $params;
        } else {
            $userWork['work_province_name'] = '';
            $userWork['work_city_name'] = '';
            $userWork['work_area_name'] = '';
            $userWork['school_province_name'] = '';
            $userWork['school_city_name'] = '';
            $userWork['school_area_name'] = '';
            $userWork['edu_pic_url'] = '';

            if (!empty($userWork['edu_pic'])) {
                $userWork['edu_pic_url'] = AliyunOSSUtil::getAccessUrl(AliyunOSSUtil::getLoanBucket(), $userWork['edu_pic']);
            }

            if (!empty($userWork['work_area'])) {
                $work = ARPFAreas::getArea($userWork['work_area']);
                if ($work) {
                    list($userWork['work_province_name'], $userWork['work_city_name'], $userWork['work_area_name']) = explode(',', $work['joinname']);
                }
            }

            if (!empty($userWork['school_area'])) {
                $school = ARPFAreas::getArea($userWork['school_area']);
                if ($school) {
                    list($userWork['school_province_name'], $userWork['school_city_name'], $userWork['school_area_name']) = explode(',', $school['joinname']);
                }
            }


            $userWork['uid'] = (string)$userWork['uid'];
            $userWork['work_province'] = (string)$userWork['work_province'];
            $userWork['work_city'] = (string)$userWork['work_city'];
            $userWork['work_area'] = (string)$userWork['work_area'];
            $userWork['school_province'] = (string)$userWork['school_province'];
            $userWork['school_city'] = (string)$userWork['school_city'];
            $userWork['school_area'] = (string)$userWork['school_area'];
            $userWork['working_status'] = (string)$userWork['working_status'];
            foreach ($userWork as $key => $value) {

                if (empty($value) || $value == null) {
                    $userWork[$key] = '';
                }
            }
            unset($userWork['create_time']);
            unset($userWork['update_time']);
            return $userWork;
        }

    }

    const USER_STATUS_PENDING_CREDIT = 1;
    const USER_STATUS_CREDIT_SUCCESS = 2;
    const USER_STATUS_CREDIT_FAIL = 3;

    /**
     * @param $uid
     * @return array
     * @throws PFException
     */
    public static function getUserStatus($uid)
    {
        $info = [
            'user_real' => self::checkUserReal($uid),
            'user_bank' => self::checkUserBank($uid),
            'user_contact' => self::checkUserContact($uid),
            'user_work' => self::checkUserWork($uid),
            'user_phonebook' => self::checkUserPhoneBook($uid),
            'user_loaning' => false
        ];

        try {
            BULoanApply::checkDoingLoan($uid);
        } catch (PFException $exception) {
            $info['user_loaning'] = true;
        }

        return $info;
    }

    /**
     * @param $uid
     * @return int
     * @throws PFException
     */
    private static function checkUserReal($uid)
    {
        $userAuthLog = ARPFUsersAuthLog::getInfoByUid($uid);
        if (empty($userAuthLog)) {
            return self::USER_STATUS_PENDING_CREDIT;
        }
        $result_auth_false = 0;
        $result_auth_true = 0;
        foreach ($userAuthLog as $item) {
            if ($item['result_auth'] == ARPFUsersAuthLog::RESULT_AUTH_FALSE) {
                $result_auth_false++;
            } else {
                $result_auth_true++;
            }
        }

        $count = count($userAuthLog);
        if ($result_auth_false == $count) {
            return self::USER_STATUS_CREDIT_FAIL;
        }

        return self::USER_STATUS_CREDIT_SUCCESS;
    }

    /**
     * @param $uid
     * @return int
     */
    private static function checkUserBank($uid)
    {
        $userBank = ARPFUsersBank::getUserRepayBankByUid($uid);
        if (empty($userBank)) {
            return self::USER_STATUS_PENDING_CREDIT;
        }
        if (empty($userBank['protocol_no'])) {
            return self::USER_STATUS_CREDIT_FAIL;
        }
        return self::USER_STATUS_CREDIT_SUCCESS;
    }

    /**
     * @param $uid
     * @return int
     */
    private static function checkUserContact($uid)
    {
        $userContact = ARPFUsersContact::getContractInfo($uid);
        if (empty($userContact)) {
            return self::USER_STATUS_PENDING_CREDIT;
        }

        $params = ['email', 'home_province', 'home_city', 'home_area', 'home_address', 'housing_situation', 'marital_status', 'contact_person', 'contact_person_relation', 'contact_person_phone'];
        $need = false;
        foreach ($params as $param) {
            if (empty($userContact[$param]) || !array_key_exists($param, $userContact)) {
                $need = true;
                break;
            }
        }
        if ($need) {
            return self::USER_STATUS_PENDING_CREDIT;
        }

        return self::USER_STATUS_CREDIT_SUCCESS;
    }

    /**
     * @param $uid
     * @return int
     */
    private static function checkUserWork($uid)
    {
        $userWork = ARPFUsersWork::getUserWork($uid);
        if (empty($userWork)) {
            return self::USER_STATUS_PENDING_CREDIT;
        }

        $need = false;
        switch ($userWork['working_status']) {
            case ARPFUsersWork::WORKING_CONDITION_WORKING:
                $params = ['highest_education', 'profession', 'working_status', 'monthly_income', 'edu_pic', 'work_name', 'work_province', 'work_city', 'work_area', 'work_address', 'work_entry_time', 'work_profession', 'work_contact'];
                break;
            case ARPFUsersWork::WORKING_CONDITION_READING:
                $params = ['highest_education', 'profession', 'working_status', 'monthly_income', 'edu_pic', 'school_name', 'school_province', 'school_city', 'school_area', 'school_address', 'school_contact', 'school_major', 'education_system', 'entrance_time'];
                break;
            case ARPFUsersWork::WORKING_CONDITION_UNEMPLOYED:
                $params = ['highest_education', 'profession', 'working_status', 'monthly_income', 'train_contact'];
                break;
            default:
                $need = true;
                break;
        }
        if ($need) {
            return self::USER_STATUS_PENDING_CREDIT;
        } else {
            foreach ($params as $param) {
                if (empty($userWork[$param]) || !array_key_exists($param, $userWork)) {
                    $need = true;
                    break;
                }
            }
            if ($need) {
                return self::USER_STATUS_PENDING_CREDIT;
            }
        }

        return self::USER_STATUS_CREDIT_SUCCESS;
    }

    /**
     * @param $uid
     * @return int
     */
    private static function checkUserPhoneBook($uid)
    {
        $userPhoneBook = ARPFUsersPhonebook::getPhoneBookLastOneByUid($uid);
        if (empty($userPhoneBook)) {
            return self::USER_STATUS_PENDING_CREDIT;
        }
        if ($userPhoneBook['phonebook_count'] < 15) {
            return self::USER_STATUS_CREDIT_FAIL;
        }
        return self::USER_STATUS_CREDIT_SUCCESS;
    }


}
