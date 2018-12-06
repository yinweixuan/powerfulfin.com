<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/2
 * Time: 8:36 PM
 */

namespace App\Components;


class CheckUtil
{
    /**
     * 检查电话是否符合要求
     * @param $phone
     * @return bool
     */
    public static function checkPhone($phone)
    {
        if (!is_numeric($phone))
            return false;
        return preg_match('#^13[\d]{9}$|^14[\d]{9}$|^15[\d]{9}$|^17[\d]{9}$|^18[\d]{9}$#', $phone) ? true : false;
    }

    /**
     * 根据身份证号码获取年龄
     * @param $idCard
     * @return bool|false|int|string
     */
    public static function getAgeByIdCard($idCard)
    {
        if (empty($idCard)) return '';
        $birth_year = substr($idCard, 6, 4);
        $year = date('Y');
        $diff_year = $year - $birth_year;

        $birth_month = substr($idCard, 10, 2);
        $month = date('m');

        if ($month == $birth_month) {
            $birth_day = substr($idCard, 12, 2);
            $day = date('d');
            if ($birth_day > $day) {
                $age = $diff_year - 1;
            } else {
                $age = $diff_year;
            }
        } else if ($month > $birth_month) {
            $age = $diff_year;
        } else if ($month < $birth_month) {
            $age = $diff_year - 1;
        }
        return $age;
    }
}
