<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/24
 * Time: 11:49 AM
 */

namespace App\Models\Server\BU;


use App\Components\OutputUtil;
use App\Components\RedisUtil;

class BULoanConfig
{
    /**
     * 配置集合redis key
     */
    const REDIS_KEY_FOR_FUNCTION_GETCONFGI = 'PF-LOAN-CONFIG-';

    /**
     * 银行列表redis key
     */
    const REDIS_KEY_FOR_FUNCTION_GETBANKLIST = 'PF-BANK-LIST-FOR-LOAN';


    /**
     * 配置集合
     * @param array $org 机构信息
     * @param array $orgHead 机构金融信息
     * @param array $class 课程信息
     * @param array $loanProducts
     * @param $resource
     * @return array|bool|mixed|string
     */
    public static function getConfig( array $org, array $orgHead, array $class, array $loanProducts, $resource)
    {
        $redis = RedisUtil::getInstance();
        $key = self::REDIS_KEY_FOR_FUNCTION_GETCONFGI . '_' . md5($org['id']);
        $data = $redis->get($key);
        if ($data) {
            return OutputUtil::json_decode($data);
        } else {
            $data = array(
                'loanProducts' => $loanProducts,  //所支持的费率类型
                'course' => self::getClassInfo($class, $orgHead, $resource),   //课程信息描述
                'courseOpenDefaultTime' => date('Y-m-d', strtotime('+1 day')),//开课默认时间(当前日期加一天)
                'statement_pic' => self::getStatementPic($org['hid']),//是否需要上传申明图片，需要 true，不需要 FALSE
                'train' => self::getTrainingContract($org['hid']),   //判断是否需要培训协议
            );
            $redis->set($key, json_encode($data), 86400);
            return $data;
        }
    }

    /**
     * 获取第一联系人关系
     * @return array
     */
    public static function getRelationsOne()
    {
        return array('父母', '配偶', '监护人', '子女');
    }

    /**
     * 获取第二联系人关系
     * @return array
     */
    public static function getRelationsTwo()
    {
        return array('父母', '配偶', '监护人', '子女', '兄弟姐妹', '亲属', '同事', '朋友', '同学', '其他',);
    }

    /**
     * 获取职位描述
     * @return array|mixed
     */
    public static function getWorkDesc()
    {
        $workDesc = array(
            0 => '其他',
            1 => '学生',
            2 => '国家机关公务员',
            3 => '事业单位员工',
            4 => '企业单位员工',
            5 => '私营业主',
            6 => '金融业人员',
            7 => '医务人员',
            8 => '服务业人员',
            9 => '信息技术人员',
            10 => '教师',
            11 => '律师',
            12 => '工程师',
            13 => '文体业人员',
            14 => '农民',
        );
        $workDesc = array_values($workDesc);
        return $workDesc;
    }

    /**
     * 获取学历描述
     * @return array|mixed
     */
    public static function getHighestEducation()
    {
        $degree = array(
            1 => '初中及以下',
            2 => '中专',
            3 => '高中',
            4 => '大专',
            5 => '本科',
            6 => '硕士',
            7 => '博士及以上',
        );
        $degree = array_values($degree);
        return $degree;
    }

    /**
     * 获取婚姻状况
     * @return array
     */
    public static function getMarriageStatus()
    {
        $marriage_status = array(1 => '已婚有子女', 2 => '已婚无子女', 3 => '未婚', 4 => '离异', 5 => '其他');
        return array_values($marriage_status);
    }

    /**
     * 获取住房性质
     * @return array
     */
    public static function getHouseStatus()
    {
        $house_status = array(1 => '宿舍', 2 => '租房', 3 => '与父母同住', 4 => '与其他亲属同住', 5 => '自有住房', 6 => '其他');
        return array_values($house_status);
    }

    /**
     * 需要声明照片的机构
     * @var array
     */
    private static $statementPic = array(10065);

    /**
     * 获取是否需要声明照片
     * @param int $schoolId
     * @return bool
     */
    public static function getStatementPic($schoolId = 0)
    {
        if ($schoolId == 0 || !is_numeric($schoolId)) {
            return false;
        }

        if (in_array($schoolId, self::$statementPic)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 职位参数
     * @return array
     */
    public static function getPosition()
    {
        return array('高层管理人员', '中层管理人员', '基层管理人员', '普通员工');
    }

    private static $trainingSchool = array();

    /**
     * 需要上传培训协议
     * @param $sid
     * @return bool
     */
    public static function getTrainingContract($sid)
    {
        if (in_array($sid, self::$trainingSchool)) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 需要上传培训协议
     * @param $resource
     * @param $hid
     * @return bool
     */
    public static function getTrainingContractSwitch($resource, $hid)
    {
        if (in_array($hid, array())) {
            return true;
        }
        if (in_array($resource, array(RESOURCE_FCS_SC, RESOURCE_FCS))) {
            return false;
        } else {
            return true;
        }
    }

    public static function getClassInfo($classes, $orgHead, $resource)
    {
        $result = [];
        foreach ($classes as $key => $class) {
            if ($class['status'] != STATUS_SUCCESS) {
                continue;
            }
            $limit = self::getMoneyApplyLimit($class, $orgHead, $resource);
            $tmp = [
                'cid' => $class['cid'],
                'class_name' => $class['class_name'],
                'money_apply_max' => $limit['money_apply_max'],
                'money_apply_min' => $limit['money_apply_min'],
                'class_days' => (string)$class['class_days'],
                'class_price' => (int)$class['class_price'],
            ];
            $result[] = $tmp;
        }
        return $result;
    }

    /**
     * 申请金额限制
     * @param $course
     * @param $orgHead
     * @param $resource
     * @return array
     */
    public static function getMoneyApplyLimit($course, $orgHead, $resource)
    {
        $tuition = ceil($course['class_price']);

        if (in_array($resource, array(RESOURCE_FCS, RESOURCE_FCS_SC, RESOURCE_JCFC))) {
            $moneyApplyMin = 3000;
            if ($resource == RESOURCE_JCFC) {
                if ($orgHead['business_type'] == '语言') {
                    $moneyApplyMax = 50000;
                } else {
                    $moneyApplyMax = 30000;
                }
            } elseif (in_array($resource, array(RESOURCE_FCS, RESOURCE_FCS_SC))) {
                $moneyApplyMax = 30000;
            } else {
                $moneyApplyMax = 30000;
            }
            if ($moneyApplyMax > $tuition) {
                $moneyApplyMax = $tuition;
            }
        } else {
            $moneyApplyMax = $tuition;
            $moneyApplyMin = 100;
        }
        return array(
            'money_apply_max' => $moneyApplyMax,
            'money_apply_min' => $moneyApplyMin
        );
    }

    /**
     * 判断资金方是否需要手持身份证照片
     * @param $resource
     * @return bool
     */
    public static function getIdcardPersonPic($resource)
    {
        if (in_array($resource, array(RESOURCE_FCS, RESOURCE_FCS_SC))) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * 判断资金方是否需要场景照
     * @param $resource
     * @return bool
     */
    public static function getSchoolPic($resource)
    {
        if (in_array($resource, array(RESOURCE_FCS, RESOURCE_FCS_SC))) {
            return false;
        } else {
            return true;
        }
    }
}
