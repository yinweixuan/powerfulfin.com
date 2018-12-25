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
use App\Components\AliyunOpenSearchUtil;

class IndexController extends AppController {

    public function index() {
        $data = [];
        $data['audit'] = $this->getAppStoreAudit();
        $data['banner'] = $this->getBanner();
        $data['customer_service'] = $this->getCustomerService();
        $data['notice'] = $this->getNotice();
        $data['loan'] = $this->getLoan();
        OutputUtil::out($data);
    }

    public function getBanner() {
        return [
            [
                'img' => '/img/banner/banner1.png',
                'url' => 'http://www.baidu.com',
            ],
            [
                'img' => '/img/banner/banner2.png',
                'url' => 'http://www.baidu.com',
            ],
        ];
    }

    public function getCustomerService() {
        return [
            'phone' => '4000029691',
            'email' => '123123@powerfulfin.com'
        ];
    }

    public function getNotice() {
        return [
            'content' => '这是通知，就一条',
            'url' => 'powerfulfin://loandetail?lid=123',
        ];
    }

    public function getLoan() {
        //保证字段全
        $data = [
            'status' => '',
            'status_img_2x' => '',
            'status_img_3x' => '',
            'status_desp' => '',
            'repay_date' => '',
            'repay_money' => '',
            'is_overdue' => '',
            'remark' => '',
            'buttons' => [],
            'school_id' => '',
            'school_name' => '',
        ];
        $loan = [];
        $lid = 123;
        $step = mt_rand(0, 5);
        if ($step == 1) {
            //审核中
            $data_detail = [
                'status' => '1',
                'status_img_2x' => '/img/loan/audit2x.png',
                'status_img_3x' => '/img/loan/audit3x.png',
                'status_desp' => '审核中',
                'remark' => '分期正在审核中，请耐心等待',
                'buttons' => [
                    $this->getButton(1, $lid)
                ]
            ];
        } elseif ($step == 2) {
            //待确认
            $data_detail = [
                'status' => '1',
                'status_img_2x' => '/img/loan/confirm2x.png',
                'status_img_3x' => '/img/loan/confirm3x.png',
                'status_desp' => '待确认',
                'remark' => '请确认分期',
                'buttons' => [
                    $this->getButton(1, $lid),
                    $this->getButton(2, $lid)
                ]
            ];
        } elseif ($step == 3) {
            //已拒绝
            $data_detail = [
                'status' => '1',
                'status_img_2x' => '/img/loan/refused2x.png',
                'status_img_3x' => '/img/loan/refused3x.png',
                'status_desp' => '已拒绝',
                'remark' => '你的分期已被拒绝',
                'buttons' => [
                    $this->getButton(1, $lid)
                ]
            ];
        } elseif ($step == 4) {
            //已终止
            $data_detail = [
                'status' => '1',
                'status_img_2x' => '/img/loan/end2x.png',
                'status_img_3x' => '/img/loan/end3x.png',
                'status_desp' => '已终止',
                'remark' => '你的分期已终止',
                'buttons' => [
                    $this->getButton(1, $lid)
                ]
            ];
        } elseif ($step == 5) {
            //还款中
            $data_detail = [
                'status' => '2',
                'status_desp' => '已终止',
                'remark' => '您已逾期，请尽快偿还',
                'repay_date' => '2019-01-15',
                'repay_money' => '1205.12',
                'is_overdue' => mt_rand(0, 1),
                'buttons' => [
                    $this->getButton(1, $lid),
                    $this->getButton(3, $lid)
                ]
            ];
        } else {
            $data_detail = $this->getRecommend();
        }
        return array_merge($data, $data_detail);
    }

    public function getButton($type, $id = null) {
        if ($type == 1) {
            $button = [
                'name' => '订单详情',
                'url' => 'powerfulfin://loandetail?lid=' . $id,
                'style' => '1'
            ];
        } elseif ($type == 2) {
            $button = [
                'name' => '分期确认',
                'url' => 'powerfulfin://loanconfirm?lid=' . $id,
                'style' => 2
            ];
        } elseif ($type == 3) {
            $button = [
                'name' => '立即还款',
                'url' => 'powerfulfin://repay?lid=' . $id,
                'style' => 2
            ];
        } elseif ($type == 4) {
            $button = [
                'name' => '立即申请',
                'url' => 'powerfulfin://apply?lid=' . $id,
                'style' => 1
            ];
        } elseif ($type == 5) {
            $button = [
                'name' => '扫码申请',
                'url' => 'powerfulfin://qrapply',
                'style' => 2
            ];
        } else {
            $button = [];
        }
        return $button;
    }

    public function getRecommend() {
        $sid = mt_rand(0, 1) ? 0 : 123456;
        if ($sid) {
            $recommend = [
                'status' => '0',
                'school_id' => $sid,
                'school_name' => '恒企教育（东风北桥分校）',
                'status_img_2x' => '/img/loan/recommend2x.png',
                'status_img_3x' => '/img/loan/recommend3x.png',
                'buttons' => [
                    $this->getButton(4, $sid),
                    $this->getButton(5)
                ]
            ];
        } else {
            $recommend = [
                'status' => '0',
                'status_img_2x' => '/img/loan/noresult2x.png',
                'status_img_3x' => '/img/loan/noresult3x.png',
                'buttons' => [
                    $this->getButton(5)
                ]
            ];
        }
        return $recommend;
    }

    public function getAppStoreAudit() {
        $flag = mt_rand(0, 1);
        $list = [];
        if ($flag) {
            $data = AliyunOpenSearchUtil::searchSchool('恒企');
            if (!empty($data['list'])) {
                $list = [];
                foreach ($data['list'] as $item) {
                    $new_item = [];
                    $new_item['id'] = $item['id'];
                    $new_item['name'] = $item['name'];
                    $new_item['address'] = $item['address'];
                    $list[] = $new_item;
                }
            } else {
                $list = [
                    [
                        'id' => '300574',
                        'name' => '恒企教育（东莞长安分校）',
                        'address' => '东莞市长安镇长盛社区长中路118号名店大厦5楼503恒企教育'
                    ],
                    [
                        'id' => '300442',
                        'name' => '恒企教育（苏州高新校区）',
                        'address' => '苏州高新区长江路436号绿宝广场商务楼8009'
                    ],
                    [
                        'id' => '300028',
                        'name' => '恒企教育（江苏观前街校区）',
                        'address' => '苏州市平江区干将东路666号和基广场527室'
                    ],
                    [
                        'id' => '300268',
                        'name' => '恒企教育（泉州校区）',
                        'address' => '福建省泉州市丰泽区泉秀路882号大洋百货七楼'
                    ],
                    [
                        'id' => '300035',
                        'name' => '恒企教育（广西柳州柳江校区）',
                        'address' => '广西壮族自治区柳州市柳江县拉堡镇柳北路6号'
                    ],
                    [
                        'id' => '300031',
                        'name' => '恒企教育（昆明南亚校区）',
                        'address' => '昆明市日新西路英茂嘉园6栋3楼'
                    ],
                    [
                        'id' => '300446',
                        'name' => '恒企教育（广州江南西校区）',
                        'address' => '广州海珠区江南大道中路180号1104房'
                    ],
                    [
                        'id' => '300382',
                        'name' => '恒企教育（广州芳村校区）',
                        'address' => '广州市荔湾区芳村大道中169号二楼B07'
                    ],
                    [
                        'id' => '300027',
                        'name' => '恒企教育（广州番禺校区）',
                        'address' => '广州市番禺区市桥街富华西路35号华南大厦8楼810'
                    ],
                    [
                        'id' => '300403',
                        'name' => '恒企教育（柳州鹿寨校区）',
                        'address' => '鹿寨县鹿寨镇建中西路34号广鹿华夏综合楼201商铺'
                    ]
                ];
            }
        }
        return [
            'flag' => $flag,
            'list' => $list
        ];
    }

}
