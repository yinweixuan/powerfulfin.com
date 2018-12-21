<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/19
 * Time: 5:19 PM
 */

namespace App\Models\Server\BU;


use App\Components\CheckUtil;
use App\Components\PFException;
use App\Components\RedisUtil;
use App\Models\ActiveRecord\ARPFUsersAuthLog;
use App\Models\ActiveRecord\ARPFUsersBank;
use App\Models\ActiveRecord\ARPFUsersReal;
use App\Models\ActiveRecord\ARPFUsersWork;
use App\Models\DataBus;

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
                $result = [];
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
            'notify_url' => DOMAIN_INNER . '/udcredit/notify',
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
            'relations' => ['父母', '配偶', '监护人', '子女'],
            'housing_situation' => ['宿舍', '租房', '与父母同住', '与其他亲属同住', '自有住房', '其他'],
            'marital_status' => ['已婚有子女', '已婚无子女', '未婚', '离异', '其他'],
        ];
        return $data;
    }


    /**
     * 实名认证第一步，个人用户信息
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
        $params = array(
            'full_name' => '姓名',
            'identity_number' => '身份证号码',
            'start_date' => '身份证有效期起始日',
            'end_date' => '身份证有效期失效日期',
            'birthday' => '生日',
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

        $data['sex'] = CheckUtil::getSexByIDCard($data['idcard']);
        ARPFUsersReal::update($user['id'], $data);

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
            }
            return true;
        }
    }


    /**
     * 实名认证第二步：个人联系方式
     * @param array $data
     * @param array $user
     * @return bool
     * @throws PFException
     */
    public static function userContact($data = array(), $user = array())
    {
        if (empty($data) || empty($user)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT);
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
        self::checkContact($data, $userReal);

        $strLength = CheckUtil::getNameLength($data['home_address']);
        if ($strLength < 5) {
            throw new PFException("详细地址不少于5字符", ERR_SYS_PARAM);
        }


    }


    /**
     * 检查联系人信息
     * @param array $ret
     * @param $userReal
     * @throws PFException
     */
    public static function checkContact($ret = array(), $userReal)
    {
        if (CheckUtil::getNameLength($ret['contact_person']) < 2) {
            throw new PFException("姓名格式有误,请重新填写");
        }

        if (!CheckUtil::checkFullName($ret['contact_person'])) {
            throw new PFException("联系人姓名必须为汉字");
        }

        if (!CheckUtil::checkPhone($ret['contact_person_phone'])) {
            throw new PFException("手机号格式有误,请重新填写");
        }

        if ($ret['contact_person'] == $userReal['full_name']) {
            throw new PFException("contact_person:联系人不能是本人");
        }

        //检查联系人手机号是否重复
        if ($ret['contact_person_phone'] == $userReal['phone']) {
            throw new PFException("contact_person_phone:联系人电话不能是本人电话");
        }
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
            throw new PFException(ERR_SYS_PARAM_CONTENT);
        }

        $params = array('highest_education' => '学历', 'profession' => '工作描述', 'working_status' => '工作状态');
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
                    'monthly_income' => '月收入',
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
                    'monthly_income' => '家庭月收入'
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
                    'monthly_income' => '家庭月收入'

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

        if ($data['work_type'] == ARPFUsersWork::WORKING_CONDITION_WORKING && $data['monthly_income'] > 100000) {
            throw new PFException("您挣得比俺老孙还多呢，真的吗？", ERR_SYS_PARAM);
        }
        $data['monthly_income'] = $data['monthly_income'] * 100;

    }


    public static function userLocation($data, $user)
    {

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


}
