<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/22
 * Time: 10:23 AM
 */


define('LOAN_1000_CREATE', 1000);               //创建贷款申请成功
define('LOAN_1100_CREATE_ACCOUNT', 1100);       //创建账号成功
define('LOAN_1200_SURE_FILE', 1200);            //确认合同

define('LOAN_2000_SCHOOL_CONFIRM', 2000);       //机构确认
define('LOAN_2100_SCHOOL_REFUSE', 2100);        //机构拒绝

define('LOAN_3000_KZ_CONFIRM', 3000);           //课栈已通过
define('LOAN_3100_KZ_REFUSE', 3100);            //课栈已拒绝
define('LOAN_3200_KZ_CREDIT_COMPANY', 3200);    //课栈脚本问题--机构查询
define('LOAN_3300_KZ_CREDIT_UPLOAD', 3300);     //课栈脚本问题--上传文件
define('LOAN_3400_KZ_CREDIT_SEND_INFO', 3400);  //课栈脚本问题--提交信息
define('LOAN_3500_KZ_CREDIT_LOAN_APPLY', 3500); //课栈脚本问题--贷款申请
define('LOAN_3600_KZ_CREDIT_UPLOAD_SUCCESS', 3600); //课栈脚本成功--上传文件
define('LOAN_3700_KZ_CREDIT_SEND_INFO_SUCCESS', 3700);  //课栈脚本成功--提交信息

define('LOAN_4200_DATA_P2P_SEND', 4200);        //已经将审核信息发给P2P
define('LOAN_4000_P2P_CONFIRM', 4000);          //P2P已通过
define('LOAN_4100_P2P_REFUSE', 4100);           //P2P已拒绝
define('LOAN_4300_SUPPLEMENT', 4300);           //信息重新补充
define('LOAN_4400_P2P_AUTO_PASS', 4400);        //富登夜间自动审核通过
define('LOAN_4500_STUDENT_SURE', 4500);         //学员确认

define('LOAN_5000_SCHOOL_BEGIN', 5000);         //机构确认已经来上课
define('LOAN_5100_SCHOOL_REFUSE', 5100);        //机构确认不来上课
define('LOAN_5200_SCHOOL_STOP', 5200);          //机构确认停止贷款
define('LOAN_5300_SCHOOL_PAUSE', 5300);         //机构确认暂停贷款
define('LOAN_5400_CHANGE_RESOURCE', 5400);      //该订单转资金方
define('LOAN_5500_PAY_TIME_OUT', 5500);         //机构确认暂停贷款

define('LOAN_6000_NOTICE_MONEY', 6000);         //已经发起上标的
define('LOAN_6300_SUPPLY_INFO', 6300);          //晋商、补充资料


define('LOAN_10000_REPAY', 10000);              //机构确认收到款,学生还款中
define('LOAN_10100_REFUSE', 10100);             //放款拒绝
define('LOAN_10200_REVOCATION', 10200);         //放款撤销
define('LOAN_11000_FINISH', 11000);             //还款已结束
define('LOAN_11100_OVERDUE', 11100);         //还款逾期,课栈催缴
define('LOAN_11200_OVERDUE_P2P', 11200);        //还款逾期,P2P催缴
define('LOAN_11500_BAD', 11500);                //坏账,终止
define('LOAN_12000_DROP', 12000);               //退课
define('LOAN_13000_EARLY_FINISH', 13000);       //提前还款
define('LOAN_14000_FOREVER_REFUSE', 14000);     //永久拒绝


define('BILL_STATUS_DOING', 0);        //账期待还
define('BILL_STATUS_DONE', 1);          //已还
define('BILL_STATUS_OVERDUE', 2);       //逾期
define('BILL_STATUS_EARLY', 3);         //提前还款
define('BILL_STATUS_DROP', 4);          //退课
