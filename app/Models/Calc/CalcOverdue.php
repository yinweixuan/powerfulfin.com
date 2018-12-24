<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/22
 * Time: 6:22 PM
 */

namespace App\Models\Calc;


use App\Components\PFException;

class CalcOverdue
{
    /**
     * 计算单期逾期天数
     * @param null $shouldRepayDate
     * @param bool $atLast
     * @return float|int
     * @throws PFException
     */
    public static function calcOverdueDays($shouldRepayDate = null, $atLast = false)
    {
        if (empty($shouldRepayDate)) {
            throw new PFException("参数异常，缺失应该时间yyyy-mm-dd", ERR_SYS_PARAM);
        }

        $nowDate = date('Y-m-d');
        //如果账期为最后一期的话则按照当前时间计算逾期天数
        //如果不是最后一期的，则按照计算还款日的下一还款计算天数
        if (!$atLast) {
            //获取下个月应还日期
            $nextShouldRepayDate = date('Y-m-d', strtotime("+1 month", strtotime($shouldRepayDate)));
            if ($nextShouldRepayDate >= $nowDate) {
                $overdueDays = (strtotime($nowDate) - strtotime($shouldRepayDate)) / 86400;
            } else {
                $overdueDays = (strtotime($nextShouldRepayDate) - strtotime($shouldRepayDate)) / 86400;
            }
        } else {
            $overdueDays = (strtotime($nowDate) - strtotime($shouldRepayDate)) / 86400;
        }

        return $overdueDays;
    }

    /**
     * 根据资金方取每一期的逾期手续费
     * @param $resource
     * @return int
     * @throws PFException
     */
    public static function calcOverdueFees($resource)
    {
        if (empty($resource)) {
            throw new PFException("请设置资金方");
        }

        switch ($resource) {
            case RESOURCE_JCFC:
                $overdueFees = number_format(20, 2, '.', '');
                break;
            case RESOURCE_FCS:
                $overdueFees = number_format(1, 2, '.', '');
                break;
            case RESOURCE_FCS_SC:
                $overdueFees = number_format(1, 2, '.', '');
                break;
            default:
                $overdueFees = number_format(0, 2, '.', '');
                break;
        }
        return $overdueFees;
    }

    /**
     * 计算逾期金额
     * @param int $principal 本金
     * @param $resource 资金方
     * @param null $shouldRepayDate 应还日期
     * @param bool $atLast 是否为最后一期
     * @return string
     * @throws PFException
     */
    public static function calcOverdueFineInterest($principal = 0, $resource, $shouldRepayDate = null, $atLast = false)
    {
        if (!is_numeric($principal) || $principal <= 0) {
            throw new PFException("本金数值异常");
        }

        if (empty($shouldRepayDate)) {
            throw new PFException("账期应还日期异常");
        }

        switch ($resource) {
            case RESOURCE_JCFC:
                $overdueFeesRate = 0.001;
                break;
            case RESOURCE_FCS:
                $overdueFeesRate = 0.0001;
                break;
            case RESOURCE_FCS_SC:
                $overdueFeesRate = 0.0001;
                break;
            default:
                $overdueFeesRate = 0;
                break;
        }

        $overdueDays = CalcOverdue::calcOverdueDays($shouldRepayDate, $atLast);
        $OverdueFineInterest = $principal * $overdueFeesRate * $overdueDays;
        return number_format($OverdueFineInterest, 2, '.', '');
    }

    /**
     * 计算还款日
     * @return int
     */
    public static function calcRepayDay()
    {
        return FN_REPAY_DAY;
    }

    /**
     * 计算逾期宽限日
     * @return int
     */
    public static function calcRepayOverdueDay()
    {
        $billDate = getdate(strtotime(date('Y-m-15')));
        if ($billDate['wday'] >= 1 && $billDate['wday'] <= 5) {
            $overdueDay = FN_REPAY_DAY + 1;
        } else if ($billDate['wday'] == 6) {
            $overdueDay = FN_REPAY_DAY + 3;
        } else if ($billDate['wday'] == 7) {
            $overdueDay = FN_REPAY_DAY + 2;
        }
        return $overdueDay;
    }
}
