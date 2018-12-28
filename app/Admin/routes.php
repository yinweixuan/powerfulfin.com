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
    $router->get('/users/banks', 'UsersController@banks');

    /**
     * 机构管理
     */
    $router->get('/org/index', 'OrgController@index');
    $router->get('/org/head', 'OrgController@head');
    $router->get('/org/class', 'OrgController@class');
    $router->get('/org/users', 'OrgController@users');

});
