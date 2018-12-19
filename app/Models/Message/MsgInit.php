<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/19
 * Time: 11:34 AM
 */

namespace App\Models\Message;


use App\Components\PFException;
use App\Components\QueueUtil;
use App\Models\DataBus;

class MsgInit
{
    /**
     * 消息类型
     */
    const SEND_MSG_TYPE_SMS = 'sms';    //短信
    const SEND_MSG_TYPE_JPUSH = 'jpush';    //极光推送
    const SEND_MSG_TYPE_EMAIL = 'email';    //电子邮件
    const SEND_MSG_TYPE_LETTER = 'letter';  //站内信

    /**
     * 消息队列名称
     */
    const SEND_MSG_QUEUE_NAME = 'KZ-MSG-LIST';
    const SEND_MSG_QUEUE_NAME_TEST = 'KZ-MSG-LIST-TEST';

    private static function getQueueName()
    {
        if (config("env") != 'product') {
            return self::SEND_MSG_QUEUE_NAME_TEST;
        } else {
            return self::SEND_MSG_QUEUE_NAME;
        }
    }

    /**
     * 消息发送
     * @return bool|null
     * @throws PFException
     */
    public static function sendMsg()
    {
        $queueName = self::getQueueName();
        $queueData = QueueUtil::receiveMessage($queueName);
        if (empty($queueData)) {
            return null;
        }

        $data = json_decode($queueData, true);
        $type = strtolower($data['type']);
        if ($type == self::SEND_MSG_TYPE_SMS) {
            try {
                $result = ARPaySms::createNewSMS(1, $data['uid'], $data['device'], $data['content'], $data['params']['sid'], $data['params']['sbid']);
                $ret = MsgSMS::sendSMS($data['device'], $data['content']);
                if (MsgSMS::SMS_PLAT == 1) {
                    ARPaySms::_update($result['id'], array('status' => 1, 'relate_id' => $ret));
                } else {
                    ARPaySms::_update($result['id'], array('status' => 1, 'plat' => MsgSMS::SMS_PLAT));
                }
            } catch (PFException $exception) {
                throw new PFException($exception->getMessage());
            }
        } else if ($type == self::SEND_MSG_TYPE_JPUSH) {
            try {
                if (empty($data['device'])) {
                    MsgJPUSH::sendPush(MsgJPUSH::SEND_SCENES_ALL, $data['title'], $data['title'], $data['content'], $data['params']['extras'], array());
                } else {
                    MsgJPUSH::sendPush(MsgJPUSH::SEND_SCENES_SPECIFY, $data['title'], $data['title'], $data['content'], $data['params']['extras'], $data['device']);
                }
            } catch (PFException $exception) {
                throw new PFException($exception->getMessage());
            }
        } else if ($type == self::SEND_MSG_TYPE_EMAIL) {
            try {

            } catch (PFException $exception) {

            }
        } else if ($type == self::SEND_MSG_TYPE_LETTER) {
            try {
                $fieldData = array(
                    'sid' => $data['uid'],
                    'status' => $data['params']['status'],
                    'uid' => $data['device'],
                    'title' => $data['title'],
                    'msg' => $data['content'],
                    'add_ip' => $data['params']['ip'],
                    'send_time' => DataBus::get('ctime'),
                    'type' => $data['params']['letter_type'],
                    'is_top' => $data['params']['is_top'],
                    'batch_number' => $data['params']['batch_number'],
                );
                ARKzLetter::_insert($fieldData);
            } catch (PFException $exception) {
                throw new PFException($exception->getMessage());
            }
        } else {
            throw new PFException("消息类型异常");
        }
        return true;
    }
}
