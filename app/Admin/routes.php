<?php

use Illuminate\Routing\Router;

Admin::registerAuthRoutes();

Route::group([
    'prefix' => config('admin.route.prefix'),
    'namespace' => config('admin.route.namespace'),
    'middleware' => config('admin.route.middleware'),
], function (Router $router) {

    $router->any('/', 'HomeController@index');
    $router->any('/home/success', 'HomeController@success');
    $router->any('/home/err', 'HomeController@err');
    /**
     * 用户管理
     */
    $router->any('/users/index', 'UsersController@index');
    $router->any('/users/real', 'UsersController@real');
    $router->any('/users/banks', 'UsersController@banks');

    /**
     * 商户管理
     */
    $router->any('/org/head', 'OrgController@head');
    $router->any('/org/addhead', 'OrgController@addhead');
    $router->any('/org/edithead', 'OrgController@edithead');
    $router->any('/org/headinfo', 'OrgController@headinfo');

    /**
     * 分校管理
     */
    $router->any('/org/index', 'OrgController@index');
    $router->any('/org/addorg', 'OrgController@addorg');
    $router->any('/org/editorg', 'OrgController@editorg');

    /**
     * 课程管理
     */
    $router->any('/org/class', 'OrgController@class');
    $router->any('/org/addorgclass', 'OrgController@addorgclass');
    $router->any('/org/editclass', 'OrgController@editclass');

    /**
     * 校区管理员
     */
    $router->any('/org/users', 'OrgController@users');
    $router->any('/org/adduser', 'OrgController@adduser');

    /**
     * 订单管理
     */
    $router->any('/loan/index', 'LoanController@index');    //订单列表
    $router->any('/loan/info', 'LoanController@info');  //订单信息
    $router->any('/loan/bill', 'LoanController@bill');  //还款计划表
    $router->any('/loan/contract', 'LoanController@contract');  //合同下载
    $router->any('/loan/tools', 'LoanController@tools');    //订单工具
    $router->any('/tools/loanstatus', 'ToolsController@loanStatus');    //工具类：更新订单状态
    $router->any('/loan/summary', 'LoanController@summary');  //订单汇总

    /**
     * 后台公用地址选择器
     */
    $router->any('/area/province', 'AreaController@province');
    $router->any('/area/city', 'AreaController@city');
    $router->any('/area/area', 'AreaController@area');

    /**
     * 风控
     */
    $router->any('/verify/lists', 'VerifyController@lists');
    $router->any('/verify/collect', 'VerifyController@collect');
    $router->any('/verify/info', 'VerifyController@info');
    $router->any('/verify/verify', 'VerifyController@verify');
    $router->any('/verify/check', 'VerifyController@check');

    /**
     * 贷后管理
     */
    $router->any('/repay/lists', 'RepayController@lists');
    $router->any('/repay/overdue', 'RepayController@overdue');

    /**
     * 消息管理
     */
    $router->any('/msg/smslists', 'MsgController@smslists');
});
