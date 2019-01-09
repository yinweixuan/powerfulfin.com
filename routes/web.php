<?php

/*
  |--------------------------------------------------------------------------
  | Web Routes
  |--------------------------------------------------------------------------
  |
  | Here is where you can register web routes for your application. These
  | routes are loaded by the RouteServiceProvider within a group which
  | contains the "web" middleware group. Now create something great!
  |
 */

/**
 * 官网
 */
Route::get('/', 'HomeController@index');

/**
 * 下载页
 */
Route::get('download', 'HomeController@download');

Route::match(['get', 'post'], '/home/qrscan', 'HomeController@qrscan');       //二维码扫描申请

/**
 * 回调路由
 */
Route::group(['namespace' => 'Inner'], function () {
    /**
     * 有盾回调接口
     */
    Route::match(['get', 'post'], '/inner/udcredit/notify', 'UdcreditController@notify');
});


/**
 * APP v1版本路由
 */
define('APP_V1', '/app/v1');

Route::group(['namespace' => 'App\V1'], function () {
    // Controllers Within The "App\Http\Controllers\APP\V1" Namespace
    /**
     * 首页
     */
    Route::match(['get', 'post'], APP_V1 . '/index/index', 'IndexController@index');

    /**
     * 登录接口
     */
    Route::match(['get', 'post'], APP_V1 . '/login/login', 'LoginController@login');

    /**
     * 登出接口
     */
    Route::match(['get', 'post'], APP_V1 . '/logout', 'LoginController@logout');

    /**
     * 获取短信验证码
     */
    Route::match(['get', 'post'], APP_V1 . '/login/verifycode', 'LoginController@verifycode');
    /**
     * 更改密码
     */
    Route::match(['get', 'post'], APP_V1 . '/login/setpassword', 'LoginController@setPassword');

    /**
     * 获取用户资料配置
     */
    Route::match(['get', 'post'], APP_V1 . '/user/uconfig', 'UserController@uconfig');  //获取用户配置项
    Route::match(['get', 'post'], APP_V1 . '/user/userreal', 'UserController@userReal');    //用户实名认证接口
    Route::match(['get', 'post'], APP_V1 . '/user/usercontact', 'UserController@userContact');    //用户联系信息接口
    Route::match(['get', 'post'], APP_V1 . '/user/userwork', 'UserController@userWork');    //用户工作&学历信息接口
    Route::match(['get', 'post'], APP_V1 . '/user/userlocation', 'UserController@userLocation');    //用户设备授权接口
    Route::match(['get', 'post'], APP_V1 . '/user/phonebook', 'UserController@phonebook');    //提交通讯录
    Route::match(['get', 'post'], APP_V1 . '/user/idcardpic', 'UserController@idcardpic');    //根据云慧眼认证成功的order拉取身份证图片
    Route::match(['get', 'post'], APP_V1 . '/user/getuserrealinfo', 'UserController@getUserRealInfo');    //用户实名信息数据
    Route::match(['get', 'post'], APP_V1 . '/user/getusercontact', 'UserController@getUserContact');    //用户联系人数据
    Route::match(['get', 'post'], APP_V1 . '/user/getuserwork', 'UserController@getUserWork');    //用户工作信息数据
    Route::match(['get', 'post'], APP_V1 . '/user/userstatus', 'UserController@userstatus');    //各项认证状态


    /**
     * 地址选择器
     */
    Route::match(['get', 'post'], APP_V1 . '/area/province', 'AreaController@province');
    Route::match(['get', 'post'], APP_V1 . '/area/city', 'AreaController@city');
    Route::match(['get', 'post'], APP_V1 . '/area/area', 'AreaController@area');

    /**
     * 搜索机构
     */
    Route::match(['get', 'post'], APP_V1 . '/search/school', 'SearchController@school');

    /**
     * 银行卡相关
     */
    Route::match(['get', 'post'], APP_V1 . '/bank/sms', 'BankController@sms');      //获取签约短息
    Route::match(['get', 'post'], APP_V1 . '/bank/bind', 'BankController@bind');    //签约
    Route::match(['get', 'post'], APP_V1 . '/bank/banks', 'BankController@banks');  //拉取用户银行卡列表
    Route::match(['get', 'post'], APP_V1 . '/bank/change', 'BankController@change');  //切换用户划扣卡

    /**
     * 申请分期
     */
    Route::match(['get', 'post'], APP_V1 . '/loan/config', 'LoanController@loanConfig');  //获取分期申请配置项
    Route::match(['get', 'post'], APP_V1 . '/loan/submit', 'LoanController@loanSubmit');  //申请分期
    Route::match(['get', 'post'], APP_V1 . '/loan/calc', 'LoanController@calc');  //申请分期
    /**
     * 订单信息
     */
    Route::match(['get', 'post'], APP_V1 . '/loan/list', 'LoanController@loanList');  //获取订单列表
    Route::match(['get', 'post'], APP_V1 . '/loan/info', 'LoanController@loanInfo');  //获取订单信息
    Route::match(['get', 'post'], APP_V1 . '/loan/loanbill', 'LoanController@loanBill');  //获取还款计划表

    /**
     * 图片
     */
    Route::match(['get', 'post'], APP_V1 . '/pic/upload', 'PicController@upload');  //app图片上传
    Route::match(['get', 'post'], APP_V1 . '/test/index', 'TestController@index');  //app图片上传
});

/**
 * ios审核相关
 */
//报名接口
Route::match(['get', 'post'], '/ios/apply', 'App\V1\IosAuditController@apply');
//报名列表接口
Route::match(['get', 'post'], '/ios/applylist', 'App\V1\IosAuditController@applyList');
//课程列表接口
Route::match(['get', 'post'], '/ios/classlist', 'App\V1\IosAuditController@classList');