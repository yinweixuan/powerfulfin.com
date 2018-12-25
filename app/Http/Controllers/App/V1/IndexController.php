<?php

/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 3:38 PM
 */

namespace App\Http\Controllers\App\V1;

use App\Http\Controllers\App\AppController;
use App\Components\OutputUtil;
use App\Http\Controllers\App\Models\Loan;

class IndexController extends AppController
{

    public function index()
    {
        $data = [];
        $data['banner'] = [
            [
                'img' => '/img/banner/banner1.png',
                'url' => 'http://www.baidu.com',
            ],
            [
                'img' => '/img/banner/banner2.png',
                'url' => 'http://www.baidu.com',
            ],
        ];
        $data['customer_service'] = [
            'phone' => '4000029691',
            'email' => '123123@powerfulfin.com'
        ];
        $data['notice'] = [
            'content' => '这是通知，就一条',
            'url' => 'powerfulfin://loandetail?lid=123',
        ];
        $data['loan'] = Loan::getHomeLoanInfo(1);   //需提交UID,如果是未登录状态则返回空
        OutputUtil::out($data);
    }

}
