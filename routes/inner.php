<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/1/22
 * Time: 10:54 AM
 */

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
 * 富登接口
 */
Route::match(['get', 'post'], '/tp/fcs/updatestatus', '\App\Models\Fcs\FcsController@updateStatus');
Route::match(['get', 'post'], '/tp/fcs/uploadcontract', '\App\Models\Fcs\FcsController@uploadContract');
Route::match(['get', 'post'], '/tp/fcs/initqueue', '\App\Models\Fcs\FcsController@initQueue');