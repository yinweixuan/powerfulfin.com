<?php

namespace App\Models\Fcs;

use App\Models\Fcs\FcsCommon;
use App\Models\Fcs\FcsContract;
use App\Models\Fcs\FcsField;
use App\Models\Fcs\FcsFtp;
use App\Models\Fcs\FcsHttp;
use App\Models\Fcs\FcsLoan;
use App\Models\Fcs\FcsQueue;
use App\Models\Fcs\FcsSoap;

class FcsContract {

    const ESIGN = true;

    /**
     * tcpdf支持的css：
     * font-family
     * font-size
     * font-weight
     * font-style
     * color
     * background-color
     * text-decoration
     * width
     * height
     * text-align
     */

    /**
     * 生成征信授权书
     */
    public static function signCredit($lid) {
        $filePath = PATH_AUDIT . '/upload/kz_student/FCS/' . (floor($lid / 10000)) . '/' . $lid;
        if (!file_exists($filePath)) {
            mkdir($filePath, 0755, true);
        }
        $filePath_signed = $filePath . '/credit_contract_' . $lid . '.pdf';
        $filePath .= '/credit_contract_' . $lid . '_raw.pdf';
        if (is_file($filePath_signed)) {
            unlink($filePath_signed);
        }
        $loan = LoanData::getLoanAndStudentById($lid);
        if ($loan['resource'] == ARPayLoanType::RESOURCE_FCS) {
            $viewPath = PATH_BASE . '/models/fcs/contract/credit.php';
        } elseif ($loan['resource'] == ARPayLoanType::RESOURCE_FCS_SC) {
            $viewPath = PATH_BASE . '/models/fcs/contract/credit_sc.php';
        }
        ob_start();
        extract($loan);
        require $viewPath;
        $content = ob_get_clean();
        PDFUtil::gen('kezhanwang.cn', $content, $filePath, PDFUtil::DEST_FILE, 10);
        try {
            if (self::ESIGN) {
                $signInfos = array(
                    array(
                        'type' => EsignUtil::SIGN_TYPE_PERSON,
                        'id' => $loan['uid'],
                        'posPage' => 1,
                        'posX' => 465,
                        'posY' => 80,
                    ),
                );
                $urlKey = EsignUtil::sign($filePath, $filePath_signed, $signInfos);
            } else {
                $student = Sign::createSignPerson($loan['uid'], $loan['lid']);
                $contractId = Sign::calcContractId($loan['uid'], $loan['lid'], Sign::CONTRACT_FCS_3);
                $signInfos = array(
                    array('Page' => 1, 'LX' => 465, 'LY' => 80, 'SealCode' => $student, 'SealPassword' => $student),
                );
                ROPSign::doSignContract($filePath, $contractId, $signInfos);
                ROPSign::getContract($contractId, $filePath_signed, $urlKey);
            }
            ARLoan::_update($loan['lid'], array('contract_3' => $urlKey));
            ARLoanLog::insertLog($loan, $loan['status'], '征信查询授权书');
        } catch (Exception $exception) {
            throw new KZException($exception->getMessage());
        }
        if (!is_file($filePath_signed)) {
            throw new KZException('生成征信查询授权书失败');
        }
        return $filePath_signed;
    }

    /**
     * 生成代扣协议文件
     */
    public static function signWithhold($lid) {
        $filePath = PATH_AUDIT . '/upload/kz_student/FCS/' . (floor($lid / 10000)) . '/' . $lid;
        if (!file_exists($filePath)) {
            mkdir($filePath, 0755, true);
        }
        $filePath_signed = $filePath . '/withhold_contract_' . $lid . '.pdf';
        $filePath .= '/withhold_contract_' . $lid . '_raw.pdf';
        if (is_file($filePath_signed)) {
            unlink($filePath_signed);
        }
        $loan = LoanData::getLoanAndStudentById($lid);
        $bank_info = require PATH_CONFIG . '/bu/bank_info.php';
        $bank = $bank_info[$loan['bank_id']];
        if ($loan['resource'] == ARPayLoanType::RESOURCE_FCS) {
            $viewPath = PATH_BASE . '/models/fcs/contract/withhold.php';
        } elseif ($loan['resource'] == ARPayLoanType::RESOURCE_FCS_SC) {
            $viewPath = PATH_BASE . '/models/fcs/contract/withhold_sc.php';
        }
        ob_start();
        extract($loan);
        require $viewPath;
        $content = ob_get_clean();
        PDFUtil::gen('kezhanwang.cn', $content, $filePath, PDFUtil::DEST_FILE);
        try {
            if (self::ESIGN) {
                $signInfos = array(
                    array(
                        'type' => EsignUtil::SIGN_TYPE_PERSON,
                        'id' => $loan['uid'],
                        'posPage' => 1,
                        'posX' => 465,
                        'posY' => 100,
                    ),
                );
                $urlKey = EsignUtil::sign($filePath, $filePath_signed, $signInfos);
            } else {
                $student = Sign::createSignPerson($loan['uid'], $loan['lid']);
                $contractId = Sign::calcContractId($loan['uid'], $loan['lid'], Sign::CONTRACT_FCS_2);
                $signInfos = array(
                    array('Page' => 1, 'LX' => 465, 'LY' => 100, 'SealCode' => $student, 'SealPassword' => $student),
                );
                ROPSign::doSignContract($filePath, $contractId, $signInfos);
                ROPSign::getContract($contractId, $filePath_signed, $urlKey);
            }
            ARLoan::_update($loan['lid'], array('contract_2' => $urlKey));
            ARLoanLog::insertLog($loan, $loan['status'], '代扣协议文件');
        } catch (Exception $exception) {
            throw new KZException($exception->getMessage());
        }
        if (!is_file($filePath_signed)) {
            throw new KZException('生成代扣协议文件失败');
        }
        return $filePath_signed;
    }

    /**
     * 生成借款合同文件
     */
    public static function signLoan($lid) {
        set_time_limit(0);
        $filePath = PATH_AUDIT . '/upload/kz_student/FCS/' . (floor($lid / 10000)) . '/' . $lid;
        if (!file_exists($filePath)) {
            mkdir($filePath, 0755, true);
        }
        $filePath_signed = $filePath . '/loan_contract_' . $lid . '.pdf';
        $filePath .= '/loan_contract_' . $lid . '_raw.pdf';
        if (is_file($filePath_signed)) {
            unlink($filePath_signed);
        }
        $fields = 'stu.idcard_name,stu.idcard,stu.phone,stu.home_address,stu.bank_id,'
                . 'stu.bank_account,stu.work_name,stu.work_address,stu.email,stu.uid,stu.lid,'
                . 'l.money_apply,l.status,l.id,l.money_school,l.pay_type,'
                . 'l.resource,l.repay_need,a.address,'
                . 'lt.ratetimex,lt.ratex,lt.ratetimey,lt.ratey,lt.ratetype,lt.real_rate,'
                . 's.full_name as school_name,s.bank_account_name as school_bank_account_name,'
                . 's.bank_name as school_bank_name,s.bank_account as school_bank_account,';
        $loan = Yii::app()->db->createCommand()
                ->select($fields)
                ->from(ARLoan::TABLE_NAME . ' l')
                ->leftJoin(ARStuAddition::TABLE_NAME . ' stu', 'stu.lid=l.id')
                ->leftJoin(ARPayUserAuth::TABLE_NAME . ' a', 'a.lid=l.id')
                ->leftJoin(ARSchoolBusi::TABLE_NAME . ' s', 's.sid=l.sbid')
                ->leftJoin(ARPayLoanType::TABLE_NAME . ' lt', 'lt.rateid=l.loan_type')
                ->where('l.id=:id', array(':id' => $lid))
                ->queryRow();
        $bank_info = require PATH_CONFIG . '/bu/bank_info.php';
        $bank = $bank_info[$loan['bank_id']];
        $contract_no = FcsLoan::getContractNo($lid);
        if ($loan['resource'] == ARPayLoanType::RESOURCE_FCS) {
            $data['fcs_lender'] = '富登小额贷款（重庆）有限公司';
            $data['fcs_address'] = '重庆市北部新区金渝大道85号汉国中心A栋2501房';
        } elseif ($loan['resource'] == ARPayLoanType::RESOURCE_FCS_SC) {
            $data['fcs_lender'] = '富登小额贷款（四川）有限公司';
            $data['fcs_address'] = '四川省成都市锦江区人民东路6号SAC航空广场11楼';
        }
        if ($loan['ratetype'] == 2) {
            //贴息
            $data['repayment'] = '等额本金';
            $data['grace_period'] = 0;
            $data['rate'] = 0;
        } elseif ($loan['ratetype'] == 3) {
            //等额
            $data['repayment'] = '等额本息';
            $data['grace_period'] = 0;
            $data['rate'] = round($loan['real_rate'] / 12, 2);
        } elseif ($loan['ratetype'] == 1) {
            //弹性
            $data['repayment'] = '等额本息（宽限期内仅归还当期利息）';
            $data['grace_period'] = $loan['ratetimex'];
            $data['rate'] = round($loan['real_rate'] / 12, 2);
        }
        $viewPath = PATH_BASE . '/models/fcs/contract/loan_new.php';
        ob_start();
        extract($loan);
        require $viewPath;
        $content = ob_get_clean();
        PDFUtil::gen('kezhanwang.cn', $content, $filePath, PDFUtil::DEST_FILE);
        try {
            if (self::ESIGN) {
                $signInfos = array(
                    array(
                        'type' => EsignUtil::SIGN_TYPE_KZ,
                        'posPage' => 11,
                        'posX' => 150,
                        'posY' => 70,
                    ),
                    array(
                        'type' => EsignUtil::SIGN_TYPE_PERSON,
                        'id' => $loan['uid'],
                        'posPage' => 12,
                        'posX' => 150,
                        'posY' => 670,
                    ),
                );
                $urlKey = EsignUtil::sign($filePath, $filePath_signed, $signInfos);
            } else {
                $student = Sign::createSignPerson($loan['uid'], $loan['lid']);
                $contractId = Sign::calcContractId($loan['uid'], $loan['lid'], Sign::CONTRACT_FCS_1);
                $signInfos = array(
                    array('Page' => 11, 'LX' => 150, 'LY' => 70, 'SealCode' => Sign::SIGN_CODE_KEZHANWANG, 'SealPassword' => Sign::SIGN_CODE_KEZHANWANG),
                    array('Page' => 12, 'LX' => 150, 'LY' => 670, 'SealCode' => $student, 'SealPassword' => $student),
                );
                ROPSign::doSignContract($filePath, $contractId, $signInfos);
                ROPSign::getContract($contractId, $filePath_signed, $urlKey);
            }
            ARLoan::_update($loan['lid'], array('contract_1' => $urlKey));
            ARLoanLog::insertLog($loan, $loan['status'], '贷款协议文件');
        } catch (Exception $exception) {
            throw new KZException($exception->getMessage());
        }
        if (!is_file($filePath_signed)) {
            throw new KZException('生成贷款协议文件失败');
        }
        return $filePath_signed;
    }

}
