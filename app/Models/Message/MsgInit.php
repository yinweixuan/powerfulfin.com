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
use App\Models\ActiveRecord\ARPFMsgTemplate;
use App\Models\ActiveRecord\ARPFSms;
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
     * 获取消息模板
     * @param $scenes 消息场景
     * @param $key  消息关键词
     * @param array $titleParams title替换数据
     * @param array $contentParams 内容替数据
     * @return array|bool|mixed
     * @throws PFException
     */
    public static function getMsgContent($scenes, $key, $titleParams = array(), $contentParams = array())
    {
        if (empty($key)) {
            throw new PFException("获取消息模板异常");
        }

        $data = ARPFMsgTemplate::getMsgTemplateByScenesAndKey($scenes, $key);
        if (empty($data)) {
            throw new PFException("未正常获取消息模板");
        }

        if (!empty($titleParams)) {
            $data['title'] = str_replace('{%s}', '%s', $data['title']);
            $data['title'] = call_user_func_array('sprintf', array_merge(array($data['title']), $titleParams));
        }

        if (!empty($contentParams)) {
            $data['content'] = str_replace('{%s}', '%s', $data['content']);
            $data['content'] = call_user_func_array('sprintf', array_merge(array($data['content']), $contentParams));
        }

        return $data;
    }

    /**
     * 消息队列名称
     */
    const SEND_MSG_QUEUE_NAME = 'PF-MSG-LIST';
    const SEND_MSG_QUEUE_NAME_TEST = 'PF-MSG-LIST-TEST';

    private static function getQueueName()
    {
        if (config("env") != 'product') {
            return self::SEND_MSG_QUEUE_NAME_TEST;
        } else {
            return self::SEND_MSG_QUEUE_NAME;
        }
    }


    /**
     * 消息加入消息队列
     * @param $type 消息类型
     * @param int $uid 用户uid
     * @param null $device 手机号码、pushid、email
     * @param $key  消息关键词
     * @param null $titleParams 信息title
     * @param null $contentParams 信息内容
     * @param array $params 附加参数
     * @param null $delaySeconds 延时多少秒处理
     * @param null $priority 消息队列优先级
     * @return bool
     * @throws PFException
     */
    public static function sendMsgQueue($type, $uid = 0, $device = null, $key, $titleParams = null, $contentParams = null, $params = array(), $delaySeconds = null, $priority = null)
    {
        //判断相关必要参数
        if (empty($type) || empty($device) || empty($key)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }
        //对消息类型进行判断，获取模板数据类型
        switch ($type) {
            case self::SEND_MSG_TYPE_SMS:
                $scenes = ARPFMsgTemplate::SCENES_SMS;
                break;
            case self::SEND_MSG_TYPE_JPUSH:
                $scenes = ARPFMsgTemplate::SCENES_JPUSH;
                break;
            case self::SEND_MSG_TYPE_EMAIL:
                $scenes = ARPFMsgTemplate::SCENES_EMAIL;
                break;
            case self::SEND_MSG_TYPE_LETTER:
                $scenes = ARPFMsgTemplate::SCENES_LETTER;
                break;
            default:
                throw new PFException("消息类型异常");
                break;
        }

        $msgTemplate = self::getMsgContent($scenes, $key, $titleParams, $contentParams);

        $data = array(
            'type' => $type,
            'uid' => $uid,
            'device' => $device,
            'title' => $msgTemplate['title'],
            'content' => $msgTemplate['content'],
            'params' => $params
        );

        if ($type == self::SEND_MSG_TYPE_JPUSH) {
            try {
                $fieldData = array(
                    'otype' => isset($data['params']['otype']) ? $data['params']['otype'] : 4,
                    'cid' => $data['params']['cid'],
                    'oid' => $data['params']['oid'],
                    'from_uid' => -1,
                    'to_uid' => $data['uid'],
                    'title' => $data['title'],
                    'content' => $data['content'],
                    'ctime' => DataBus::get('ctime'),
                    'status' => 0,
                );
//                ARMessage::_insert($fieldData);
                if ($device == 1) {
                    return true;
                }
            } catch (PFException $exception) {
                //TODO nothing
            }
        }
        try {
//            $queueName = self::getQueueName();
//            QueueUtil::sendMessage($queueName, $data, $delaySeconds, $priority);
            self::sendMsg($data);
        } catch (PFException $exception) {
            throw new PFException($exception->getMessage());
        }
    }

    /**
     * 消息发送
     * @return bool|null
     * @throws PFException
     */
    public static function sendMsg($data)
    {
//        $queueName = self::getQueueName();
//        $queueData = QueueUtil::receiveMessage($queueName);
//        if (empty($queueData)) {
//            return null;
//        }
//
//        $data = json_decode($queueData, true);
        $type = strtolower($data['type']);
        if ($type == self::SEND_MSG_TYPE_SMS) {
            try {
                $result = ARPFSms::createNewSMS($data['uid'], $data['device'], $data['content']);
                MsgSMS::sendSMS($data['device'], $data['content']);
                ARPFSms::_update($result, array('status' => STATUS_SUCCESS, 'plat' => env('SMS_PLAT')));
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
//                ARKzLetter::_insert($fieldData);
            } catch (PFException $exception) {
                throw new PFException($exception->getMessage());
            }
        } else {
            throw new PFException("消息类型异常");
        }
        return true;
    }
}
