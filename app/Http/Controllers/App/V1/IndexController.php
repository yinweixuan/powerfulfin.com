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
                'title' => 'this is banner 1',
                'img' => '/img/banner/banner1.png',
                'url' => 'http://www.baidu.com',
            ],
            [
                'title' => 'this is banner 2',
                'img' => '/img/banner/banner2.png',
                'url' => 'http://www.baidu.com',
            ],
        ];
        $data['customer_service'] = [
            'phone' => '4000029691'
        ];
        $data['notice'] = [
            [
                'content' => '这是第一条通知'
            ],
            [
                'content' => '这是第二条通知'
            ],
        ];
        $data['loan'] = [
            'status' => '1',
            'status_img' => '/img/loan/audit.png',
            'status_desp' => '审核中',
            'repay_date' => '2019-01-15',
            'repay_money' => '1205.12',
            'remark' => '您的贷款正在审核中，请耐心等待',
        ];
        $data['recommend'] = [
            'school_id' => '123456',
            'school_name' => '恒企教育（东风北桥分校）',
        ];
        OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $data);
    }

}
