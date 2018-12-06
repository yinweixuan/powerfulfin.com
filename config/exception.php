<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 3:20 PM
 */
define('ALARM_MAIL_RECEIVER', 'runnysky@qq.com; ');
define('ALARM_SMS_RECEIVER', '13426386028@qq.com');

/**
 * 错误等级定义
 */
define('EXCEPTION_NOTICE', 0);          //静默方式处理异常,只记录日志
define('EXCEPTION_FATAL', 1);           //严重错误,直接终止
define('EXCEPTION_WARN', 2);            //当前功能点不可用,但不影响其他的功能

define('ERR_OK', 0);
define('ERR_OK_CONTENT', '操作成功');
/**
 * 系统错误码,1~199
 */
/**
 * 系统未知错误
 */
define('ERR_SYS_UNKNOWN', 1);
define('ERR_SYS_UNKNOWN_CONTENT', '系统未知错误！');
/**
 * 数据库错误
 */
define('ERR_SYS_DB', 2);
define('ERR_SYS_DB_CONTENT', "数据库异常");
/**
 * 参数错误
 */
define('ERR_SYS_PARAM', 3);
define('ERR_SYS_PARAM_CONTENT', '参数错误');
/**
 * 未登录
 */
define('ERR_NOLOGIN', 4);
define('ERR_NOLOGIN_CONTENT', '您未登录，请先登录！');
/**
 * 注册用户失败
 */
define('ERR_REGISTER', 5);
define('ERR_REGISTER_CONTENT', '注册失败，请稍后重试');
/**
 * 手机号格式异常
 */
define('ERR_PHONE_FORMAT', 6);
define('ERR_PHONE_FORMAT_CONTENT', '手机号格式异常');

define('ERR_VERIFY_CODE', 7);
define('ERR_VERIFY_CODE_CONTENT', '短信验证码异常');
