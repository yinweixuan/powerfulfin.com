<?php

namespace App\Components;

require_once __DIR__ . '/../Libraries/esign/eSignOpenAPI.php';

use tech\core\eSign;

/**
 * e签宝
 */
class EsignUtil {

    const SIGN_TYPE_KZ = 1;
    const SIGN_TYPE_SCHOOL = 2;
    const SIGN_TYPE_PERSON = 3;
    const TEMPLATE_CATE_SCHOOL = 2;
    const TEMPLATE_CATE_PERSON = 3;
    const SEAL_WIDTH = 90;
    const KZ_SID = 8888;

    /**
     * e签宝签章
     * @param string $contract 待签章文件地址
     * @param string $dst_contract 签章后文件地址
     * @param array $sign_info 签章信息数组：<br>
     * 'type' => 签章类型：EsignUtil::SIGN_TYPE_KZ：平台签章；EsignUtil::SIGN_TYPE_SCHOOL：机构签章；
     * EsignUtil::SIGN_TYPE_PERSON：个人签章，<br>
     * 'id' => 签章类型对应的id，机构：sid，个人：uid，平台：无需填写，<br>
     * 'posPage' => 签章页码,<br>
     * 'posX' => x坐标,<br>
     * 'posY' => y坐标,<br>
     * 'width' => 印章展现宽度，默认100，如果字段为空，宽度超过默认宽度的印章会被等比缩小至默认宽度，
     * 宽度小于默认宽度的印章会保持原尺寸；如果字段有值，则以传入值为准。
     * @return string 下载链接的url_key
     * @throws \Exception
     */
    public static function sign($contract, $dst_contract, $sign_info) {
        try {
            if (!is_file($contract)) {
                throw new \Exception('待签章文件不能为空');
            }
            if (!$dst_contract) {
                throw new \Exception('签章文件保存地址不能为空');
            }
            if (file_exists($dst_contract) && stripos($dst_contract, '.pdf') !== false) {
                unlink($dst_contract);
            }
            $temp_dst_contract = $dst_contract . '.tmp';
            foreach ($sign_info as $item) {
                $sign_type = $item['type'];
                $id = $item['id'];
                if ($sign_type != self::SIGN_TYPE_KZ && !$id) {
                    throw new \Exception('待签章对象id不能为空');
                }
                if ($sign_type == self::SIGN_TYPE_PERSON) {
                    //个人签章
                    $account_id = self::getPersonAccount($id);
                    $seal_template = self::getTemplateSeal($account_id, self::TEMPLATE_CATE_PERSON);
                } elseif ($sign_type == self::SIGN_TYPE_SCHOOL) {
                    //机构签章
                    $account_id = self::getOrganizeAccount($id);
                    $seal_template = self::getTemplateSeal($account_id, self::TEMPLATE_CATE_SCHOOL);
                } elseif ($sign_type == self::SIGN_TYPE_KZ) {
                    //平台签章
                    $id = self::KZ_SID;
                    $account_id = self::getOrganizeAccount($id);
                    $seal_template = self::getTemplateSeal($account_id, self::TEMPLATE_CATE_SCHOOL);
                } else {
                    throw new \Exception('签章类型无效');
                }
                $sign_service_id = self::userSignPDF($account_id, $contract, $temp_dst_contract, $item, $seal_template);
                $contract = $temp_dst_contract;
            }
            if (is_file($temp_dst_contract)) {
                rename($temp_dst_contract, $dst_contract);
            }
            $log = '签章成功：' . $dst_contract . PHP_EOL;
            \Yii::log($log, 'loan.sign');
        } catch (\Exception $ex) {
            $log = '签章失败：' . $ex->getMessage() . PHP_EOL;
            $log .= '参数：' . print_r(func_get_args(), true);
            \Yii::log($log, 'loan.sign');
            throw $ex;
        }
        return $dst_contract;
    }

    /**
     * 创建个人账户
     */
    public static function addPersonAccount($uid) {
        $user = \App\Models\ActiveRecord\ARPFUsersReal::getInfo($uid);
        if (!$user) {
            throw new \Exception('用户信息不存在');
        }
        $esign = new eSign();
        $result = $esign->addPersonAccount($user['phone'], $user['full_name'], $user['identity_number']);
        $log = 'function : ' . __FUNCTION__ . PHP_EOL;
        $log .= 'request : ' . print_r(array($uid, $user['phone'], $user['idcard_name'], $user['idcard']), true);
        $log .= 'response : ' . print_r($result, true);
        \Yii::log($log, 'loan.sign');
        if ($result['errCode'] == 0 && $result['accountId']) {
            \App\Models\ActiveRecord\ARPFUsersReal::updateInfo($uid, array('esign_id' => $result['accountId']));
            //创建印章模板
            self::addTemplateSeal($result['accountId'], self::TEMPLATE_CATE_PERSON);
            return $result['accountId'];
        } else {
            throw new \Exception('创建个人账户失败：' . $result['msg']);
        }
    }

    /**
     * 获取个人账户id
     */
    public static function getPersonAccount($uid) {
        $user = \App\Models\ActiveRecord\ARPFUsersReal::getInfo($uid);
        if (!$user) {
            throw new \Exception('用户信息不存在');
        }
        $account_id = $user['esign_id'];
        //没有账户则创建账户
        if (!$account_id) {
            $account_id = self::addPersonAccount($uid);
        }
        return $account_id;
    }

    /**
     * 更新个人账户（仅更新姓名，手机号码）
     */
    public static function updatePersonAccount($uid) {
        $user = \App\Models\ActiveRecord\ARPFUsersReal::getInfo($uid);
        if (!$user) {
            throw new \Exception('用户信息不存在');
        }
        $account_id = $user['esign_id'];
        if (!$account_id) {
            //创建账户
            $account_id = self::addPersonAccount($uid);
        } else {
            //更新账户
            $update = array();
            $update['name'] = $user['full_name'];
            $update['mobile'] = $user['phone'];
            $esign = new eSign();
            $result = $esign->updatePersonAccount($account_id, $update);
            $log = 'function : ' . __FUNCTION__ . PHP_EOL;
            $log .= 'request : ' . print_r(array($uid, $account_id, $update), true);
            $log .= 'response : ' . print_r($result, true);
            \Yii::log($log, 'loan.sign');
            if ($result['errCode'] != 0) {
                throw new \Exception('更新个人账户失败：' . $result['msg']);
            }
            //更新印章模板
            self::addTemplateSeal($account_id, self::TEMPLATE_CATE_PERSON);
        }
        return $account_id;
    }

    /**
     * 创建企业账户
     */
    public static function addOrganizeAccount($sid) {
        $school = \App\Models\ActiveRecord\ARPFOrgHead::getInfo($sid);
        if (!$school) {
            throw new \Exception('机构信息不存在');
        }
        $school['business_license'] = trim(str_replace('（', '(', $school['business_license']));
        $l = strpos($school['business_license'], '(');
        if ($l > 0) {
            $school['business_license'] = substr($school['business_license'], 0, $l);
        }
        if (strpos($school['business_license'], '-') !== false) {
            $reg_type = \tech\constants\OrganRegType::NORMAL;
        } elseif (strlen($school['business_license']) == 15) {
            $reg_type = \tech\constants\OrganRegType::REGCODE;
        } else {
            $reg_type = \tech\constants\OrganRegType::MERGE;
        }
        $esign = new eSign();
        $result = $esign->addOrganizeAccount('', $school['full_name'], $school['business_license'], $reg_type);
        $log = 'function : ' . __FUNCTION__ . PHP_EOL;
        $log .= 'request : ' . print_r(array($sid, $school['full_name'], $school['license_id'], $reg_type), true);
        $log .= 'response : ' . print_r($result, true);
        \Yii::log($log, 'loan.sign');
        if ($result['errCode'] == 0 && $result['accountId']) {
            \App\Models\ActiveRecord\ARPFOrgHead::updateInfo($sid, array('esign_id' => $result['accountId']));
            //创建印章模板
            self::addTemplateSeal($result['accountId'], self::TEMPLATE_CATE_SCHOOL);
            return $result['accountId'];
        } else {
            throw new \Exception('创建企业账户失败：' . $result['msg']);
        }
    }

    /**
     * 获取企业账户id
     */
    public static function getOrganizeAccount($sid) {
        $school = \App\Models\ActiveRecord\ARPFOrgHead::getInfo($sid);
        if (!$school) {
            throw new \Exception('机构信息不存在');
        }
        $account_id = $school['esign_id'];
        //没有账户则创建账户
        if (!$account_id) {
            $account_id = self::addOrganizeAccount($sid);
        }
        return $account_id;
    }

    /**
     * 更新企业账户（仅更新企业名称）
     */
    public static function updateOrganizeAccount($sid) {
        $school = \App\Models\ActiveRecord\ARPFOrgHead::getInfo($sid);
        if (!$school) {
            throw new \Exception('机构信息不存在');
        }
        $account_id = $school['esign_id'];
        if (!$account_id) {
            //创建账户
            $account_id = self::addOrganizeAccount($sid);
        } else {
            //更新账户
            $update = array();
            $update['name'] = $school['full_name'];
            $esign = new eSign();
            $result = $esign->updateOrganizeAccount($account_id, $update);
            $log = 'function : ' . __FUNCTION__ . PHP_EOL;
            $log .= 'request : ' . print_r(array($sid, $account_id, $update), true);
            $log .= 'response : ' . print_r($result, true);
            \Yii::log($log, 'loan.sign');
            if ($result['errCode'] != 0) {
                throw new \Exception('更新企业账户失败：' . $result['msg']);
            }
            //更新印章模板
            self::addTemplateSeal($account_id, self::TEMPLATE_CATE_SCHOOL);
        }
        return $account_id;
    }

    /**
     * 创建印章模板（一对一，强制更新）
     */
    public static function addTemplateSeal($account_id, $template_cate) {
        $h_text = '';
        $q_text = '';
        $seal_color = \tech\constants\SealColor::RED;
        //区分模板类型
        if ($template_cate == self::TEMPLATE_CATE_PERSON) {
            $template_type = \tech\constants\PersonTemplateType::SQUARE;
            $user = \Illuminate\Support\Facades\DB::table(\App\Models\ActiveRecord\ARPFUsersReal::TABLE_NAME)
                    ->select(['identity_number'])
                    ->where('esign_id', $account_id)
                    ->first();
            if (mb_strlen($user['identity_number'], 'utf8') > 4) {
                $template_type = \tech\constants\PersonTemplateType::RECTANGLE;
            }
        } elseif ($template_cate == self::TEMPLATE_CATE_SCHOOL) {
            $template_type = \tech\constants\OrganizeTemplateType::STAR;
            $h_text = '合同专用章';
        } else {
            throw new \Exception('签章模板类型无效');
        }
        $esign = new eSign();
        $result = $esign->addTemplateSeal($account_id, $template_type, $seal_color, $h_text, $q_text);
        $log = 'function : ' . __FUNCTION__ . PHP_EOL;
        $log .= 'request : ' . print_r(array($account_id, $template_cate, $template_type), true);
        $log .= 'response : ' . print_r($result, true);
        \Yii::log($log, 'loan.sign');
        if ($result['errCode'] == 0 && $result['imageBase64']) {
            $seal_template_file = self::getSealTemplatePath($account_id) . '/' . $account_id;
            if (!is_dir(self::getSealTemplatePath($account_id))) {
                mkdir(self::getSealTemplatePath($account_id), 0777, true);
            }
            file_put_contents($seal_template_file, $result['imageBase64']);
            return $result['imageBase64'];
        } else {
            throw new \Exception('创建印章模板失败：' . $result['msg']);
        }
    }

    /**
     * 获取印章模板
     */
    public static function getTemplateSeal($account_id, $template_cate) {
        $seal_template = null;
        $seal_template_file = self::getSealTemplatePath($account_id) . '/' . $account_id;
        if (is_file($seal_template_file)) {
            $seal_template = file_get_contents($seal_template_file);
        }
        //没有模板则创建模板
        if (!$seal_template) {
            $seal_template = self::addTemplateSeal($account_id, $template_cate);
        }
        return $seal_template;
    }

    /**
     * 平台自身签章
     */
    public static function selfSignPDF($contract, $dst_contract, $sign_pos) {
        $sign_file = array(
            'srcPdfFile' => $contract,
            'dstPdfFile' => $dst_contract,
            'fileName' => '',
            'ownerPassword' => ''
        );
        $sign_pos_filtered = array(
            'posPage' => $sign_pos['posPage'] ? $sign_pos['posPage'] : 1,
            'posX' => $sign_pos['posX'] ? $sign_pos['posX'] : 0,
            'posY' => $sign_pos['posY'] ? $sign_pos['posY'] : 0,
            'key' => '',
            'width' => $sign_pos['width'] ? $sign_pos['width'] : self::SEAL_WIDTH,
        );
        //兼容旧坐标
        $sign_pos_filtered['posX'] += $sign_pos_filtered['width'] / 2 - 10;
        $sign_pos_filtered['posY'] += $sign_pos_filtered['width'] / 2 - 10;
        $esign = new eSign();
        $result = $esign->selfSignPDF($sign_file, $sign_pos_filtered, 0, \tech\constants\SignType::SINGLE, $stream = true);
        $log = 'function : ' . __FUNCTION__ . PHP_EOL;
        $log .= 'request : ' . print_r(array($sign_file, $sign_pos_filtered), true);
        $log .= 'response : ' . print_r($result, true);
        \Yii::log($log, 'loan.sign');
        if ($result['errCode'] == 0 && $result['signServiceId']) {
            return $result['signServiceId'];
        } else {
            throw new \Exception('签章失败：' . $result['msg']);
        }
    }

    /**
     * 平台用户签章
     */
    public static function userSignPDF($account_id, $contract, $dst_contract, $sign_pos, $seal_template) {
        $sign_file = array(
            'srcPdfFile' => $contract,
            'dstPdfFile' => $dst_contract,
            'fileName' => '',
            'ownerPassword' => ''
        );
        $sign_pos_filtered = array(
            'posPage' => $sign_pos['posPage'] ? $sign_pos['posPage'] : 1,
            'posX' => $sign_pos['posX'] ? $sign_pos['posX'] : 0,
            'posY' => $sign_pos['posY'] ? $sign_pos['posY'] : 0,
            'key' => '',
            'width' => !empty($sign_pos['width']) ? $sign_pos['width'] : self::SEAL_WIDTH,
        );
        //兼容旧坐标
        $sign_pos_filtered['posX'] += $sign_pos_filtered['width'] / 2 - 10;
        $sign_pos_filtered['posY'] += $sign_pos_filtered['width'] / 2 - 10;
        $esign = new eSign();
        $result = $esign->userSignPDF($account_id, $sign_file, $sign_pos_filtered, \tech\constants\SignType::SINGLE, $seal_template, $stream = true);
        $log = 'function : ' . __FUNCTION__ . PHP_EOL;
        $log .= 'request : ' . print_r(array($account_id, $sign_file, $sign_pos_filtered), true);
        $log .= 'response : ' . print_r($result, true);
        \Yii::log($log, 'loan.sign');
        if ($result['errCode'] == 0 && $result['signServiceId']) {
            return $result['signServiceId'];
        } else {
            throw new \Exception('签章失败：' . $result['msg']);
        }
    }

    /**
     * 查询签署详情
     */
    public static function getSignDetail($sign_service_id) {
        $esign = new eSign();
        $result = $esign->getSignDetail($sign_service_id);
        $log = 'function : ' . __FUNCTION__ . PHP_EOL;
        $log .= 'request : ' . print_r(array($sign_service_id), true);
        $log .= 'response : ' . print_r($result, true);
        \Yii::log($log, 'loan.sign');
        if ($result['errCode'] == 0) {
            return $result['signDetail'];
        } else {
            throw new \Exception('查询失败：' . $result['msg']);
        }
    }

    /**
     * 获取印章模板存放路径
     */
    public static function getSealTemplatePath($account_id) {
        $prefix = substr($account_id, 0, 2);
        return __DIR__ . '/../../storage/app/esign_seal/' . $prefix;
    }

    /**
     * 初始化
     */
    public static function init() {
        $esign = new eSign();
        $result = $esign->init();
        print_r($result);
    }

}
