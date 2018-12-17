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
 * 回调路由
 */
Route::group(['namespace' => 'Inner'], function () {
    /**
     * 有盾回调接口
     */
    Route::match(['post'], '/udcredit/notify', 'UdcreditController@notify');
});


/**
 * APP v1版本路由
 */

define('APP_V1', '/app/v1');

Route::group(['namespace' => 'APP\V1'], function () {
    // Controllers Within The "App\Http\Controllers\APP\V1" Namespace
    /**
     * 首页
     */
    Route::match(['get', 'post'], APP_V1 . '/index/index', 'IndexController@index');

    /**
     * 登录接口
     */
    Route::match(['get', 'post'], APP_V1 . '/login/login', 'LoginController@login');
});
