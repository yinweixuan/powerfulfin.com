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
use App\Models\Server\BU\BULoanBill;
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
        $lists = [];
        foreach ($loanBills as $loanBill) {
            $tmp = [
                'bill_id' => $loanBill['id'],
                'lid' => $lid,
                'uid' => $uid,
                'status' => $loanBill['status'],
                'status_desp' => BULoanBill::getLoanBillStatusDesp($loanBill['status']),
                'installment' => $loanBill['installment_plan'] . '/' . $loanBill['installment'] . '期',
                'should_repay_date' => $loanBill['should_repay_date'],
                'repay_date' => !empty($loanBill['repay_date']) ? $loanBill['repay_date'] : "",
                'repay_need' => $loanBill['total'],
                'repaid' => $loanBill['repay_total'],
                'repay_way' => '系统划扣',
                'repay_bank_account' => '',
                'repay_bank_name' => '',
                'repay_button' => 0,
                'resource' => $loanBill['resource'],
                'resource_company' => ARPFLoanProduct::$resourceCompany[$loanBill['resource']],
            ];
            $lists[] = $tmp;
        }
        return $lists;
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

        $loanProducts = BULoanProduct::getLoanTypeByIds([$loanInfo['loan_product']], true, null);
        $loanProduct = array_shift($loanProducts);
        if (empty($loanProduct)) {
            throw new PFException("金融产品异常，请稍后再试！", ERR_SYS_PARAM);
        }

        $loanBill = self::getLoanBill($lid, $uid);
        $org = ARPFOrg::getOrgById($loanInfo['oid']);
        $userReal = ARPFUsersReal::getInfo($uid);
        $userBank = ARPFUsersBank::getUserRepayBankByUid($uid);

        $info = [
            'lid' => (string)$loanInfo['id'],   //订单号
            'status' => $loanInfo['status'],    //状态
            'status_desp' => BULoanStatus::getStatusDescriptionForC($loanInfo['status']),
            'create_time' => $loanInfo['create_time'],  //申请时间
            'resource' => $loanInfo['resource'],    //资金方
            'resource_company' => ARPFLoanProduct::$resourceCompany[$loanInfo['resource']],    //资金方
            'borrow_money' => $loanInfo['borrow_money'],    //借款金额
            'installment' => (string)($loanProduct['rate_time_x'] + $loanProduct['rate_time_y']),
            'repay_need' => count($loanBill),   //总期数
            'org_name' => $org['org_name'],  //机构名称
            'oid' => $loanInfo['oid'],  //机构ID
            'full_name' => $userReal['full_name'],
            'phone' => $userReal['phone'],
            'bank_account' => $userBank['bank_account'],
            'bank_name' => $userBank['bank_name'],
            'loan_product' => $loanProduct['name'],
            'contract' => '',
            'audit_opinion' => !empty($loanInfo['audit_opinion']) ? $loanInfo['audit_opinion'] : "",
        ];
        $info['repay_now'] = '';
        if (in_array($loanInfo['status'], [LOAN_10000_REPAY, LOAN_11100_OVERDUE])) {
            $bill = DB::table(ARPFLoanBill::TABLE_NAME)
                ->select('*')
                ->where('lid', $lid)
                ->where('uid', $uid)
                ->whereIn('status', [ARPFLoanBill::STATUS_NO_REPAY, ARPFLoanBill::STATUS_OVERDUE])
                ->orderBy('installment_plan')
                ->first();
            if (!empty($bill)) {
                $info['repay_now'] = '当前待还' . $bill['installment_plan'] . '期';
            }
        }

        return $info;
    }

    public static function getLoanList($uid)
    {
        $loanList = DB::table(ARPFLoan::TABLE_NAME . ' as l')
            ->select(['l.id', 'l.borrow_money', 'l.status', 'o.org_name'])
            ->leftJoin(ARPFOrg::TABLE_NAME . ' as o', 'o.id', '=', 'l.oid')
            ->where('l.uid', $uid)
            ->orderByDesc('id')
            ->get()->toArray();
        $info = [];
        foreach ($loanList as $item) {
            $tmp = [
                'lid' => (string)$item['id'],
                'borrow_money' => $item['borrow_money'],
                'status' => $item['status'],
                'status_desp' => BULoanStatus::getStatusDescriptionForC($item['status']),
                'org_name' => $item['org_name']
            ];
            $info[] = $tmp;
        }

        return $info;
    }

    public static function getLoanConfig($oid, $uid)
    {
        BULoanApply::checkDoingLoan($uid);

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
        if (empty($class)) {
            throw new PFException("暂未获取到可分期课程，请稍后再试!", ERR_SYS_PARAM);
        }

        $loan_product = ArrayUtil::escapeEmpty(explode(',', $orgHead['loan_product']));
        $loanProducts = BULoanProduct::getLoanTypeByIds($loan_product, false, STATUS_SUCCESS);

        //计算用户支持的费率ID
        $resource = self::getUserResource($user['uid'], $loanProducts, $orgHead, $org);
        foreach ($loanProducts as $key => $loanType) {
            if ($loanType['resource'] != $resource) {
                unset($loanProducts[$key]);
            }
        }

        $loanProducts = array_values($loanProducts);
        $data = BULoanConfig::getConfig($org, $orgHead, $class, $loanProducts, $resource);
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
    public static function getHomeLoanInfo($uid = null)
    {
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
                $data['data'] = $loan;
                if (in_array($loan['status'], [
                        LOAN_1200_SURE_FILE,
                        LOAN_4500_STUDENT_SURE
                    ]
                )) {
                    //待确认
                    $data['step'] = 2;
                } elseif (in_array($loan['status'], [
                        LOAN_2100_SCHOOL_REFUSE,
                        LOAN_3100_PF_REFUSE,
                        LOAN_4100_P2P_REFUSE,
                        LOAN_5100_SCHOOL_REFUSE,
                        LOAN_14000_FOREVER_REFUSE,
                    ]
                )) {
                    //已拒绝
                    $data['step'] = 3;
                } elseif (in_array($loan['status'], [
                        LOAN_5200_SCHOOL_STOP,
                        LOAN_5400_CHANGE_RESOURCE,
                        LOAN_5500_PAY_TIME_OUT,
                        LOAN_10100_REFUSE,
                        LOAN_10200_REVOCATION,
                        LOAN_11500_BAD,
                        LOAN_12000_DROP,
                    ]
                )) {
                    //已终止
                    $data['step'] = 4;
                } elseif (in_array($loan['status'], [
                        LOAN_10000_REPAY,
                        LOAN_11100_OVERDUE,
                    ]
                )) {
                    //还款/逾期中
                    $data['step'] = 5;
                    $loan_bill = ARPFLoanBill::getLoanBillByLidAndUid($loan['id'], $uid);
                    $bill_date = '';
                    foreach ($loan_bill as $bill) {
                        if (in_array($bill['status'], [ARPFLoanBill::STATUS_NO_REPAY, ARPFLoanBill::STATUS_OVERDUE]) && ($bill['bill_date'] < $bill_date || !$bill_date)) {
                            $data['repay_date'] = date('Y-m-d', strtotime($bill['bill_date'] . '15'));
                            $data['repay_money'] = $bill['miss_total'];
                            $bill_date = $bill['bill_date'];
                        }
                    }
                    if (in_array($loan['resource'], [RESOURCE_JCFC])) {
                        $data['can_repay'] = 1;
                    }
                    if (in_array($loan['status'], [
                            LOAN_11100_OVERDUE,
                        ]
                    )) {
                        $data['is_overdue'] = 1;
                    }
                } elseif (in_array($loan['status'], [
                        LOAN_11000_FINISH,
                        LOAN_13000_EARLY_FINISH,
                    ]
                )) {
                    //已结清
                    $data['step'] = 6;
                } elseif (in_array($loan['status'], [
                        LOAN_6000_NOTICE_MONEY,
                        LOAN_6300_SUPPLY_INFO
                    ]
                )) {
                    //待放款
                    $data['step'] = 7;
                } else {
                    //审核中
                    $data['step'] = 1;
                }
            }
        }
        return $data;
    }

}
