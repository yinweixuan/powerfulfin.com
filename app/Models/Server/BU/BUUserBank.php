<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/20
 * Time: 4:54 PM
 */

namespace App\Models\Server\BU;


use App\Components\CheckUtil;
use App\Components\PFException;
use App\Components\RedisUtil;
use App\Models\ActiveRecord\ARPFUsersBank;
use App\Models\ActiveRecord\ARPFUsersReal;
use App\Models\Epay\Epay;
use Illuminate\Support\Facades\DB;

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
            'env' => config('app.env')
        );
        $redisKey = 'PF_BANK_CHECK_' . md5(json_encode($params));
        $bankCheckData = $redis->get($redisKey);
        if ($bankCheckData) {
            $result = json_decode($bankCheckData, true);
            throw new PFException("支付机构检查结果：" . $result['data']['org_desc'], ERR_BANK_ACCOUNT);
        } else {
            //TODO
            $result = Epay::exec($params);
            if ($result['success'] == true && !empty($result['data'])) {
                if ($result['data']['code'] != 0) {
                    $redis->set($redisKey, json_encode($result), 1800);
                    throw new PFException("支付机构验核：" . $result['data']['org_desc'], ERR_BANK_ACCOUNT);
                }
            } else {
                throw new PFException($result['errorMsg'], ERR_BANK_ACCOUNT);
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
            $params = [
                'api' => 'query_bind',
                'uid' => $uid,
                'env' => config('app.env')
            ];
            $binds = Epay::exec($params);
            foreach ($binds['data'] as $bind) {
                if ($bind['bank_account'] == $bank_account) {
                    throw new PFException("该银行卡已经签约绑定，请勿重复绑定", ERR_BANK_ACCOUNT);
                }
            }
            $smsConfig = [
                'api' => 'prebind',
                'uid' => $uid,
                'env' => config('app.env'),
                'idcard_name' => $userInfo['full_name'],
                'idcard_number' => $userInfo['identity_number'],
                'phone' => $phone,
                'bank_account' => $bank_account,
                'bank_code' => $bank_code
            ];
            $result = Epay::exec($smsConfig);
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
            $params = [
                'api' => 'query_bind',
                'uid' => $uid,
                'env' => config('app.env')
            ];
            $binds = Epay::exec($params);
            foreach ($binds['data'] as $bind) {
                if ($bind['bank_account'] == $bank_account) {
                    throw new PFException("该银行卡已经签约绑定，请勿重复绑定", ERR_BANK_ACCOUNT);
                }
            }
            $bindData = array(
                'api' => 'bind',
                'uid' => $uid,
                'env' => config('app.env'),
                'unique_code' => $serialNumber,
                'sms_code' => $vcode,
            );

            $result = Epay::exec($bindData);
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

                $banks = ARPFUsersBank::getUserRepayBankByUid($uid);
                if (empty($banks)) {
                    $info['type'] = 1;
                }

                ARPFUsersBank::addUserBank($info);
                return true;
            } else {
                throw new PFException("未正确获得协议号", ERR_BANK_ACCOUNT);
            }
        } catch (PFException $exception) {
            throw new PFException($exception->getMessage(), $exception->getCode());
        }
    }

    public static function getUserBanks($uid)
    {
        if (is_null($uid) || !is_numeric($uid)) {
            return [];
        }

        $info = [];
        $userReal = ARPFUsersReal::getInfo($uid);
        if (empty($userReal) || empty($userReal['full_name']) || empty($userReal['identity_number'])) {
            throw new PFException("请先进行实名认证！", ERR_SYS_PARAM);
        }
        $info['user_real'] = [
            'full_name' => $userReal['full_name'],
            'identity_number' => $userReal['identity_number']
        ];
        $lists = ARPFUsersBank::getUserBanksByUid($uid);
        if (!empty($lists)) {
            foreach ($lists as &$list) {
                $list['logo'] = BUBanks::getBankLogo($list['bank_code']);
                $list['bank_account'] = CheckUtil::formatCreditCard($list['bank_code']);
            }
            $info['banks'] = $lists;
        } else {
            $info['banks'] = new \stdClass();
        }

        $userReal = ARPFUsersReal::getInfo($uid);
        $info['user_real'] = [
            'full_name' => $userReal['full_name'],
            'identity_number' => $userReal['identity_number']
        ];

        return $info;
    }

    public static function changeRepayCard($uid, $bank_account)
    {
        if (is_null($uid) || !is_numeric($uid) || empty($bank_account)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        $bankInfo = ARPFUsersBank::getUserBanksByUidAndBankAccount($uid, $bank_account);
        if (empty($bankInfo)) {
            throw new PFException("未正确获取银行卡信息，请稍后重试", ERR_BANK_ACCOUNT);
        }
        $update = [
            'type' => 1,
        ];
        ARPFUsersBank::updateBankInfo($bankInfo['id'], $update);
        DB::table(ARPFUsersBank::TABLE_NAME)
            ->where('uid', $uid)
            ->where('bank_account', '!=', $bank_account)
            ->update($update);
        return true;
    }
}
