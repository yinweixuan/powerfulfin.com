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
