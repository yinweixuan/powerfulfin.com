<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/22
 * Time: 6:08 PM
 */

namespace App\Models\Calc;


use App\Components\PFException;
use App\Models\ActiveRecord\ARPFLoanProduct;

class CalcLoanBill
{
    /**
     * 创建还款计划表
     * @param $loanProduct
     * @param $payTime
     * @param $principal
     * @return array
     * @throws PFException
     */
    public static function createLoanBill($loanProduct, $payTime, $principal)
    {
        //获取费率数据
        $loanProductInfo = ARPFLoanProduct::getLoanProductByProduct($loanProduct);
        if (empty($loanProductInfo)) {
            throw new PFException("费率模板不存在{$loanProduct}", ERR_SYS_PARAM);
        }
        //放款日期，如果放款日期为空，则默认为当前日期
        if (empty($payTime)) {
            $payTime = date('Y-m-d H:i:s');
        }
        //初始化还款计划表数组
        $loanBill = array(
            'repay' => array(),
            'principal' => CalcMoney::calcMoney($principal),
            'interest' => self::calcInterest($loanProductInfo, $principal),
        );
        //获取应还期数
        $times = self::calcRepayNeed($loanProductInfo);
        //计算个账期的还款日
        for ($i = 1; $i <= $times; $i++) {
            $repayDay = self::calcRepayDay(empty($repayDay) ? $payTime : $repayDay);
            $loanBill['repay'][$repayDay] = array();
        }


        $loanBill['repay'] = self::calcLoanBillList($principal, $loanProductInfo, $loanBill['repay']);
        return $loanBill;
    }

    /**
     * 计算还款日的日期
     * @param type $payTime
     * @param int $repayDay
     * @param bool $atLast
     * @return string
     */
    public static function calcRepayDay($payTime, $repayDay = 15, $atLast = false)
    {
        $payTime = date('Y-m-15 00:00:00', strtotime($payTime));
        $date = getdate(strtotime("{$payTime} +1 month"));
        $nextPayTime = (string)sprintf('%04d%02d%02d', $date['year'], $date['mon'], $repayDay);
        return $nextPayTime;
    }


    /**
     * 根据费率类型计算应还期数
     * @param $loanProduct
     * @return int
     */
    public static function calcRepayNeed($loanProduct)
    {
        switch ($loanProduct['loan_type']) {
            case ARPFLoanProduct::LOAN_TYPE_XY:
                $times = $loanProduct['rate_time_x'] + $loanProduct['rate_time_y'];
                break;
            case ARPFLoanProduct::LOAN_TYPE_DISCOUNT:
                $times = $loanProduct['rate_time_x'];
                break;
            case ARPFLoanProduct::LOAN_TYPE_EQUAL:
                $times = $loanProduct['rate_time_y'];
                break;
            default:
                $times = 0;
                break;
        }
        return $times;
    }

    /**
     * 计算利息总额
     * @param $loanProduct 费率id
     * @param $principal
     * @return float|int|string 返回利息
     */
    public static function calcInterest($loanProduct, $principal)
    {
        switch ($loanProduct['loan_type']) {
            case ARPFLoanProduct::LOAN_TYPE_XY:
                $interest = $principal * $loanProduct['rate_time_x'] * $loanProduct['rate_x'] + $principal * $loanProduct['rate_time_y'] * $loanProduct['rate_y'];
                break;
            case ARPFLoanProduct::LOAN_TYPE_DISCOUNT:
                $interest = 0;
                break;
            case ARPFLoanProduct::LOAN_TYPE_EQUAL:
                $interest = $principal * $loanProduct['rate_time_y'] * $loanProduct['rate_y'];
                break;
            default:
                $interest = 0;
                break;
        }
        return CalcMoney::calcMoney($interest);
    }

    /**
     * 计算还款计划表每期信息
     * @param $principal
     * @param $loanProduct
     * @param $repay
     * @return mixed
     */
    public static function calcLoanBillList($principal, $loanProduct, $repay)
    {
        switch ($loanProduct['loan_type']) {
            case ARPFLoanProduct::LOAN_TYPE_XY:
                $item = self::calcTanxing($principal, $loanProduct, $repay);
                break;
            case ARPFLoanProduct::LOAN_TYPE_DISCOUNT:
                $item = self::calcTiexi($principal, $loanProduct, $repay);
                break;
            case ARPFLoanProduct::LOAN_TYPE_EQUAL:
                $item = self::calcDenge($principal, $loanProduct, $repay);
                break;
            default:
                $item = $repay;
                break;
        }
        return $item;
    }


    /**
     * 计算弹性
     * @param $principal
     * @param $loanProduct
     * @param $repay
     * @return mixed
     */
    private static function calcTanxing($principal, $loanProduct, $repay)
    {
        $count = 0;
        $sumPrincipal = 0;
        $times = count($repay);
        foreach ($repay as $pay => &$item) {
            if ($count < $loanProduct['rate_time_x']) {
                $item['principal'] = 0;
                $item['interest'] = CalcMoney::calcMoney($principal * $loanProduct['rate_x']);
            } else {
                $item['principal'] = CalcMoney::calcMoney($principal / $loanProduct['rate_time_y']);
                $item['interest'] = CalcMoney::calcMoney($principal * $loanProduct['rate_y']);
            }
            $item['interview_fee'] = CalcMoney::calcMoney(($principal * $loanProduct['interview_fee']) / $times);
            //计算最后一期金额的时候,需要用总金额-已经还款金额,这样本金还的总数才会和总金额持平.
            if ($count == count($repay) - 1) {
                $item['principal'] = CalcMoney::calcMoney($principal - $sumPrincipal);
            }
            $sumPrincipal += $item['principal'];
            $item['total'] = CalcMoney::calcMoney($item['principal'] + $item['interest']);
            $item['repay'] = $pay;
            $count++;
        }
        return $repay;
    }

    /**
     * 计算贴息
     * @param $principal
     * @param $loanProduct
     * @param $repay
     * @return mixed
     */
    private static function calcTiexi($principal, $loanProduct, $repay)
    {
        $count = 0;
        $sumPrincipal = 0;
        $times = count($repay);
        foreach ($repay as $pay => &$item) {
            $item['principal'] = CalcMoney::calcMoney($principal / $times);
            $item['interest'] = 0;
            //计算最后一期金额的时候,需要用总金额-已经还款金额,这样本金还的总数才会和总金额持平.
            if ($count == count($repay) - 1) {
                $item['principal'] = CalcMoney::calcMoney($principal - $sumPrincipal);
            }
            //计算每一期还款中包含的居间服务费
            $item['interview_fee'] = CalcMoney::calcMoney(($principal * $loanProduct['interview_fee']) / $times);
            $sumPrincipal += $item['principal'];
            $item['total'] = CalcMoney::calcMoney($item['principal'] + $item['interest']);
            $item['repay'] = $pay;
            $count++;
        }
        return $repay;
    }

    /**
     * 计算等额
     * @param $principal
     * @param $loanProduct
     * @param $repay
     * @return mixed
     */
    private static function calcDenge($principal, $loanProduct, $repay)
    {
        $count = 0;
        $sumPrincipal = 0;
        $times = count($repay);
        foreach ($repay as $payTime => &$item) {
            $item['principal'] = CalcMoney::calcMoney($principal / $loanProduct['rate_time_y']);
            $item['interest'] = CalcMoney::calcMoney($principal * $loanProduct['rate_y']);
            if ($count == $times - 1) {
                $item['principal'] = CalcMoney::calcMoney($principal - $sumPrincipal);
            }
            //计算每一期还款中包含的居间服务费
            $item['interview_fee'] = CalcMoney::calcMoney(($principal * $loanProduct['interview_fee']) / $times);
            $sumPrincipal += $item['principal'];
            $item['total'] = CalcMoney::calcMoney($item['principal'] + $item['interest']);
            $item['repay'] = $payTime;
            $count++;
        }
        return $repay;
    }
}
