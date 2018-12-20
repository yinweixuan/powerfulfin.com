<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/20
 * Time: 4:54 PM
 */

namespace App\Models\Server\BU;


use App\Components\PFException;
use App\Components\RedisUtil;
use App\Models\ActiveRecord\ARPFUsersBank;
use App\Models\ActiveRecord\ARPFUsersReal;

class BUUserBank
{
    /**
     * 宝付四要素验证
     * @param $userInfo
     * @param $phone
     * @param $bank_account
     * @throws PFException
     */
    public static function checkBankAccount($userInfo, $phone, $bank_account)
    {
        $redis = RedisUtil::getInstance();
        $params = array(
            'idcard_name' => $userInfo['full_name'],
            'idcard_number' => $userInfo['identity_number'],
            'bank_account' => $bank_account,
            'phone' => $phone,
            'verify' => 4,
            'api' => 'verify',
            'env' => config('env')
        );
        $redisKey = 'PF_BANK_CHECK_' . md5(json_encode($params));
        $bankCheckData = $redis->get($redisKey);
        if ($bankCheckData) {
            $result = json_decode($bankCheckData, true);
            throw new PFException("支付机构检查结果：" . $result['data']['org_desc'], ERR_SYS_UNKNOWN);
        } else {
            //TODO
            $result = ['success' => true];
            if ($result['success'] == true && !empty($result['data'])) {
                if ($result['data']['code'] != 0) {
                    $redis->set($redisKey, json_encode($result), 1800);
                    throw new PFException("支付机构验核：" . $result['data']['org_desc']);
                }
//                if (Xinyan::$xinyan_banks[$data['bank_id']] != $result['data']['bank_id']) {
//                    throw new PFException("卡片校验归属银行异常，请选择正确银行");
//                }
            } else {
                throw new PFException($result['errorMsg'], ERR_SYS_UNKNOWN);
            }
        }
    }

    /**
     * 获取宝付签约短信
     * @param $uid
     * @param $phone
     * @param $bank_account
     * @param $bank_code
     * @return array
     * @throws PFException
     */
    public static function bindSmsBaofu($uid, $phone, $bank_account, $bank_code)
    {
        try {
            //检查用户银行卡四要素
            $userInfo = ARPFUsersReal::getInfo($uid);
            if (empty($userInfo)) {
                throw new PFException("暂未获取用户认证信息", ERR_SYS_PARAM);
            }
            self::checkBankAccount($userInfo, $phone, $bank_account);

            $binds = [];
            foreach ($binds['data'] as $bind) {
                if ($bind['bank_account'] == $bank_account) {
                    throw new PFException("该银行卡已经签约绑定，请勿重复绑定", ERR_SYS_PARAM);
                }
            }
            $smsInfo = [];
            $result['data']['unique_code'] = '';
            return array(
                'serialnumber' => $result['data']['unique_code'],
            );
        } catch (PFException $exception) {
            throw new PFException($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * 宝付银行卡签约
     * @param $uid
     * @param $bank_account
     * @param $bank_code
     * @param $vcode
     * @param $serialNumber
     * @return bool
     * @throws PFException
     */
    public static function bindBaofu($uid, $phone, $bank_account, $bank_code, $vcode, $serialNumber)
    {
        try {
            $binds = [];
            foreach ($binds['data'] as $bind) {
                if ($bind['bank_account'] == $bank_account) {
                    throw new PFException("该银行卡已经签约绑定，请勿重复绑定", ERR_SYS_PARAM);
                }
            }
            $bindData = array(
                'unique_code' => $serialNumber,
                'sms_code' => $vcode,
            );

            $result = [];
            if (!empty($result['data']['protocol_no'])) {
                $info = [
                    'uid' => $uid,
                    'bank_account' => $bank_account,
                    'bank_code' => $bank_code,
                    'bank_name' => BUBanks::getBankName($bank_code),
                    'phone' => $phone,
                    'status' => STATUS_SUCCESS,
                    'type' => 2,
                    'protocol_no' => $result['data']['protocol_no'],
                    'create_time' => date('Y-m-d H:i:s'),
                ];
                ARPFUsersBank::addUserBank($info);
                return true;
            } else {
                throw new PFException("未正确获得协议号", ERR_SYS_UNKNOWN);
            }
        } catch (PFException $exception) {
            throw new PFException($exception->getMessage(), $exception->getCode());
        }
    }
}
