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
    public static function getStatusDescriptionForC($status)
    {
        $info = [
            LOAN_1000_CREATE => '创建贷款申请成功',
            LOAN_1100_CREATE_ACCOUNT => '创建账号成功',
            LOAN_1200_SURE_FILE => '确认合同',
            LOAN_2000_SCHOOL_CONFIRM => '机构确认',
            LOAN_2100_SCHOOL_REFUSE => '机构拒绝',
            LOAN_3000_KZ_CONFIRM => '课栈已通过',
            LOAN_3100_KZ_REFUSE => '课栈已拒绝',
            LOAN_3200_KZ_CREDIT_COMPANY => '课栈脚本问题--机构查询',
            LOAN_3300_KZ_CREDIT_UPLOAD => '课栈脚本问题--上传文件',
            LOAN_3400_KZ_CREDIT_SEND_INFO => '课栈脚本问题--提交信息',
            LOAN_3500_KZ_CREDIT_LOAN_APPLY => '课栈脚本问题--贷款申请',
            LOAN_3600_KZ_CREDIT_UPLOAD_SUCCESS => '课栈脚本成功--上传文件',
            LOAN_3700_KZ_CREDIT_SEND_INFO_SUCCESS => '课栈脚本成功--提交信息',
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
            LOAN_11100_OVERDUE_KZ => '还款逾期,课栈催缴',
            LOAN_11200_OVERDUE_P2P => '还款逾期,P2P催缴',
            LOAN_11500_BAD => '坏账,终止',
            LOAN_12000_DROP => '退课',
            LOAN_13000_EARLY_FINISH => '提前还款',
            LOAN_14000_FOREVER_REFUSE => '永久拒绝',
        ];
        if (array_key_exists($status, $info)) {
            return $info[$status];
        } else {
            return "未知";
        }
    }

    public static function getStatusDescriptionForB($status)
    {
        $info = [
            LOAN_1000_CREATE => '创建贷款申请成功',
            LOAN_1100_CREATE_ACCOUNT => '创建账号成功',
            LOAN_1200_SURE_FILE => '确认合同',
            LOAN_2000_SCHOOL_CONFIRM => '机构确认',
            LOAN_2100_SCHOOL_REFUSE => '机构拒绝',
            LOAN_3000_KZ_CONFIRM => '课栈已通过',
            LOAN_3100_KZ_REFUSE => '课栈已拒绝',
            LOAN_3200_KZ_CREDIT_COMPANY => '课栈脚本问题--机构查询',
            LOAN_3300_KZ_CREDIT_UPLOAD => '课栈脚本问题--上传文件',
            LOAN_3400_KZ_CREDIT_SEND_INFO => '课栈脚本问题--提交信息',
            LOAN_3500_KZ_CREDIT_LOAN_APPLY => '课栈脚本问题--贷款申请',
            LOAN_3600_KZ_CREDIT_UPLOAD_SUCCESS => '课栈脚本成功--上传文件',
            LOAN_3700_KZ_CREDIT_SEND_INFO_SUCCESS => '课栈脚本成功--提交信息',
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
            LOAN_11100_OVERDUE_KZ => '还款逾期,课栈催缴',
            LOAN_11200_OVERDUE_P2P => '还款逾期,P2P催缴',
            LOAN_11500_BAD => '坏账,终止',
            LOAN_12000_DROP => '退课',
            LOAN_13000_EARLY_FINISH => '提前还款',
            LOAN_14000_FOREVER_REFUSE => '永久拒绝',
        ];
        if (array_key_exists($status, $info)) {
            return $info[$status];
        } else {
            return "未知";
        }

    }

    public static function getStatusDescriptionForAdmin($status)
    {
        $info = [
            LOAN_1000_CREATE => '创建贷款申请成功',
            LOAN_1100_CREATE_ACCOUNT => '创建账号成功',
            LOAN_1200_SURE_FILE => '确认合同',
            LOAN_2000_SCHOOL_CONFIRM => '机构确认',
            LOAN_2100_SCHOOL_REFUSE => '机构拒绝',
            LOAN_3000_KZ_CONFIRM => '课栈已通过',
            LOAN_3100_KZ_REFUSE => '课栈已拒绝',
            LOAN_3200_KZ_CREDIT_COMPANY => '课栈脚本问题--机构查询',
            LOAN_3300_KZ_CREDIT_UPLOAD => '课栈脚本问题--上传文件',
            LOAN_3400_KZ_CREDIT_SEND_INFO => '课栈脚本问题--提交信息',
            LOAN_3500_KZ_CREDIT_LOAN_APPLY => '课栈脚本问题--贷款申请',
            LOAN_3600_KZ_CREDIT_UPLOAD_SUCCESS => '课栈脚本成功--上传文件',
            LOAN_3700_KZ_CREDIT_SEND_INFO_SUCCESS => '课栈脚本成功--提交信息',
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
            LOAN_11100_OVERDUE_KZ => '还款逾期,课栈催缴',
            LOAN_11200_OVERDUE_P2P => '还款逾期,P2P催缴',
            LOAN_11500_BAD => '坏账,终止',
            LOAN_12000_DROP => '退课',
            LOAN_13000_EARLY_FINISH => '提前还款',
            LOAN_14000_FOREVER_REFUSE => '永久拒绝',
        ];
        if (array_key_exists($status, $info)) {
            return $info[$status];
        } else {
            return "未知";
        }
    }
}
