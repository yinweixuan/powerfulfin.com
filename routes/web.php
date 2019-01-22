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


Route::group(['domian' => DOMAIN_APP], function () {
    include __DIR__ . '/app.php';
});

Route::group(['domian' => DOMAIN_INNER], function () {
    include __DIR__ . '/inner.php';
});
