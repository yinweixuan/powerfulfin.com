<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/24
 * Time: 11:34 AM
 */

namespace App\Models\Server\BU;


use App\Components\PFException;
use App\Models\ActiveRecord\ARPFLoanProduct;

class BULoanProduct
{
    /**
     * 获取所有费率模板
     * @staticvar array $config
     * @param null $canloan
     * @param bool $isMemcache
     * @param null $status
     * @return array
     */
    public static function getAllLoanType($canloan = null, $isMemcache = true, $status = null)
    {
        static $config = array();
        if (empty($config)) {
            $config = self::getLoanTypeAll($isMemcache, $status);
            foreach ($config as $k => $tmp) {
                if ($canloan !== null && $tmp['canloan'] != $canloan) {
                    unset($config[$k]);
                    continue;
                }
                $nameAndDesp = self::getLoanTypeNameAndDesp($tmp);
                $tmp = array_merge($tmp, $nameAndDesp);
                $config[$k] = $tmp;
            }
        }
        return $config;
    }

    /**
     * 解析费率模板
     * @param type $ids
     * @param bool $isMemcache
     * @param $status
     * @return array
     */
    public static function getLoanTypeByIds($ids, $isMemcache = true, $status = true)
    {
        $ret = array();
        if (empty($ids)) {
            return $ret;
        }
        static $config = array();
        if (empty($config)) {
            $config = self::getLoanTypeAll($isMemcache, $status);
        }
        foreach ($ids as $id) {
            if (isset($config[$id])) {
                $tmp = $config[$id];
                $nameAndDesp = self::getLoanTypeNameAndDesp($tmp);
                $tmp = array_merge($tmp, $nameAndDesp);
                $ret[$id] = $tmp;
            }
        }
        return $ret;
    }

    public static function getLoanTypeAll($isMemcache = true, $status)
    {
        $config = array();
        try {
            $loanTypeAll = ARPFLoanProduct::getLoanProductAll($isMemcache, $status);
            foreach ($loanTypeAll as $key => &$item) {
                $item['resource_company'] = ARPFLoanProduct::$resourceCompany[$item['resource']];
                $item['rate_time'] = $item['rate_time_x'] + $item['rate_time_y'];
                $config[$item['loan_product']] = $item;
            }
        } catch (PFException $e) {
            \Yii::log("数据库获取费率失败:" . $e->getMessage(), 'audit.student');
        }
        return $config;
    }

    /**
     * 获取资金方描述
     * @param $resource
     * @param $isSimple 简称
     * @return string
     */
    public static function getResourceCompany($resource, $isSimple = false)
    {
        if ($isSimple) {
            $arr = ARPFLoanProduct::$resourceCompanySimple;
        } else {
            $arr = ARPFLoanProduct::$resourceCompany;
        }
        if (array_key_exists($resource, $arr)) {
            return $arr[$resource];
        } else {
            return '未知';
        }
    }

    private static function getLoanTypeNameAndDesp($tmp = array())
    {
        $data = array();

        if ($tmp['loan_type'] == ARPFLoanProduct::LOAN_TYPE_XY) {
            $data['name'] = "弹性{$tmp['rate_time_x']}+{$tmp['rate_time_y']}";
            $data['desp'] = "前{$tmp['rate_time_x']}月只还息,后{$tmp['rate_time_y']}月等额本息";
        } else if ($tmp['loan_type'] == ARPFLoanProduct::LOAN_TYPE_DISCOUNT) {
            $data['name'] = "贴息{$tmp['rate_time_x']}期";
            $data['desp'] = "连续{$tmp['rate_time_x']}固定金额";
        } else if ($tmp['loan_type'] == ARPFLoanProduct::LOAN_TYPE_EQUAL) {
            $data['name'] = "等额本息{$tmp['rate_time_y']}期";
            $data['desp'] = "连续{$tmp['rate_time_y']}月等额本息";
        }
        return $data;
    }
}
