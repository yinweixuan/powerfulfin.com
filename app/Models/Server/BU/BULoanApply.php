<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/24
 * Time: 3:06 PM
 */

namespace App\Models\Server\BU;


use App\Components\ArrayUtil;
use App\Components\HttpUtil;
use App\Components\PFException;
use App\Components\RedisUtil;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFOrg;
use App\Models\ActiveRecord\ARPFOrgClass;
use App\Models\ActiveRecord\ARPFOrgHead;
use App\Models\ActiveRecord\ARPFUsersPhonebook;
use App\Models\DataBus;
use Illuminate\Support\Facades\DB;

class BULoanApply
{
    const YII_LOAN_NAME = 'loan.op';

    /**
     * 检查数据详情
     * @param array $data
     * @param array $user
     * @return array
     * @throws PFException
     */
    static public function checkParams($data = array(), $user = array())
    {
        //检查用户是否存在正在进行的订单
        self::checkDoingLoan($user['uid'], array());


        //检查用户手机设备是否有他人进行分期申请
        $phoneid = HttpUtil::getPhoneID();
        if ($phoneid) {
            self::checkPhoneID($phoneid, $user['uid']);
        }

        $paramsNeed = array('cid', 'borrow_money', 'loan_product', 'course_open_time', 'school_pic', 'review_time');
        foreach ($paramsNeed as $item) {
            if (!array_key_exists($item, $data)) {
                throw new PFException("系统异常，必填项目未正确上报，请联系课栈运营人员", ERR_SYS_PARAM);
            }
            if (empty($data[$item]) || !isset($data[$item]) || trim($data[$item]) === '') {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
        }

        //根据课程id判断目前是否允许分期以
        $orgClass = ARPFOrgClass::getById($data['cid']);
        if (empty($course) || $orgClass['status'] != STATUS_SUCCESS) {
            throw new PFException("该课程目前暂停分期业务，请与机构联系确认，谢谢");
        }

        //判断申请价格是否高于课程价格
        if ($course['class_price'] < $data['borrow_money']) {
            throw new PFException("您的期望分期价格高于课程价格，请慎重考虑");
        }

        //根据课程信息取机构的信息，判断是否分期
        $org = ARPFOrg::getOrgById($data['oid']);
        if (empty($org) || $org['status'] != STATUS_SUCCESS) {
            throw new Exception("该课程所属机构目前暂停分期业务，请与机构联系确认，谢谢");
        }

        //判断机构是否为需要声明照片机构
        $statementPic = BULoanConfig::getStatementPic($org['hid']);
        if ($statementPic) {
            if (!array_key_exists('statement_pic', $data) || trim($data['statement_pic']) === '') {
                throw new PFException("请上传声明照片");
            }
        }

        $orgHead = ARPFOrgHead::getInfo($org['hid']);
        if (empty($orgHead) || empty($orgHead['loan_product'])) {
            throw new PFException("机构暂不支持分期业务，请稍后再试！", ERR_SYS_PARAM);
        }

        //取机构的费率信息,判断费率是否正确
        $loan_product = ArrayUtil::escapeEmpty(explode(',', $orgHead['loan_product']));
        $loanProducts = BULoanProduct::getLoanTypeByIds($loan_product, false);
        if (empty($loanProducts) || !array_key_exists($data['loan_product'], $loanProducts)) {
            throw new PFException("机构{$orgHead['full_name']}不允许进行分期业务！");
        }

        $loanProduct = $loanProducts[$data['loan_product']];
        if ($loanProduct['status'] != STATUS_SUCCESS) {
            throw new PFException("该类费率暂时不可用，请稍后再试");
        }

        //支持多张协议照片，向下兼容
        $trainingSchool = BULoanConfig::getTrainingContractSwitch($loanProduct['resource'], $orgHead['hid']);
        if ($trainingSchool) {
            if (!array_key_exists('training_contract', $data)) {
                throw new PFException("请上传协议照片");
            }
            $trainingContractArr = json_decode($data['training_contract'], true);
            if (!is_array($trainingContractArr)) {
                throw new PFException("协议照片无法解析");
            }
            $count = count($trainingContractArr);
            if ($count > 9 || $count < 1) {
                throw new PFException("您好，培训协议最多上传9张");
            }
        }

        //检查是否存在通讯录异常
        $phoneBook = ARPFUsersPhonebook::getPhoneBookLastOneByUid($user['uid']);
        if (empty($phoneBook)) {
            throw new PFException('未查询到通讯录信息', ERR_SYS_PARAM);
        }
        if (empty($phoneBook['phonebook']) || !is_array(json_decode($phoneBook['phonebook'], true))) {
            throw new PFException('未查询到通讯录信息', ERR_SYS_PARAM);
        }

        //检查开课时间
        self::checkCourseInfo($data);

        //添加分期申请附属信息
        $info = array(
            'uid' => $user['uid'],
            'oid' => $org['id'],
            'hid' => $orgHead['hid'],
            'create_time' => DataBus::get('ctime'),
            'review_time' => self::getReviewTime($data['review_time']),
            'status' => LOAN_1000_CREATE,
            'course_period' => $course['course_period'] . $course['course_period_property'],
        );

        return array_merge($data, $info);
    }

    /**
     * 检查是否存在贷中订单
     * @param $uid
     * @param array $status
     * @throws PFException
     */
    static public function checkDoingLoan($uid, array $status)
    {
        if (is_null($uid) || !is_numeric($uid)) {
            throw new PFException("提交参数异常：" . $uid, ERR_SYS_PARAM);
        }
        //检查用户是否存在正在进行的订单
        $rejectStatusArr = array(
            LOAN_2100_SCHOOL_REFUSE,
            LOAN_3100_KZ_REFUSE,
            LOAN_4100_P2P_REFUSE,
            LOAN_4300_SUPPLEMENT,
            LOAN_5100_SCHOOL_REFUSE,
            LOAN_5200_SCHOOL_STOP,
            LOAN_5400_CHANGE_RESOURCE,
            LOAN_10100_REFUSE,
            LOAN_12000_DROP,
            LOAN_11000_FINISH,
            LOAN_13000_EARLY_FINISH,
        );

        if (!empty($status)) {
            $rejectStatusArr = array_merge($rejectStatusArr, $status);
        }

        $tmpRes = DB::table(ARPFLoan::TABLE_NAME)->select('*')
            ->where('uid', $uid)
            ->whereNotIn('status', $rejectStatusArr)
            ->get()->toArray();

        if ($tmpRes) {
            throw new PFException('您已提交分期申请,不能多次申请,学习不能贪多哦', ERR_SYS_PARAM);
        }
    }

    /**
     * 检查设备号
     * @param $phoneid
     * @param $uid
     * @throws CException
     * @throws PFException
     */
    public static function checkPhoneID($phoneid, $uid)
    {
        return;
        $check = Yii::app()->db->createCommand()->select('id')->from(ARStuAddition::TABLE_NAME)->where('uid!=:uid AND phoneid=:phoneid', array(':uid' => $uid, ':phoneid' => $phoneid))->queryRow();
        if (!empty($check) && !ENV_DEBUG) {
            throw new PFException("该手机已为其他用户申请分期，请使用自己手机重新申请");
        }
    }

    /**
     * 检查课程信息
     * @param $data
     * @return bool|string
     * @throws PFException
     */
    public static function checkCourseInfo($data)
    {
        if (!isset($data['course_open_time'])) {
            throw new PFException("请填写开课时间,如果无此项,请更新课栈App", ERR_SYS_PARAM);
        } else if (trim($data['course_open_time']) === '') {
            throw new PFException("开课时间未填写", ERR_SYS_PARAM);
        } else if ($data['course_open_time'] < date('Y-m-d')) {
            throw new PFException('开课时间不能早于当前时间', ERR_SYS_PARAM);
        } else if (strtotime($data['course_open_time']) > strtotime('+60 days')) {
            throw new PFException('开课时间已超过当前两个月时间', ERR_SYS_PARAM);
        } else {
            return true;
        }
    }

    /**
     * 检查审核信息
     * @param int $review
     * @return string
     */
    public static function getReviewTime($review = 0)
    {
        if ($review == 0) {
            return '尽快审核';
        }
        $reviewTime = ['9:00-11:00', '11:00-13:00', '13:00-15:00', '15:00-17:00', '17:00-19:00',];;
        $time = time();
        if ($reviewTime[$review]) {
            list($min, $max) = explode('-', $reviewTime[$review]);
            $min = strtotime(date('Y-m-d') . $min);
            $max = strtotime(date('Y-m-d') . $max);
            if ($time > $min && $time < $max) {
                $review_time = date('Y-m-d') . ' ' . $reviewTime[$review];
            } elseif ($time > $max) {
                $review_time = self::getReviewTime($review + 1);
            }
        } else {
            $review_time = date('Y-m-d', strtotime('+1 day')) . ' ' . $reviewTime[$review - count($reviewTime)];
        }
        return $review_time;
    }


    /**
     * 创建订单
     * @param array $info
     * @param array $user
     * @return array
     * @throws PFException
     */
    public static function createSimpleLoanApplyInfo($info = array(), $user = array())
    {
        \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:Create loan info:' . var_export($info, true), self::YII_LOAN_NAME);
        try {
            //用redis做个防止重入
            $entryKey = 'pf_loan_create_' . $user['uid'];
            $redis = RedisUtil::getInstance();
            $redisRes = $redis->exists($entryKey);
            if ($redisRes) {
                \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:redis lock for uid:' . $user['uid'], self::YII_LOAN_NAME);
                throw new PFException("系统处理中,请勿重复提交", ERR_SYS_PARAM);
            } else {
                $redis->setex($entryKey, 10, DataBus::get('curtime'));
            }

            $loan = BULoanUpdate::createLoan($info);
            return $loan;
        } catch (PFException $e) {
            \Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:Create loan has error:' . $e->getMessage(), self::YII_LOAN_NAME);
            throw new PFException($e->getMessage(), $e->getCode());
        }
    }


}
