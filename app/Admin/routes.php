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
    $router->any('/org/addhead', 'OrgController@addhead');
    $router->any('/org/edithead', 'OrgController@edithead');
    $router->any('/org/addorg', 'OrgController@addorg');
    $router->any('/org/addorgclass', 'OrgController@addorgclass');
    $router->get('/org/class', 'OrgController@class');
    $router->get('/org/users', 'OrgController@users');

    /**
     * 订单管理
     */
    $router->get('/loan/index', 'LoanController@index');
    $router->get('/loan/info', 'LoanController@info');

    /**
     * 后台公用地址选择器
     */
    $router->any('/area/province', 'AreaController@province');
    $router->any('/area/city', 'AreaController@city');
    $router->any('/area/area', 'AreaController@area');
});
