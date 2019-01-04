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
use App\Http\Controllers\App\Models\Loan;
use App\Models\DataBus;
use Illuminate\Support\Facades\Input;

class IndexController extends AppController {

    public function index() {
//        $version = mt_rand(0, 3);
//        $mac = mt_rand(0, 1) == 1 ? '' : 'AA:AA:AA:AA:AA:AA';
//        $loan = [
//            'id' => 123,
//            'step' => mt_rand(0, 5),
//            'is_overdue' => mt_rand(0, 1),
//            'repay_date' => '2019-02-15',
//            'repay_money' => mt_rand(10000, 500000) / 100,
//            'can_repay' => mt_rand(0, 1),
//        ];
        $version = Input::get('version');
        $mac = Input::get('mac');
        $uid = DataBus::getUid();
        $loan = Loan::getHomeLoanInfo($uid);
        $data = [];
        $data['audit'] = $this->getAppStoreAudit($version);
        $data['banner'] = $this->getBanner();
        $data['customer_service'] = $this->getCustomerService();
        $data['notice'] = $this->getNotice();
        $data['loan'] = $this->getLoan($mac, $loan);
        OutputUtil::out($data);
    }

    /**
     * 首页banner
     */
    public function getBanner() {
        return config('index.banner');
    }

    /**
     * 首页客服信息
     */
    public function getCustomerService() {
        return config('index.customer_service');
    }

    /**
     * 首页通知
     */
    public function getNotice() {
        return config('index.notice');
    }

    public function getLoan($mac = null, $loan = null) {
        $lid = $loan['id'];
        $step = $loan['step'];
        $repay_date = $loan['repay_date'];
        $repay_money = $loan['repay_money'];
        $is_overdue = $loan['is_overdue'];
        $can_repay = $loan['can_repay'];
        if ($step == 1) {
            //审核中
            $data_detail = [
                'status' => '1',
                'status_img_2x' => 'http://www.powerfulfin.com/img/loan/audit2x.png',
                'status_img_3x' => 'http://www.powerfulfin.com/img/loan/audit3x.png',
                'status_desp' => '审核中',
                'remark' => '您的分期正在审核中，请耐心等待',
                'buttons' => [
                    $this->getButton(1, $lid)
                ]
            ];
        } elseif ($step == 2) {
            //待确认
            $data_detail = [
                'status' => '1',
                'status_img_2x' => 'http://www.powerfulfin.com/img/loan/confirm2x.png',
                'status_img_3x' => 'http://www.powerfulfin.com/img/loan/confirm3x.png',
                'status_desp' => '待确认',
                'remark' => '请您确认分期',
                'buttons' => [
                    $this->getButton(1, $lid),
                    $this->getButton(2, $lid)
                ]
            ];
        } elseif ($step == 3) {
            //已拒绝
            $data_detail = [
                'status' => '1',
                'status_img_2x' => 'http://www.powerfulfin.com/img/loan/refused2x.png',
                'status_img_3x' => 'http://www.powerfulfin.com/img/loan/refused3x.png',
                'status_desp' => '已拒绝',
                'remark' => '您的分期已被拒绝',
                'buttons' => [
                    $this->getButton(1, $lid)
                ]
            ];
        } elseif ($step == 4) {
            //已终止
            $data_detail = [
                'status' => '1',
                'status_img_2x' => 'http://www.powerfulfin.com/img/loan/end2x.png',
                'status_img_3x' => 'http://www.powerfulfin.com/img/loan/end3x.png',
                'status_desp' => '已终止',
                'remark' => '您的分期已终止',
                'buttons' => [
                    $this->getButton(1, $lid)
                ]
            ];
        } elseif ($step == 5) {
            //还款中
            $data_detail = [
                'status' => '2',
                'remark' => '请你按时还款',
                'repay_date' => $repay_date,
                'repay_money' => $repay_money,
                'is_overdue' => '0',
                'buttons' => [
                    $this->getButton(1, $lid)
                ]
            ];
            if ($can_repay) {
                $data_detail['buttons'][] = $this->getButton(3, $lid);
            }
            if ($is_overdue) {
                $data_detail['remark'] = '您已逾期，请尽快偿还';
                $data_detail['is_overdue'] = '1';
            }
        } else {
            $data_detail = $this->getRecommend($mac);
        }
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
        return array_merge($data, $data_detail);
    }

    /**
     * 首页按钮（style：1：白按钮；2：红按钮）
     */
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

    /**
     * 首页机构推荐信息
     */
    public function getRecommend($mac = null) {
        $recommend = [
            'status' => '0',
            'status_img_2x' => 'http://www.powerfulfin.com/img/loan/noresult2x.png',
            'status_img_3x' => 'http://www.powerfulfin.com/img/loan/noresult3x.png',
            'buttons' => [
                $this->getButton(5)
            ]
        ];
        if ($mac) {
            $wifi = \App\Models\ActiveRecord\ARPFOrgWifi::getByMac($mac);
            if ($wifi['sid']) {
                $school = \App\Models\ActiveRecord\ARPFOrg::getOrgById($wifi['sid']);
                if ($school['id']) {
                    $recommend = [
                        'status' => '0',
                        'school_id' => $school['id'],
                        'school_name' => $school['org_name'],
                        'status_img_2x' => 'http://www.powerfulfin.com/img/loan/recommend2x.png',
                        'status_img_3x' => 'http://www.powerfulfin.com/img/loan/recommend3x.png',
                        'buttons' => [
                            $this->getButton(4, $school['id']),
                            $this->getButton(5)
                        ]
                    ];
                }
            }
        }
        return $recommend;
    }

    /**
     * 首页ios审核信息
     */
    public function getAppStoreAudit($version = null) {
        $flag = '0';
        $list = [];
        if (config('index.ios_audit_version') && $version == config('index.ios_audit_version')) {
            $flag = '1';
            $data = AliyunOpenSearchUtil::searchSchool('恒企');
            if (!empty($data['list'])) {
                foreach ($data['list'] as $item) {
                    $new_item = [];
                    $new_item['id'] = $item['id'];
                    $new_item['name'] = $item['name'];
                    $new_item['address'] = $item['address'];
                    $list[] = $new_item;
                }
            }
        }
        return [
            'flag' => $flag,
            'list' => $list
        ];
    }

}
