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
            LOAN_3000_PF_CONFIRM => '课栈已通过',
            LOAN_3100_PF_REFUSE => '课栈已拒绝',
            LOAN_3200_PF_CREDIT_COMPANY => '课栈脚本问题--机构查询',
            LOAN_3300_PF_CREDIT_UPLOAD => '课栈脚本问题--上传文件',
            LOAN_3400_PF_CREDIT_SEND_INFO => '课栈脚本问题--提交信息',
            LOAN_3500_PF_CREDIT_LOAN_APPLY => '课栈脚本问题--贷款申请',
            LOAN_3600_PF_CREDIT_UPLOAD_SUCCESS => '课栈脚本成功--上传文件',
            LOAN_3700_PF_CREDIT_SEND_INFO_SUCCESS => '课栈脚本成功--提交信息',
            LOAN_4200_DATA_P2P_SEND => '已经将审核信息发给P2P',
            LOAN_4000_P2P_CONFIRM => 'P2P已通过',
            LOAN_4100_P2P_REFUSE => 'P2P已拒绝',
            LOAN_4300_SUPPLEMENT => '信息重新补充',
            LOAN_4400_P2P_AUTO_PASS => '富登夜间自动审核通过',
            LOAN_4500_STUDENT_SURE => '学员确认',
            LOAN_5000_SCHOOL_BEGIN => '机构确认已经来上课',
            LOAN_5100_SCHOOL_REFUSE => '机构确认不来上课',
            LOAN_5200_SCHOOL_STOP => '机构确认停止贷款',
            LOAN_5300_SCHOOL_PAUSE => '机构确认暂停贷款',
            LOAN_5400_CHANGE_RESOURCE => '该订单转资金方',
            LOAN_5500_PAY_TIME_OUT => '机构确认暂停贷款',
            LOAN_6000_NOTICE_MONEY => '已经发起上标的',
            LOAN_6300_SUPPLY_INFO => '晋商、补充资料',
            LOAN_10000_REPAY => '机构确认收到款,学生还款中',
            LOAN_10100_REFUSE => '放款拒绝',
            LOAN_10200_REVOCATION => '放款撤销',
            LOAN_11000_FINISH => '还款已结束',
            LOAN_11100_OVERDUE => '还款逾期,课栈催缴',
            LOAN_11500_BAD => '坏账,终止',
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
