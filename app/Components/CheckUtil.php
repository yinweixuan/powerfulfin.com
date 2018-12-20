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
     * 检查某些进程是否还存在，返回grep到的个数
     * @param $process
     * @param array $escape
     * @param array $retArr
     * @return int
     * @throws PFException
     */
    public static function processAlive($process, $escape = array(), &$retArr = array())
    {
        if (!is_string($process)) {
            throw new PFException("process_alive input error");
        }
        $command = " ps -ef | grep '{$process}'";
        $ret = exec($command, $retArr);
        $count = 0;
        foreach ($retArr as $k => $r) {
            if (stripos($r, 'grep') !== false) {
                unset($retArr[$k]);
                continue;
            }
            $escapeCheck = false;
            //排除某些关键字
            foreach ($escape as $ke => $e) {
                if (stripos($r, $e) !== false) {
                    unset($retArr[$k]);
                    $escapeCheck = true;
                }
            }
            if (!$escapeCheck && stripos($r, $process) !== false) {
                $count++;
            }
        }
        return $count;
    }

    /**
     * 检查电话号码。1开头的11位数字，或者0开头的去掉-的11位以上数字
     * @param $str
     * @return bool
     */
    public static function phone($str)
    {
        $str = str_replace(array('-', ' '), '', $str);
        if (preg_match('/^0([0-9]{10,11})$/', $str) || preg_match('/^1([0-9]{10})$/', $str)) {
            $ret = true;
        } else {
            $ret = false;
        }
        return $ret;
    }

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
     * 检查姓名是否为汉字
     * @param $full_name
     * @return bool
     */
    public static function checkFullName($full_name)
    {
        if (preg_match('/^([\xe4-\xe9][\x80-\xbf]{2}){2,10}$/', $full_name))
            return true;
        else
            return false;
    }

    /**
     * 检查身份证号码是否正确
     * @param $vStr
     * @return bool
     */
    public static function checkIDCard($vStr)
    {
        $vCity = array(
            '11', '12', '13', '14', '15', '21', '22',
            '23', '31', '32', '33', '34', '35', '36',
            '37', '41', '42', '43', '44', '45', '46',
            '50', '51', '52', '53', '54', '61', '62',
            '63', '64', '65', '71', '81', '82', '91'
        );

        if (!preg_match('/^([\d]{17}[xX\d]|[\d]{15})$/', $vStr)) return false;

        if (!in_array(substr($vStr, 0, 2), $vCity)) return false;

        $vStr = preg_replace('/[xX]$/i', 'a', $vStr);
        $vLength = strlen($vStr);

        if ($vLength == 18) {
            $vBirthday = substr($vStr, 6, 4) . '-' . substr($vStr, 10, 2) . '-' . substr($vStr, 12, 2);
        } else {
            $vBirthday = '19' . substr($vStr, 6, 2) . '-' . substr($vStr, 8, 2) . '-' . substr($vStr, 10, 2);
        }

        if (date('Y-m-d', strtotime($vBirthday)) != $vBirthday) return false;
        if ($vLength == 18) {
            $vSum = 0;

            for ($i = 17; $i >= 0; $i--) {
                $vSubStr = substr($vStr, 17 - $i, 1);
                $vSum += (pow(2, $i) % 11) * (($vSubStr == 'a') ? 10 : intval($vSubStr, 11));
            }

            if ($vSum % 11 != 1) return false;
        }
        return true;
    }

    /**
     * 检查email是否正确
     * @param $email
     * @return bool
     */
    public static function checkEmail($email)
    {
        $result = filter_var($email, FILTER_VALIDATE_EMAIL);
        if (!$result)
            return false;
        else
            return true;
    }

    /**
     * 获取汉字名字长度
     * @param $fullname
     * @return int
     */
    public static function getNameLength($fullname)
    {
        return mb_strlen($fullname, 'UTF-8');
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

    /**
     * 根据身份证号码获取出生年月
     * @param $idcard
     * @return false|string
     */
    public static function getBirthday($idcard)
    {
        $string = substr($idcard, 6, 8);
        return date('Y-m-d', strtotime($string));
    }

    /**
     * 返回银行卡类似的格式
     * @param $cc
     * @param bool $hide 是否隐藏中间信息
     * @return string
     */
    public static function formatCreditCard($cc, $hide = true)
    {
        // REMOVE EXTRA DATA IF ANY
        $cc = str_replace(array('-', ' '), '', $cc);
        // GET THE CREDIT CARD LENGTH
        $cc_length = strlen($cc);
        $newCreditCard = substr($cc, -4);
        for ($i = $cc_length - 5; $i >= 0; $i--) {
            // ADDS HYPHEN HERE
            if ((($i + 1) - $cc_length) % 4 == 0) {
                $newCreditCard = '-' . $newCreditCard;
            }
            $newCreditCard = $cc[$i] . $newCreditCard;
        }
        // REPLACE CHARACTERS WITH X EXCEPT FIRST FOUR AND LAST FOUR
        if ($hide) {
            for ($i = 4; $i < $cc_length - 4; $i++) {
                if ($newCreditCard[$i] == '-') {
                    continue;
                }
                $newCreditCard[$i] = 'X';
            }
        }

        // RETURN THE FINAL FORMATED AND MASKED CREDIT CARD NO
        return $newCreditCard;
    }

    /**
     * 检查身份证有效期
     * @param null $idcardStart
     * @param null $idcardExpire
     * @param $idcard
     * @return bool
     * @throws PFException
     */
    public static function checkIdcardDate($idcardStart = null, $idcardExpire = null, $idcard)
    {
        if ($idcardStart === null) {
            throw new PFException("请填写身份证有效期起始时间");
        }

        if ($idcardExpire === null) {
            throw new PFException("请填写身份证有效期截止日");
        }
        $birthday = substr($idcard, 6, 8);
        $birthday = strtotime($birthday);
        $idcardStartTime = strtotime($idcardStart);
        $date = time();
        $idcardExpireTime = strtotime($idcardExpire);
        if ($idcardStartTime < $birthday) {
            throw new PFException("身份证起始日期不能早于出生日期");
        } else if ($idcardStartTime > $date) {
            throw new PFException("身份证起始日期不能大于当前日期");
        } else if ($idcardStartTime >= $idcardExpireTime) {
            throw new PFException("身份证失效日期不可早于起始日期");
        } else if ($idcardExpireTime < $date) {
            throw new PFException("身份证失效日期不可早于当前日期");
        } else if ($idcardExpireTime < $birthday) {
            throw new PFException("身份证失效日期不能早于出生日期");
        }

        $years = substr($idcardExpire, 0, 4) - substr($idcardStart, 0, 4);

        if (!in_array($years, array(5, 10, 20, 30))) {
            throw new PFException("身份证有效期应为10年、20年或30年,请检查!");
        }

        return true;
    }

    /**
     * 根据身份证号码判断性别
     * @param $idcard
     * @return int
     */
    public static function getSexByIDCard($idcard)
    {
        $tmp = intval($idcard[16]);
        if ($tmp % 2 == 1) {
            $sex = 1;
        } else {
            $sex = 2;
        }
        return $sex;
    }
}
