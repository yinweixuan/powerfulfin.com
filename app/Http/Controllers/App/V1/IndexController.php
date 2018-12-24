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

class IndexController extends AppController {

    public function index() {
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
            'phone' => '4000029691'
        ];
        $data['notice'] = [
            'content' => '这是通知，就一条',
            'url' => 'powerfulfin://loandetail?lid=123',
        ];
        $data['loan'] = [
            'status' => '1',
            'status_img_2x' => '/img/loan/confirm2x.png',
            'status_img_3x' => '/img/loan/confirm3x.png',
            'status_desp' => '待确认',
            'repay_date' => '2019-01-15',
            'repay_money' => '1205.12',
            'remark' => '请确认分期',
            'buttons' => [
                [
                    'name' => '订单详情',
                    'url' => 'powerfulfin://loandetail?lid=123',
                    'style' => '1'
                ],
                [
                    'name' => '分期确认',
                    'url' => 'powerfulfin://loanconfirm?lid=123',
                    'style' => '2'
                ]
            ],
            'school_id' => '123456',
            'school_name' => '恒企教育（东风北桥分校）',
        ];
        OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $data);
    }

}
