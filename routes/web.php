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

use Illuminate\Support\Facades\Redirect;

if ($_SERVER['SERVER_NAME'] == DOMAIN_WWW) {

    if (strpos($_SERVER['REQUEST_URI'], '/admin') !== false) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('请检查访问路径');
    }

    /**
     * 官网
     */
    Route::get('/', 'HomeController@index');

    /**
     * 下载页
     */
    Route::get('download', 'HomeController@download');
    Route::get('downloadpackage', 'HomeController@downloadPackage');

    Route::match(['get', 'post'], '/home/qrscan', 'HomeController@qrscan');       //二维码扫描申请
}


if ($_SERVER['SERVER_NAME'] == DOMAIN_APP) {
    Route::group(['domain' => DOMAIN_APP], function () {
        include __DIR__ . '/app.php';
    });
}

if ($_SERVER['SERVER_NAME'] == DOMAIN_INNER) {
    if (strpos($_SERVER['REQUEST_URI'], '/admin') !== false) {
        throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('请检查访问路径');
    }
    Route::group(['domain' => DOMAIN_INNER], function () {
        include __DIR__ . '/inner.php';
    });
}

if ($_SERVER['SERVER_NAME'] == DOMAIN_ADMIN) {
    if (strpos($_SERVER['REQUEST_URI'], '/admin') === false) {
        Route::get('/', function () {
            return Redirect::to('admin/auth/login');
        });
    }
}

