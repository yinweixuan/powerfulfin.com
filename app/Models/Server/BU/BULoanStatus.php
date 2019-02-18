<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/25
 * Time: 10:19 AM
 */

namespace App\Models\Server\BU;


class BULoanStatus
{
    /**
     * 找不到状态的默认展示
     */
    const NOT_FOUND = "未知";

    public static function getStatusDescriptionForC($status = null)
    {
        $info = [
            LOAN_1000_CREATE => '创建订单成功',
            LOAN_1100_CREATE_ACCOUNT => '申请成功',
            LOAN_1200_SURE_FILE => '确认合同',
            LOAN_2000_SCHOOL_CONFIRM => '机构确认',
            LOAN_2100_SCHOOL_REFUSE => '机构拒绝',
            LOAN_3000_PF_CONFIRM => '大圣已通过',
            LOAN_3100_PF_REFUSE => '大圣已拒绝',
            LOAN_3200_PF_CREDIT_COMPANY => '审核中',
            LOAN_3300_PF_CREDIT_UPLOAD => '审核中',
            LOAN_3400_PF_CREDIT_SEND_INFO => '审核中',
            LOAN_3500_PF_CREDIT_LOAN_APPLY => '审核中',
            LOAN_3600_PF_CREDIT_UPLOAD_SUCCESS => '审核中',
            LOAN_3700_PF_CREDIT_SEND_INFO_SUCCESS => '审核中',
            LOAN_4200_DATA_P2P_SEND => '审核中',
            LOAN_4000_P2P_CONFIRM => '审核通过',
            LOAN_4100_P2P_REFUSE => '审核拒绝',
            LOAN_4300_SUPPLEMENT => '补充信息',
            LOAN_4400_P2P_AUTO_PASS => '审核通过',
            LOAN_4500_STUDENT_SURE => '学员确认',
            LOAN_5000_SCHOOL_BEGIN => '机构确认上课',
            LOAN_5100_SCHOOL_REFUSE => '已终止',
            LOAN_5200_SCHOOL_STOP => '已终止',
            LOAN_5300_SCHOOL_PAUSE => '已终止',
            LOAN_5400_CHANGE_RESOURCE => '已终止',
            LOAN_5500_PAY_TIME_OUT => '已终止',
            LOAN_6000_NOTICE_MONEY => '等待放款',
            LOAN_6300_SUPPLY_INFO => '补充资料',
            LOAN_10000_REPAY => '还款中',
            LOAN_10100_REFUSE => '放款拒绝',
            LOAN_10200_REVOCATION => '放款撤销',
            LOAN_11000_FINISH => '已结清',
            LOAN_11100_OVERDUE => '已逾期',
            LOAN_11500_BAD => '坏账',
            LOAN_12000_DROP => '退课',
            LOAN_13000_EARLY_FINISH => '提前还款',
            LOAN_14000_FOREVER_REFUSE => '永久拒绝',
        ];
        if ($status === null) {
            return $info;
        } else if (array_key_exists($status, $info)) {
            return $info[$status];
        } else {
            return self::NOT_FOUND;
        }
    }

    public static function getStatusDescriptionForB($status = null)
    {
        $info = [
            LOAN_1000_CREATE => '创建订单成功',
            LOAN_1100_CREATE_ACCOUNT => '等待报名确认',
            LOAN_1200_SURE_FILE => '确认合同',
            LOAN_2000_SCHOOL_CONFIRM => '机构确认',
            LOAN_2100_SCHOOL_REFUSE => '机构拒绝',
            LOAN_3000_PF_CONFIRM => '大圣已通过',
            LOAN_3100_PF_REFUSE => '大圣已拒绝',
            LOAN_3200_PF_CREDIT_COMPANY => '审核中',
            LOAN_3300_PF_CREDIT_UPLOAD => '审核中',
            LOAN_3400_PF_CREDIT_SEND_INFO => '审核中',
            LOAN_3500_PF_CREDIT_LOAN_APPLY => '审核中',
            LOAN_3600_PF_CREDIT_UPLOAD_SUCCESS => '审核中',
            LOAN_3700_PF_CREDIT_SEND_INFO_SUCCESS => '审核中',
            LOAN_4200_DATA_P2P_SEND => '审核中',
            LOAN_4000_P2P_CONFIRM => '等待上课放款确认',
            LOAN_4100_P2P_REFUSE => '审核拒绝',
            LOAN_4300_SUPPLEMENT => '补充信息',
            LOAN_4400_P2P_AUTO_PASS => '审核通过',
            LOAN_4500_STUDENT_SURE => '学员确认',
            LOAN_5000_SCHOOL_BEGIN => '机构确认上课',
            LOAN_5100_SCHOOL_REFUSE => '已终止',
            LOAN_5200_SCHOOL_STOP => '已终止',
            LOAN_5300_SCHOOL_PAUSE => '已终止',
            LOAN_5400_CHANGE_RESOURCE => '已终止',
            LOAN_5500_PAY_TIME_OUT => '已终止',
            LOAN_6000_NOTICE_MONEY => '等待放款',
            LOAN_6300_SUPPLY_INFO => '补充资料',
            LOAN_10000_REPAY => '还款中',
            LOAN_10100_REFUSE => '放款拒绝',
            LOAN_10200_REVOCATION => '放款撤销',
            LOAN_11000_FINISH => '已结清',
            LOAN_11100_OVERDUE => '逾期中',
            LOAN_11500_BAD => '坏账',
            LOAN_12000_DROP => '退课',
            LOAN_13000_EARLY_FINISH => '提前还款',
            LOAN_14000_FOREVER_REFUSE => '永久拒绝',
        ];
        if ($status === null) {
            return $info;
        } else if (array_key_exists($status, $info)) {
            return $info[$status];
        } else {
            return self::NOT_FOUND;
        }

    }

    public static function getStatusDescriptionForAdmin($status = null)
    {
        $info = [
            LOAN_1000_CREATE => '创建订单成功',
            LOAN_1100_CREATE_ACCOUNT => '申请成功',
            LOAN_1200_SURE_FILE => '确认合同',
            LOAN_2000_SCHOOL_CONFIRM => '机构确认',
            LOAN_2100_SCHOOL_REFUSE => '机构拒绝',
            LOAN_3000_PF_CONFIRM => '大圣已通过',
            LOAN_3100_PF_REFUSE => '大圣已拒绝',
            LOAN_3200_PF_CREDIT_COMPANY => '机构查询异常',
            LOAN_3300_PF_CREDIT_UPLOAD => '上传文件异常',
            LOAN_3400_PF_CREDIT_SEND_INFO => '提交信息异常',
            LOAN_3500_PF_CREDIT_LOAN_APPLY => '分期申请异常',
            LOAN_3600_PF_CREDIT_UPLOAD_SUCCESS => '上传文件成功',
            LOAN_3700_PF_CREDIT_SEND_INFO_SUCCESS => '提交信息成功',
            LOAN_4200_DATA_P2P_SEND => '资方审核中',
            LOAN_4000_P2P_CONFIRM => '资方通过',
            LOAN_4100_P2P_REFUSE => '资方拒绝',
            LOAN_4300_SUPPLEMENT => '信息重新补充',
            LOAN_4400_P2P_AUTO_PASS => '富登夜间自动审核通过',
            LOAN_4500_STUDENT_SURE => '学员确认',
            LOAN_5000_SCHOOL_BEGIN => '机构确认上课',
            LOAN_5100_SCHOOL_REFUSE => '机构取消',
            LOAN_5200_SCHOOL_STOP => '停止分期',
            LOAN_5300_SCHOOL_PAUSE => '暂停分期',
            LOAN_5400_CHANGE_RESOURCE => '该订单转资金方',
            LOAN_5500_PAY_TIME_OUT => '暂停分期',
            LOAN_6000_NOTICE_MONEY => '分期合同',
            LOAN_6300_SUPPLY_INFO => '晋商、补充资料',
            LOAN_10000_REPAY => '还款中',
            LOAN_10100_REFUSE => '放款拒绝',
            LOAN_10200_REVOCATION => '放款撤销',
            LOAN_11000_FINISH => '已结清',
            LOAN_11100_OVERDUE => '已逾期',
            LOAN_11500_BAD => '坏账',
            LOAN_12000_DROP => '退课',
            LOAN_13000_EARLY_FINISH => '提前还款',
            LOAN_14000_FOREVER_REFUSE => '永久拒绝',
        ];
        if ($status === null) {
            return $info;
        } else if (array_key_exists($status, $info)) {
            return $info[$status];
        } else {
            return self::NOT_FOUND;
        }
    }
}
