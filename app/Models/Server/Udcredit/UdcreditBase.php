<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:56 PM
 */

namespace App\Models\Server\Udcredit;


use App\Components\PFException;

class UdcreditBase
{
    protected $values = array();

    /**
     * 设置签名，详见签名生成算法
     **/
    public function SetSign()
    {
        $sign = $this->MakeSign();
        $this->values['sign'] = $sign;
        return $sign;
    }

    /**
     * 获取签名，详见签名生成算法的值
     * @return 值
     **/
    public function GetSign()
    {
        return strtolower($this->values['sign']);
    }

    /**
     * 判断签名，详见签名生成算法是否存在
     * @return true 或 false
     **/
    public function IsSignSet()
    {
        $isSign = array_key_exists('sign', $this->values);
        $isSignField = array_key_exists('sign_field', $this->values);
        if ($isSign && $isSignField) {
            return true;
        }
        return false;
    }

    /**
     * @param $params
     * @return array
     * @throws PFException
     */
    public function setValues($params)
    {
        if (!$params) {
            throw new PFException("数据异常！");
        }
        $this->values = $params;
        return $this->values;
    }

    /**
     * 格式化参数格式化成url参数
     */
    public function ToSignParams()
    {
        $params = explode('|', $this->values['sign_field']);
        $buff = "";
        foreach ($params as $k) {
            $buff .= $k . "=" . $this->values[$k] . "|";
        }
        $buff = substr($buff, 0, strlen($buff) - 1);
        return $buff;
    }

    /**
     * 生成签名
     * @return 签名，本函数不覆盖sign成员变量，如要设置签名需要调用SetSign方法赋值
     */
    public function MakeSign()
    {
        $string = $this->ToSignParams();
        //签名步骤二：在string后加入KEY
        $string = $string . env('UDCREDIT_MERCHANT_KEY');
        //签名步骤三：MD5加密
        $result = md5($string);
        return $result;
    }

    /**
     * 获取设置的值
     */
    public function GetValues()
    {
        return $this->values;
    }
}
