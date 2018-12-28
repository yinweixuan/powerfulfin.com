<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/21
 * Time: 11:05 AM
 */

namespace App\Http\Controllers\App\Models;


use App\Components\ArrayUtil;
use App\Components\CheckUtil;
use App\Components\PFException;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFLoanBill;
use App\Models\ActiveRecord\ARPFLoanProduct;
use App\Models\ActiveRecord\ARPFOrg;
use App\Models\ActiveRecord\ARPFOrgClass;
use App\Models\ActiveRecord\ARPFOrgHead;
use App\Models\ActiveRecord\ARPfUsers;
use App\Models\ActiveRecord\ARPFUsersBank;
use App\Models\ActiveRecord\ARPFUsersReal;
use App\Models\Calc\CalcResource;
use App\Models\Server\BU\BULoanApply;
use App\Models\Server\BU\BULoanConfig;
use App\Models\Server\BU\BULoanProduct;
use App\Models\Server\BU\BULoanStatus;
use Illuminate\Support\Facades\DB;

class Loan
{
    public static function getLoanBill($lid, $uid)
    {
        $loanBills = ARPFLoanBill::getLoanBillByLidAndUid($lid, $uid);
        if (empty($loanBills)) {
            return [];
        }

        foreach ($loanBills as $loanBill) {
            $tmp = [
                'bill_id' => $loanBill['id'],
                'lid' => $lid,
                'uid' => $uid,
                'status' => $loanBill['status'],
                'status_desp' => '',
                'installment' => $loanBill['installment_plan'] . '/' . $loanBill['installment'] . '期',
                'should_repay_date' => $loanBill['should_repay_date'],
                'repay_date' => $loanBill['repay_date'],
                'repay_need' => $loanBill['total'],
                'repaid' => $loanBill['repay_total'],
                'repay_way' => '系统划扣',
                'repay_bank_account' => '',
                'repay_bank_name' => '',
                'repay_button' => 1,
                'resource' => $loanBill['resource']
            ];
            $lists[] = $tmp;
        }
    }

    public static function getLoanInfo($lid, $uid)
    {
        if (!is_numeric($lid) || !is_numeric($uid)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        $loanInfo = ARPFLoan::getLoanById($lid);
        if (empty($loanInfo)) {
            throw new PFException("暂未查询到订单信息，请稍后再试！", ERR_SYS_PARAM);
        }

        if ($loanInfo['uid'] != $uid) {
            throw new PFException("请求订单信息非当前登录用户所有，请稍后重试！", ERR_SYS_PARAM);
        }


        $loanProduct = ARPFLoanProduct::getLoanProductByProduct($loanInfo['loan_product']);
        if (empty($loanProduct)) {
            throw new PFException("金融产品异常，请稍后再试！", ERR_SYS_PARAM);
        }

        $loanBill = self::getLoanBill($lid, $uid);
        $org = ARPFOrg::getOrgById($loanInfo['oid']);
        $userReal = ARPFUsersReal::getInfo($uid);
        $userBank = ARPFUsersBank::getUserRepayBankByUid($uid);
        $info = [
            'lid' => $loanInfo['id'],   //订单号
            'create_time' => $loanInfo['create_time'],  //申请时间
            'resource' => $loanInfo['resource'],    //资金方
            'borrow_money' => $loanInfo['borrow_money'],    //借款金额
            'repay_need' => count($loanBill),   //总期数
            'org' => $org['org_name'],  //机构名称
            'oid' => $loanInfo['oid'],  //机构ID
            'full_name' => $userReal['full_name'],
            'phone' => $userReal['phone'],
            'bank_account' => CheckUtil::formatCreditCard($userBank['bank_account']),
            'bank_name' => $userBank['bank_name'],
            'contract' => ''
        ];

        return $info;
    }

    public static function getLoanList($uid)
    {
        $loanList = ARPFLoan::getLoanByUid($uid);

        $info = [];
        foreach ($loanList as $item) {
            $tmp = [
                'lid' => $item['id'],
                'borrow_money' => $item['borrow_money'],
                'status' => $item['status'],
                'status_desp' => '',
                'org_name' => ''
            ];
            $info[] = $tmp;
        }

        return $info;
    }

    public static function getLoanConfig($oid, $uid)
    {
        if ($loanList = self::getLoanList($uid)) {
            foreach ($loanList as $item) {
                if (in_array($item['status'], [])) {
                    throw new PFException("您目前存在贷中订单，请稍后重试", ERR_SYS_PARAM);
                }
            }
        }

        $user = ARPfUsers::getUserAllInfo($uid);
        if (empty($user)) {
            throw new PFException("暂未正确获取用户信息，请稍后再试！", ERR_SYS_PARAM);
        }

        $org = ARPFOrg::getOrgById($oid);
        if (empty($org) || $org['status'] != STATUS_SUCCESS) {
            throw new PFException("机构暂不支持分期业务，请稍后再试！", ERR_SYS_PARAM);
        }

        $orgHead = ARPFOrgHead::getInfo($org['hid']);
        if (empty($orgHead) || empty($orgHead['loan_product'])) {
            throw new PFException("机构暂不支持分期业务，请稍后再试！", ERR_SYS_PARAM);
        }

        $class = ARPFOrgClass::getClassByOidWhichCanLoan($oid);
        if (empty($classInfo)) {
            throw new PFException("暂未获取到可分期订单，请稍后再试!", ERR_SYS_PARAM);
        }

        $loan_product = ArrayUtil::escapeEmpty(explode(',', $orgHead['loan_product']));
        $loanProducts = BULoanProduct::getLoanTypeByIds($loan_product, false);

        //计算用户支持的费率ID
        $resource = self::getUserResource($user['uid'], $loanProducts, $orgHead, $org);
        foreach ($loanProducts as $key => $loanType) {
            if ($loanType['resource'] != $resource) {
                unset($loanProducts[$key]);
            }
        }

        $loanProducts = array_values($loanProducts);
        $data = BULoanConfig::getConfig($user, $org, $orgHead, $class, $loanProducts, $resource);

        //判断资金方是否需要手持身份证照片
        $data['idcard_person_pic_switch'] = BULoanConfig::getIdcardPersonPic($resource);
        //判断资金方是否需要场景照
        $data['school_pic_switch'] = BULoanConfig::getSchoolPic($resource);
        //判断是否开启审核时间
        $data['review_time_switch'] = BULoanConfig::getReviewTimeSwitch();
        //开课时间
        $data['course_open_time_switch'] = true;
        //判断班级是否需要开启，目前为潭州必须填写
        $data['class_switch'] = BULoanConfig::getClassSwitch($orgHead['hid']);
        //重新定义是否需要培训协议照片
        $data['train'] = BULoanConfig::getTrainingContractSwitch($resource, $orgHead['hid']);
        return $data;
    }

    /**
     * 获取用户所属资金方
     * @param $uid
     * @param $loanTypes
     * @param $orgHead
     * @param $org
     * @return int
     * @throws PFException
     */
    static public function getUserResource($uid, $loanTypes, $orgHead, $org)
    {
        //计算用户资金方
        return CalcResource::getUserResource($uid, $loanTypes, $orgHead, $org);
    }


    /**
     * 提交订单申请
     * @param $data
     * @param $uid
     * @return
     * @throws PFException
     */
    public static function submitLoan($data, $uid)
    {
        if (empty($data) || empty($uid)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        $data = ArrayUtil::trimArray($data);
        //获取用户关键信息
        $userInfo = ARPfUsers::getUserAllInfo($uid);
        if (empty($userInfo)) {
            throw new PFException("获取用户基本资料失败，请稍后再试！", ERR_SYS_PARAM);
        }

        //对分期申请人的年龄进行判断
        $age = CheckUtil::getAgeByIdCard($userInfo['identity_number']);
        if ($age > 45) {
            throw new PFException("很抱歉，大于45周岁不予学费分期！", ERR_SYS_PARAM);
        }

        try {
            $data = BULoanApply::checkParams($data, $userInfo);
        } catch (PFException $exception) {
            throw new PFException($exception->getMessage(), $exception->getCode());
        }
        return BULoanApply::createSimpleLoanApplyInfo($data, $userInfo);
    }


    /**
     * 首页获取订单详情
     * @param $uid
     * @return array
     */
    public static function getHomeLoanInfo($uid = null) {
        $data = [
            'id' => 0,
            'step' => 0,
            'is_overdue' => 0,
            'repay_date' => '',
            'repay_money' => 0,
            'can_repay' => 0,
        ];
        if (!is_null($uid) && is_numeric($uid)) {
            $loan = DB::table(ARPFLoan::TABLE_NAME)->select('*')
                ->where('uid', $uid)
                ->orderByDesc('id')
                ->first();
            if (!empty($loan)) {
                $data['id'] = $loan['id'];
                if (in_array($loan['status'], [
                        LOAN_1200_SURE_FILE,
                        LOAN_4500_STUDENT_SURE
                    ]
                )) {
                    //待确认
                    $data['step'] = 2;
                } elseif (in_array($loan['status'], [
                        LOAN_2100_SCHOOL_REFUSE,
                        LOAN_3100_KZ_REFUSE,
                        LOAN_4100_P2P_REFUSE,
                        LOAN_14000_FOREVER_REFUSE,
                        LOAN_10200_REVOCATION,
                    ]
                )) {
                    //已拒绝
                    $data['step'] = 3;
                } elseif (in_array($loan['status'], [
                        LOAN_5100_SCHOOL_REFUSE,
                        LOAN_5200_SCHOOL_STOP,
                        LOAN_5400_CHANGE_RESOURCE,
                        LOAN_5500_PAY_TIME_OUT,
                        LOAN_10100_REFUSE,
                        LOAN_10200_REVOCATION,
                        LOAN_11500_BAD,
                        LOAN_11000_FINISH,
                        LOAN_12000_DROP,
                        LOAN_13000_EARLY_FINISH,
                    ]
                )) {
                    //已终止
                    $data['step'] = 4;
                } elseif (in_array($loan['status'], [
                        LOAN_10000_REPAY,
                        LOAN_11100_OVERDUE_KZ,
                        LOAN_11200_OVERDUE_P2P,
                    ]
                )) {
                    //还款/逾期中
                    $data['step'] = 5;
                    $loan_bill = ARPFLoanBill::getLoanBillByLidAndUid($loan['id'], $uid);
                    $bill_date = '';
                    foreach ($loan_bill as $bill) {
                        if (in_array($bill['status'], [0, 2]) && $bill['bill_date'] < $bill_date) {
                            $data['repay_date'] = date('Y-m-d', strtotime($bill['bill_date'] . '15'));
                            $data['repay_money'] = $bill['miss_total'];
                            $bill_date = $bill['bill_date'];
                        }
                    }
                    if (in_array($loan['resource'], [RESOURCE_JCFC])) {
                        $data['can_repay'] = 1;
                    }
                    if (in_array($loan['status'], [
                            LOAN_11100_OVERDUE_KZ,
                            LOAN_11200_OVERDUE_P2P,
                        ]
                    )) {
                        $data['is_overdue'] = 1;
                    }
                } else {
                    //审核中
                    $data['step'] = 1;
                }
            }
        }
        return $data;
    }

}
