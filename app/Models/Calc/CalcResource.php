<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/24
 * Time: 10:45 AM
 */

namespace App\Models\Calc;


use App\Components\CheckUtil;
use App\Components\PFException;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFUsersAuthLog;
use App\Models\ActiveRecord\ARPFUsersWork;
use Illuminate\Support\Facades\DB;

class CalcResource
{
    const YII_LOAN_NAME = 'calc_resource.op';

    /**
     * 根据机构，用户，费率，计算最佳的资金方，优先级为 富登（含四川富登）》晋商
     * @param $uid
     * @param $loanProducts
     * @param $org
     * @param null $oid
     * @return int
     * @throws PFException
     */
    static public function getUserResource($uid, $loanProducts, $org, $oid = null)
    {
        //读取用户信息
        $userInfo = BUUserInfo::getByUid($uid);

        //如果有曾经转单的资金方,可用的资金方里排除.
        $changeLoan = DB::table(ARPFLoan::TABLE_NAME)->select('resource')
            ->where('uid', $userInfo['uid'])
            ->where('status', LOAN_5400_CHANGE_RESOURCE)
            ->orderByDesc('id')
            ->first();

        if (in_array($changeLoan['resource'], array(RESOURCE_FCS, RESOURCE_FCS_SC))) {
            $changeResource = array(RESOURCE_FCS, RESOURCE_FCS_SC);
        } else {
            $changeResource = array($changeLoan['resource']);
        }

        //根据费率取出支持的资金方集合
        $resources = array();
        foreach ($loanProducts as $loanProduct) {
            if (!in_array($loanProduct['resource'], $resources) && !in_array($loanProduct['resource'], $changeResource)) {
                $resources[] = $loanProduct['resource'];
            }
        }

        //根据用户信息、机构信息 计算所支持的资金方，如果支持则返回资金方ID，否则返回0；直贷和保理无需计算，只要费率支持则支持
        $can = array();
        foreach ($resources as $resource) {
            switch ($resource) {
                case RESOURCE_JCFC:
                    $can[$resource] = self::calcJcfcResource($userInfo, $org, $errJcfc, );
                    break;
                case RESOURCE_FCS:
                case RESOURCE_FCS_SC:
                    $can[$resource] = self::calcFCSResource($userInfo, $org, $resource, $errFcs);
                    break;
                default:
                    break;
            }
        }

        //删除不支持的资金方
        foreach ($can as $key => $item) {
            if ($item == 0) {
                unset($can[$key]);
            }
        }

        if (in_array($userInfo['resource'], $can)) {
            return $userInfo['resource'];
        } else {
            //根据计算支持的资金方确定用户的订单资金方，优先级为 富登（含四川富登）》晋商 》 保理 == 直贷  目前直贷优先级略高于保理
            if (in_array(RESOURCE_FCS, $can) || in_array(RESOURCE_FCS_SC, $can)) {
                $result = isset($can[RESOURCE_FCS]) ? RESOURCE_FCS : RESOURCE_FCS_SC;
            } else if (in_array(RESOURCE_JCFC, $can)) {
                $result = RESOURCE_JCFC;
            } else {
                //错误信息,优先展示富登的
                if ($errFcs) {
                    $errMsg = implode(';', $errFcs);
                } else if ($errJcfc) {
                    $errMsg = implode(';', $errJcfc);
                } else {
                    $errMsg = "个人信息暂不符合资金方！";
                }
                throw new PFException($errMsg, ERR_SYS_PARAM);
            }
            return $result;
        }
    }

    /**
     * 计算个人信息是否符合晋商资金方
     * @param array $user
     * @param $org
     * @param array $errMsg
     * @return int
     */
    static public function calcJcfcResource(array $user, $org, &$errMsg = array())
    {
        $pass = true;

        //银行不符合晋商要求的转保理
        if (in_array($user['bank_id'], ['403',])) {
            \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "晋商：银行卡不符合要求", self::YII_LOAN_NAME);
            $pass = false;
            $errMsg[] = '资金方暂不支持招商、邮储银行卡';
        }

        //1、年龄18-40周岁；
        //2、大专（含在读）及以上学历；
        //3、学生（需上传学生证/一卡通）&在职人群（不含待业）
        //4、语言类机构的在职人员申请金额限制：3000-50000
        //5、其他申请金额限制：3000-30000
        $age = CheckUtil::getAgeByIdCard($user['identity_number']);
        if ($age > 40 || $age < 18) {
            \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "晋商：年龄不符合要求：" . $age, self::YII_LOAN_NAME);
            $pass = false;
            $errMsg[] = '年龄超过资金方要求';
        }

        if ($user['highest_education'] == '初中及以下') {
            \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "晋商：学历不符合要求：", self::YII_LOAN_NAME);
            $pass = false;
            $errMsg[] = '学历低于资金方要求';
        }

        if ($user['work_type'] == ARPFUsersWork::WORKING_CONDITION_READING && empty($user['edu_pic'])) {
            \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "晋商：无学历照片：", self::YII_LOAN_NAME);
            $pass = false;
        }

        if (!$pass) {
            return 0;
        } else {
            return RESOURCE_JCFC;
        }
    }

    /**
     * 富登 == 恒企机构限制
     * @var array
     */
    public static $fcs_oids_hengqi = array();

    /**
     * 富登 == 翡翠机构限制
     * @var array
     */
    public static $fcs_oids_feicui = array();

    /**
     * 计算个人信息是否符合富登资金方
     * @param array $user
     * @param $org
     * @param $resource
     * @param array $errMsg
     * @return float|int
     */
    static public function calcFCSResource(array $user, $org, $resource, &$errMsg = array())
    {
        $pass = true;

        //1、年龄：财会，学历：18-40，语言：18-45
        $age = CheckUtil::getAgeByIdCard($user['identity_number']);
        if ($age < 18 || $age > 45) {
            \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "富登：年龄不符合要求：" . $age, self::YII_LOAN_NAME);
            $pass = false;
            $errMsg[] = '年龄超过资金方要求';
        }
        if (in_array($org['busi_type'], array('财会', '学历'))) {
            if ($age > 40) {
                \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "富登：特殊类型机构年龄不符合要求：" . $age, self::YII_LOAN_NAME);
                $pass = false;
                $errMsg[] = '年龄超过资金方要求';
            }
        }
        if (in_array($org['busi_type'], array('IT'))) {
            if ($age > 35) {
                \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "富登：特殊类型机构年龄不符合要求（IT）：" . $age, self::YII_LOAN_NAME);
                $pass = false;
                $errMsg[] = '年龄超过资金方要求';
            }
        }

        //2、最低学历要求：高中/中专；
        if ($user['highest_education'] == '初中及以下') {
            \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "富登：学历不符合要求：", self::YII_LOAN_NAME);
            $pass = false;
            $errMsg[] = '学历低于资金方要求';
        }

        //3、必须有正式工作（签订正式劳动合同）且工作时间在3个月及以上；月收入大于2000元 ### 此处调整为需要满足月收入（家庭收入）大于等于2000元
        if ($user['monthly_income'] < 2000) {
            $pass = false;
            $errMsg[] = '收入低于资金方要求';
        }

        //只不要学生
        if ($user['work_type'] == 2 || $user['work_desc'] == '学生') {
            \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "富登：不支持学生：", self::YII_LOAN_NAME);
            $pass = false;
            $errMsg[] = '在校学生无法在该资金方进行申请';
        }

        //4、课程类型：语言类、财会类、学历类；
//        if (!in_array($schoolBusi['busi_type'], array('语言', '财会', '学历', '其他技能培训'))) {
//            Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "富登：机构类型异常：", CLogger::LEVEL_INFO, BULoanApply::YII_LOAN_NAME);
//            $pass = false;
//        }
        //5、身份证有效期：必须在有效期内；
        if ($user['idcard_expire'] < date('Y-m-d H:i:s')) {
            \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "富登：身份证有效期异常：", self::YII_LOAN_NAME);
            $pass = false;
            $errMsg[] = '身份证不在有效期内';
        }

        //6、申请提交的材料：
        //身份证正反面照片；
        //客户手持身份证照片；
        //客户在培训机构与培训机构老师的合影；
        //客户用于还款的借记卡照片；
        //PBOC和第三方征信查询授权（CFCA线上签署，无须客户操作）；
        //培训合同照片。
//        if (!$user['training_contract'] || !$user['bank_account_pic'] || !$user['school_pic'] || !$user['idcard_person_pic']) {
//            Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "富登：用户图片资料异常：", CLogger::LEVEL_INFO, BULoanApply::YII_LOAN_NAME);
//            $pass = false;
//        }
        //8、检查云慧眼数据
        $yunhuiyan = DB::table(ARPFUsersAuthLog::TABLE_NAME)->select('*')
            ->where('uid', $user['uid'])
            ->get()->toArray();
        if (empty($yunhuiyan)) {
            \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "富登：未使用云慧眼认证：", self::YII_LOAN_NAME);
            $pass = false;
            $errMsg[] = '必须进行人脸识别才可申请';
        } else {
            $yunSuccess = false;
            foreach ($yunhuiyan as $yun) {
                //如果人脸识别通过
                if ($yun['result_auth'] == 2 && $yun['be_idcard'] > 0.8 && $yun['id_card'] == $user['idcard']) {
                    $yunSuccess = true;
                }
            }
            if (!$yunSuccess) {
                \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "富登：未通过云慧眼认证：", self::YII_LOAN_NAME);
                $pass = false;
                $errMsg[] = '未通过人脸识别,请调整光线并露出全脸后重试';
            }
        }

        //判断永久被拒
        $refused_loan = DB::table(ARPFLoan::TABLE_NAME)->select('*')
            ->where('uid', $user['uid'])
            ->where('status', LOAN_14000_FOREVER_REFUSE)
            ->where('resource', $resource)
            ->first();
        if ($refused_loan) {
            \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:' . $user['uid'] . "富登：有永拒订单：", self::YII_LOAN_NAME);
            $pass = false;
            $errMsg[] = '资金方不接受该用户申请';
        }

        //不符合富登要求
        if (!$pass) {
            return 0;
        } else {
            //全部给四川
            $result = RESOURCE_FCS_SC;
            return $result == $resource ? $result : 0;
        }
    }
}
