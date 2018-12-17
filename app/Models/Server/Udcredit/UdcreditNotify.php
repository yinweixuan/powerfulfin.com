<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:55 PM
 */

namespace App\Models\Server\Udcredit;


use App\Components\PFException;

class UdcreditNotify extends UdcreditBase
{
    const SUCCESS = 1;
    const SUCCESS_MSG = "接收成功";
    const FAIL = 0;
    const FAIL_MSG = "接收失败";

    /**
     *
     * 检测签名
     */
    public function CheckSign()
    {
        if (!$this->IsSignSet()) {
            throw new PFException("签名错误！");
        }

        $sign = $this->MakeSign();
        if ($this->GetSign() == $sign) {
            return true;
        }
        throw new PFException("签名错误！sing=" . $sign);
    }

    /**
     *
     * 设置参数
     * @param string $key
     * @param string $value
     */
    public function SetData($key, $value)
    {
        $this->values[$key] = $value;
    }

    /**
     * @param $params
     * @return array
     * @throws PFException
     */
    public static function Init($params)
    {
        $obj = new self();
        $obj->setValues($params);
        $obj->CheckSign();
        // 更新数据
        return $obj->GetValues();
    }

    /**
     * 返回通知结果
     * @param bool $isSuccess
     */
    public static function ReplyNotify($isSuccess = false)
    {
        if ($isSuccess) {
            echo json_encode(array('code' => UdcreditNotify::SUCCESS, 'message' => UdcreditNotify::SUCCESS_MSG));
        } else {
            echo json_encode(array('code' => UdcreditNotify::FAIL, 'message' => UdcreditNotify::FAIL_MSG));
        }
        return;
    }
}
