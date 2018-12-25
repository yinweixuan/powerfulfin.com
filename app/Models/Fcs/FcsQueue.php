<?php

namespace App\Models\Fcs;

use App\Models\Fcs\FcsCommon;
use App\Models\Fcs\FcsContract;
use App\Models\Fcs\FcsField;
use App\Models\Fcs\FcsFtp;
use App\Models\Fcs\FcsHttp;
use App\Models\Fcs\FcsLoan;
use App\Models\Fcs\FcsQueue;
use App\Models\Fcs\FcsSoap;

/**
 * 富登队列
 */
class FcsQueue {

    const MAX_TRY = 8; //最大尝试次数（包含首次申请）
    const DEFAULT_DELAY = 900; //默认最小尝试时间间隔（秒）/富登切系统10分左右

    /**
     * 最小尝试时间间隔队列（秒）,从第一次push开始计算。
     * 间隔不要太小，申请时第一次返回异常后第二次太快得不到贷款id。
     */
    public static $delay_list = array(0, 200, 200, 200, 900, 3600, 7200, 30000);

    const QUEUE_FALL = 'FALL'; //存放出错的请求的队列
    const QUEUE_APPLY = 'APPLY'; //申请贷款队列
    const QUEUE_UPDATE = 'UPDATE'; //同步贷款状态至富登队列
    const QUEUE_REPAY = 'REPAY'; //同步还款状态的队列
    const QUEUE_PHONEBOOK = 'PHONEBOOK'; //上传手机通信录的队列

    public static function initQueue() {
        QueueUtil::createQueueNew(self::getQueueName(self::QUEUE_FALL));
        QueueUtil::createQueueNew(self::getQueueName(self::QUEUE_APPLY));
        QueueUtil::createQueueNew(self::getQueueName(self::QUEUE_UPDATE));
        QueueUtil::createQueueNew(self::getQueueName(self::QUEUE_REPAY));
        QueueUtil::createQueueNew(self::getQueueName(self::QUEUE_PHONEBOOK));
    }

    /**
     * 获取当前环境的队列名
     */
    public static function getQueueName($queue) {
        $queue_name = 'PF-FCS-' . $queue;
        if (config('app.env') == 'local') {
            //开发环境 TEST放前面方便查找
            $queue_name = 'TEST-' . $queue_name;
        }
        return $queue_name;
    }

    /**
     * 推送数据
     */
    public static function push($queue, $data) {
        $queue_data = array();
        $queue_data['tried_times'] = 0;
        $queue_data['queue'] = $queue;
        $queue_data['add_time'] = date('Y-m-d H:i:s');
        $queue_data['update_time'] = $queue_data['add_time'];
        $queue_data['data'] = $data;
        $delay = self::$delay_list[$queue_data['tried_times']];
        if ($delay === null) {
            $delay = self::DEFAULT_DELAY;
        }
        $queue_name = self::getQueueName($queue);
        $sdata = serialize($queue_data);
        $msg_id = QueueUtil::sendMessage($queue_name, $sdata, $delay);
        return $msg_id;
    }

    /**
     * 处理失败的信息重新推回原队列<br>
     * 如果同一数据重复失败次数过多会被推送至失败队列
     */
    public static function pushBack($queue_data) {
        $queue_data['tried_times'] ++;
        $queue_data['update_time'] = date('Y-m-d H:i:s');
        if ($queue_data['tried_times'] >= self::MAX_TRY) {
            $msg_id = self::pushFall($queue_data);
        } else {
            $delay = self::$delay_list[$queue_data['tried_times']];
            if ($delay === null) {
                $delay = self::DEFAULT_DELAY;
            }
            $queue_name = self::getQueueName($queue_data['queue']);
            $sdata = serialize($queue_data);
            $msg_id = QueueUtil::sendMessage($queue_name, $sdata, $delay);
        }
        return $msg_id;
    }

    public static function pull($queue) {
        $queue_name = self::getQueueName($queue);
        $sdata = QueueUtil::receiveMessage($queue_name);
        $data = unserialize($sdata);
        return $data;
    }

    /**
     * 推送数据：存放出错的请求的队列
     */
    public static function pushFall($data) {
        $sdata = serialize($data);
        $queue_name = self::getQueueName(self::QUEUE_FALL);
        $msg_id = QueueUtil::sendMessage($queue_name, $sdata);
        return $msg_id;
    }

    /**
     * 获取数据：存放出错的请求的队列
     */
    public static function pullFall() {
        return self::pull(self::QUEUE_FALL);
    }

    /**
     * 推送数据：申请贷款队列
     */
    public static function pushApply($lid) {
        return self::push(self::QUEUE_APPLY, $lid);
    }

    /**
     * 获取数据：申请贷款队列
     */
    public static function pullApply() {
        return self::pull(self::QUEUE_APPLY);
    }

    /**
     * 推送数据：同步贷款状态至富登队列
     */
    public static function pushUpdate($lid) {
        return self::push(self::QUEUE_UPDATE, $lid);
    }

    /**
     * 获取数据：同步贷款状态至富登队列
     */
    public static function pullUpdate() {
        return self::pull(self::QUEUE_UPDATE);
    }

    /**
     * 推送数据：同步还款状态的队列
     */
    public static function pushRepay($lid) {
        $data = array();
        $data['lid'] = $lid;
        return self::push(self::QUEUE_REPAY, $data);
    }

    /**
     * 获取数据：同步还款状态的队列
     */
    public static function pullRepay() {
        return self::pull(self::QUEUE_REPAY);
    }

    /**
     * 推送数据：上传手机通信录的队列
     */
    public static function pushPhonebook($uid, $loanId) {
        $data = array();
        $data['uid'] = $uid;
        $data['loanId'] = $loanId;
        return self::push(self::QUEUE_PHONEBOOK, $data);
    }

    /**
     * 获取数据：上传手机通信录的队列
     */
    public static function pullPhonebook() {
        return self::pull(self::QUEUE_PHONEBOOK);
    }

}
