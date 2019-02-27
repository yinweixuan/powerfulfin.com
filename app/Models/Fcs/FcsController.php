<?php

/**
 * 富登对外接口
 */

namespace App\Models\Fcs;

class FcsController {

    public $params = [];

    public function __construct() {
        $this->params = FcsHttp::getParams();
        $this->params['fcs_loan_id'] = $this->params['lid'];
    }

    //贷款状态更新接口
    public function updateStatus() {
        try {
            $lid = FcsLoan::pullStatus($this->params);
        } catch (\Exception $ex) {
            FcsHttp::fail($ex->getMessage());
        }
        FcsHttp::success([], $lid);
    }

    //协议上传接口
    public function uploadContract() {
        try {
            $lid = FcsLoan::getContract($this->params);
        } catch (\Exception $ex) {
            FcsHttp::fail($ex->getMessage());
        }
        FcsHttp::success([], $lid);
    }

    //初始化队列
    public function initQueue() {
        FcsQueue::initQueue();
    }

}