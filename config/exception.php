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

define('ERR_VCODE_CHECK', 8);
define('ERR_VCODE_CHECK_CONTENT', '验证码校验失败');

define('ERR_VCODE_CREATE', 9);
define('ERR_VCODE_CREATE_CONTENT', "验证码创建失败");

define('ERR_SMS_FAIL', 10);
define('ERR_SMS_FAIL_CONTENT', "短信发送失败");

define('ERR_SMS_MOBILE_FORMAT', 11);
define('ERR_SMS_MOBILE_FORMAT_CONTENT', "手机号错误");

define('ERR_SMS_CODE_FORMAT', 12);
define('ERR_SMS_CODE_FORMAT_CONTENT', '验证码错误');

define('ERR_AREA', 13);
define('ERR_AREA_CONTENT', '暂未获取地址数据，请稍后重试');

define('ERR_LOGIN', 14);
define('ERR_LOGIN_CONTENT', '登录失败，用户名或密码错误');

define('ERR_USER_EXIST', 15);
define('ERR_USER_EXIST_CONTENT', '您未设置密码，请使用短信验证码登录');

define('ERR_PASSWORD', 16);
define('ERR_PASSWORD_CONTENT', '密码错误');

define('ERR_PASSWORD_FORMAT', 17);
define('ERR_PASSWORD_FORMAT_CONTENT', '密码格式错误');

define('ERR_LOAN_COLLECT_MOBILE', 18);
define('ERR_LOAN_COLLECT_MOBILE_CONTENT', '获取用户通讯录失败');

/**
 * 队列,2201~2299
 */
define('ERR_QUEUE_CREATE_CLIENT', 2201);       //创建连接失败
define('ERR_QUEUE_CREATE_QUEUE', 2202);       //创建队列失败
define('ERR_QUEUE_DEL_QUEUE', 2203);       //删除队列失败
define('ERR_QUEUE_GET_QUEUE', 2211);       //获取队列失败
define('ERR_QUEUE_SEND_MSG', 2212);       //发送消息失败
define('ERR_QUEUE_REV_MSG', 2213);       //接收消息失败
define('ERR_QUEUE_DEL_MSG', 2214);       //删除消息失败
define('ERR_QUEUE_QUEUE_EMPTY', 2215);       //消息队列为空
