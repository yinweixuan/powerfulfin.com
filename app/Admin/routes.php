<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix' => config('admin.route.prefix'),
    'namespace' => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->get('/', 'HomeController@index');
    /**
     * 用户管理
     */
    $router->get('/users/index', 'UsersController@index');
    $router->get('/users/real', 'UsersController@real');

    /**
     * 机构管理
     */
    $router->get('/org/index', 'OrgController@index');

});
