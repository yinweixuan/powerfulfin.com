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
 * 机构访问
 */
Route::group(['namespace' => 'Org'], function () {
    Route::match(['get', 'post'], '/', 'HomeController@index');                 //根域名
    Route::match(['get', 'post'], '/home/index', 'HomeController@index');       //首页
    Route::match(['get', 'post'], '/home/login', 'HomeController@login');       //登录
    Route::match(['get', 'post'], '/home/logout', 'HomeController@logout');       //登出
    Route::match(['get', 'post'], '/home/msglist', 'HomeController@msglist');       //站内信
    Route::match(['get', 'post'], '/home/faq', 'HomeController@faq');       //常见问题

    //订单相关
    Route::match(['get', 'post'], '/order/bookinglist', 'OrderController@bookinglist');       //报名列表
    Route::match(['get', 'post'], '/order/confirmlist', 'OrderController@confirmlist');       //确认上课列表
    Route::match(['get', 'post'], '/order/operate', 'OrderController@operate');       //订单审核

    //统计相关
    Route::match(['get', 'post'], '/stat/list', 'StatController@list');       //订单查询
    Route::match(['get', 'post'], '/stat/sumup', 'StatController@sumup');       //统计



    Route::match(['get', 'post'], '/test/test1', 'TestController@test1');       //测试
});

/**
 * 机构管理后台域名,o.powerfulfin.com
 */
Route::match(['get', 'post'], '/res/{img?}', function($img = '') {
    //专属素材应放在org目录下.其他public下的属于公用.
    $file = PATH_BASE . '/public/' . $img;
    if (!file_exists($file)) {
        header("HTTP/1.1 404 Not Found");
        header("Status: 404 Not Found");
        exit;
    }
    header('Content-type: image/jpg');
    echo file_get_contents($file);
    exit;
});

