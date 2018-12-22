<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:35 PM
 */

namespace App\Models\Calc;


use App\Components\PFException;

class CalcMoney
{
    //分：小数点
    const SCALE_FEN = 0;
    //元：小数点
    const SCALE_YUAN = 2;

    /**
     * 金额计算元转分
     * @param $money
     * @return string
     */
    public static function yuanToFen($money)
    {
        return bcmul($money, 100, self::SCALE_FEN);
    }

    /**
     * 金额计算分转元
     * @param $money
     * @return string
     */
    public static function fenToYuan($money)
    {
        return bcdiv($money, 100, self::SCALE_YUAN);
    }

    /**
     * 两个高精度数相加
     * @param $left
     * @param $right
     * @param $scale 精确到的小数点位数
     * @return string
     */
    public static function sum($left, $right, $scale)
    {
        return bcadd($left, $right, $scale);
    }

    /**
     * 高精度数值数组求和，递归
     * @param array $lists
     * @param $scale
     * @return int|string
     */
    public static function sumArr(array $lists, $scale = self::SCALE_YUAN)
    {
        $result = 0;
        foreach ($lists as $list) {
            if (is_array($list)) {
                $result = self::sum($result, self::sumArr($list, $scale), $scale);
            } else {
                $result = self::sum($result, $list, $scale);
            }
        }
        return $result;
    }

    /**
     * 两个高精度数相减
     * @param $left
     * @param $right
     * @param int $scale
     * @return string
     */
    public static function subtraction($left, $right, $scale = self::SCALE_YUAN)
    {
        return bcsub($left, $right, $scale);
    }

    /**
     * 两个高精度数相除
     * @param $left
     * @param $right
     * @param int $scale
     * @return string
     * @throws PFException
     */
    public static function division($left, $right, $scale = self::SCALE_YUAN)
    {
        if ($right == 0) {
            throw new PFException("数据异常：分母不能为0");
        }
        return bcdiv($left, $right, $scale);
    }

    /**
     * 两个高精度数相乘
     * @param $left
     * @param $right
     * @param int $scale
     * @return string
     */
    public static function multiplication($left, $right, $scale = self::SCALE_YUAN)
    {
        return bcmul($left, $right, $scale);
    }

    /**
     * 金额处理，保留两位小数
     * @param $amount
     * @return float
     * @throws PFException
     */
    public static function showMoneyType($amount)
    {
        $money = self::multiplication($amount, 100);
        $money = self::division($money, 100);
        return $money;
    }

    /**
     * 计算金额,两位小数,向前进位
     * @param type $money
     * @return float|int|string
     */
    public static function calcMoney($money)
    {
        $ret = sprintf("%.2f", $money);
        return $ret;
    }
}
