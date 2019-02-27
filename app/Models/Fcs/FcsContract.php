<?php

/**
 * 富登合同相关
 */

namespace App\Models\Fcs;

use App\Components\AliyunOSSUtil;
use App\Components\EsignUtil;
use App\Components\MPDFUtil;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFLoanProduct;
use App\Models\ActiveRecord\ARPFUsersBank;
use App\Models\ActiveRecord\ARPFUsersReal;

class FcsContract {

    const TITLE = 'powerfulfin.com';

    public static function getContractTemplateDir() {
        return __DIR__ . '/contract';
    }

    public static function getResData($loan) {
        $data = [];
        $data['intermediary'] = '北京中宇育达科技有限公司';
        $data['intermediary_authorized_representative'] = '孙北北';
        $data['intermediary_address'] = '北京市朝阳区将台乡安家楼西十里67号三层316室';
        $data['intermediary_product'] = '大圣分期';
        if ($loan['resource'] == RESOURCE_FCS) {
            $data['fcs_lender'] = '富登小额贷款（重庆）有限公司';
            $data['fcs_address'] = '重庆市北部新区金渝大道85号汉国中心A栋2501房';
        } elseif ($loan['resource'] == RESOURCE_FCS_SC) {
            $data['fcs_lender'] = '富登小额贷款（四川）有限公司';
            $data['fcs_address'] = '四川省成都市锦江区人民东路6号SAC航空广场11楼';
        }
        return $data;
    }

    public static function getContractPdf($template_name, $file_path, $loan, $data) {
        if (is_file($file_path)) {
            unlink($file_path);
        }
        $contract_view = self::getContractTemplateDir() . '/' . $template_name;
        ob_start();
        extract($loan);
        require $contract_view;
        $content = ob_get_clean();
        MPDFUtil::gen(self::TITLE, $content, $file_path, MPDFUtil::DEST_FILE);
        if (!is_file($file_path)) {
            throw new \Exception('生成合同模板失败');
        }
        return $file_path;
    }

    /**
     * 生成征信授权书
     */
    public static function signCredit($lid) {
        $file_dir = FcsFtp::getLocalFileDir($lid);
        $file_path_signed = $file_dir . '/credit_contract_' . $lid . '.pdf';
        $file_path = $file_path_signed . '.raw.pdf';
        if (is_file($file_path_signed)) {
            unlink($file_path_signed);
        }
        $loan = ARPFLoan::getLoanById($lid);
        $user = ARPFUsersReal::getInfo($loan['uid']);
        $data = self::getResData($loan);
        $data = array_merge($data, $user);
        self::getContractPdf('credit.php', $file_path, $loan, $data);
        $signInfos = array(
            array(
                'type' => EsignUtil::SIGN_TYPE_PERSON,
                'id' => $loan['uid'],
                'posPage' => 1,
                'posX' => 465,
                'posY' => 160,
            ),
        );
        EsignUtil::sign($file_path, $file_path_signed, $signInfos);
        if (!is_file($file_path_signed)) {
            throw new \Exception('生成征信查询授权书失败');
        } else {
            unlink($file_path);
        }
        $oss_obj = AliyunOSSUtil::uploadLoanFile($loan['resource'], $loan['hid'], $lid, $file_path_signed);
        FcsDB::updateLoan($lid, array('contract_1' => $oss_obj), $loan, '征信查询授权书');
        return $file_path_signed;
    }

    /**
     * 生成代扣协议文件
     */
    public static function signWithhold($lid) {
        $file_dir = FcsFtp::getLocalFileDir($lid);
        $file_path_signed = $file_dir . '/withhold_contract_' . $lid . '.pdf';
        $file_path = $file_path_signed . '.raw.pdf';
        if (is_file($file_path_signed)) {
            unlink($file_path_signed);
        }
        $loan = ARPFLoan::getLoanById($lid);
        $user = ARPFUsersReal::getInfo($loan['uid']);
        unset($user['phone']);//防止错误数据
        $user_bank = ARPFUsersBank::getUserRepayBankByUid($loan['uid']);
        $data = self::getResData($loan);
        $data = array_merge($data, $user, $user_bank);
        self::getContractPdf('withhold.php', $file_path, $loan, $data);
        $signInfos = array(
            array(
                'type' => EsignUtil::SIGN_TYPE_PERSON,
                'id' => $loan['uid'],
                'posPage' => 1,
                'posX' => 465,
                'posY' => 230,
            ),
        );
        EsignUtil::sign($file_path, $file_path_signed, $signInfos);
        if (!is_file($file_path_signed)) {
            throw new \Exception('生成代扣协议文件失败');
        } else {
            unlink($file_path);
        }
        $oss_obj = AliyunOSSUtil::uploadLoanFile($loan['resource'], $loan['hid'], $lid, $file_path_signed);
        FcsDB::updateLoan($lid, array('contract_2' => $oss_obj), $loan, '代扣协议文件');
        return $file_path_signed;
    }

    /**
     * 生成借款合同文件
     */
    public static function signLoan($lid) {
        set_time_limit(0);
        $file_dir = FcsFtp::getLocalFileDir($lid);
        $file_path_signed = $file_dir . '/loan_contract_' . $lid . '.pdf';
        $file_path = $file_path_signed . '.raw.pdf';
        if (is_file($file_path_signed)) {
            unlink($file_path_signed);
        }
        $loan = FcsDB::getLoanContractData($lid);
        $user = ARPFUsersReal::getInfo($loan['uid']);
        $user['user_phone'] = $user['phone'];
        unset($user['phone']);//防止错误数据
        $data = self::getResData($loan);
        $data = array_merge($data, $loan, $user);
        $data['contract_no'] = FcsLoan::getContractNo($lid);
        if ($loan['loan_type'] == ARPFLoanProduct::LOAN_TYPE_DISCOUNT) {
            //贴息
            $data['repayment'] = '等额本金';
            $data['grace_period'] = 0;
            $data['rate'] = 0;
        } elseif ($loan['loan_type'] == ARPFLoanProduct::LOAN_TYPE_EQUAL) {
            //等额
            $data['repayment'] = '等额本息';
            $data['grace_period'] = 0;
            $data['rate'] = round($loan['real_rate'] / 12, 2);
        } elseif ($loan['loan_type'] == ARPFLoanProduct::LOAN_TYPE_XY) {
            //弹性
            $data['repayment'] = '等额本息（宽限期内仅归还当期利息）';
            $data['grace_period'] = $loan['rate_time_x'];
            $data['rate'] = round($loan['real_rate'] / 12, 2);
        }
        $data['repay_need'] = $loan['rate_time_x'] + $loan['rate_time_y'];
        self::getContractPdf('loan.php', $file_path, $loan, $data);
        $signInfos = array(
            array(
                'type' => EsignUtil::SIGN_TYPE_KZ,
                'posPage' => 9,
                'posX' => 150,
                'posY' => 210,
            ),
            array(
                'type' => EsignUtil::SIGN_TYPE_PERSON,
                'id' => $loan['uid'],
                'posPage' => 9,
                'posX' => 150,
                'posY' => 125,
            ),
        );
        EsignUtil::sign($file_path, $file_path_signed, $signInfos);
        if (!is_file($file_path_signed)) {
            throw new \Exception('生成贷款协议文件失败');
        } else {
            unlink($file_path);
        }
        $oss_obj = AliyunOSSUtil::uploadLoanFile($loan['resource'], $loan['hid'], $lid, $file_path_signed);
        FcsDB::updateLoan($lid, array('loan_contract' => $oss_obj), $loan, '贷款协议文件');
        return $file_path_signed;

    }

}
