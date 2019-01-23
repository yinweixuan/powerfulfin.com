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

if ($_SERVER['SERVER_NAME'] == DOMAIN_WWW) {
    /**
     * 官网
     */
    Route::get('/', 'HomeController@index');

    /**
     * 下载页
     */
    Route::get('download', 'HomeController@download');

    Route::match(['get', 'post'], '/home/qrscan', 'HomeController@qrscan');       //二维码扫描申请
}


if ($_SERVER['SERVER_NAME'] == DOMAIN_APP) {
    Route::group(['domain' => DOMAIN_APP], function () {
        include __DIR__ . '/app.php';
    });
}

if ($_SERVER['SERVER_NAME'] == DOMAIN_INNER) {
    Route::group(['domain' => DOMAIN_INNER], function () {
        include __DIR__ . '/inner.php';
    });
}

