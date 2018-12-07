<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/7
 * Time: 11:39 AM
 */

namespace App\Components;


require_once(PATH_LIBRARIES . '/aliyun-mns-1.3.5/mns-autoloader.php');

use AliyunMNS\Client;
use AliyunMNS\Requests\SendMessageRequest;
use AliyunMNS\Requests\CreateQueueRequest;
use AliyunMNS\Exception\MnsException;
use Illuminate\Support\Facades\Log;

class QueueUtil
{
    private static function getAccessId()
    {
        return env('ALIYUN_MNS_ACCESS_ID', '');
    }

    private static function getAccessKey()
    {
        return env('ALIYUN_MNS_ACCESS_KEY', '');
    }

    private static function getEndPoint()
    {
        return env('ALIYUN_MNS_END_POINT', '');
    }

    /**
     * 队列连接
     * @var type
     */
    private static $client = null;

    /**
     * 获取客户端连接
     * @return type
     * @throws PFException
     */
    public static function getClient()
    {
        if (empty(self::$client)) {
            try {
                self::$client = new Client(self::getEndPoint(), self::getAccessId(), self::getAccessKey());
            } catch (\Exception $e) {
                throw new PFException($e->getMessage(), ERR_QUEUE_CREATE_CLIENT);
            }
        }
        return self::$client;
    }

    /**
     * 最长队列名和key长度
     */
    const MAX_STR_LEN = 64;

    /**
     * 队列缓存
     * @var type
     */
    private static $queues = array();

    /**
     * 创建队列
     * @param type $queueName 队列名
     * @param type $attributes 队列属性.有以下可选.默认无需设置 https://help.aliyun.com/document_detail/mns/api_reference/queue_api_spec/queue_operation.html?spm=5176.docmns/api_reference/invoke/common_parameters.6.154.VQ4XIH
     *      private $delaySeconds;
     * private $maximumMessageSize;
     * private $messageRetentionPeriod;
     * private $visibilityTimeout;
     * private $pollingWaitSeconds;
     * @return
     * @throws PFException
     */
    public static function createQueue($queueName, $attributes = NULL)
    {
        if (!is_string($queueName) || strlen($queueName) >= self::MAX_STR_LEN) {
            throw new PFException("队列名非法:{$queueName}", ERR_QUEUE_CREATE);
        }
        if (isset(self::$queues[$queueName])) {
            return self::$queues[$queueName];
        }
        $client = self::getClient();
        //获取队列
        try {
            self::$queues[$queueName] = $client->getQueueRef($queueName);
            if (self::$queues[$queueName]) {
                return self::$queues[$queueName];
            }
        } catch (\Exception $e) {
            throw new PFException($e->getMessage(), ERR_QUEUE_GET_QUEUE);
        }
        //如果获取不到,创建队列
        try {
            $request = new CreateQueueRequest($queueName, $attributes);
            $res = $client->createQueue($request);
        } catch (MnsException $e) {
            throw new PFException($e->getMessage(), ERR_QUEUE_CREATE_QUEUE);
        }
        //获取队列句柄
        try {
            self::$queues[$queueName] = $client->getQueueRef($queueName);
        } catch (MnsException $e) {
            throw new PFException($e->getMessage(), ERR_QUEUE_GET_QUEUE);
        }

        return self::$queues[$queueName];
    }

    /**
     * 创建新队列
     */
    public static function createQueueNew($queueName, $attributes = NULL)
    {
        if (!is_string($queueName) || strlen($queueName) >= self::MAX_STR_LEN) {
            throw new PFException("队列名非法:{$queueName}", ERR_QUEUE_CREATE);
        }
        $client = self::getClient();
        //创建队列
        try {
            $request = new CreateQueueRequest($queueName, $attributes);
            $res = $client->createQueue($request);
        } catch (MnsException $e) {
            throw new PFException($e->getMessage(), ERR_QUEUE_CREATE_QUEUE);
        }
        //获取队列句柄
        try {
            self::$queues[$queueName] = $client->getQueueRef($queueName);
        } catch (MnsException $e) {
            throw new PFException($e->getMessage(), ERR_QUEUE_GET_QUEUE);
        }
        return self::$queues[$queueName];
    }

    /**
     * 删除队列
     * @param type $queueName 队列名
     * @return bool
     * @throws PFException
     */
    public static function deleteQueue($queueName)
    {
        $client = self::getClient();
        try {
            $res = $client->deleteQueue($queueName);
            unset(self::$queues[$queueName]);
        } catch (MnsException $e) {
            throw new PFException($e->getMessage(), ERR_QUEUE_DEL_QUEUE);
        }
        return true;
    }

    /**
     * 发送消息
     * @param type $queueName 队列名
     * @param type $msg 消息内容
     * @param type $delaySeconds 延迟出现的秒数
     * @param type $priority 优先级
     * @return
     * @throws PFException
     */
    public static function sendMessage($queueName, $msg, $delaySeconds = NULL, $priority = NULL)
    {
        if (!is_string($msg)) {
            $msg = json_encode($msg);
        }
        $queue = self::createQueue($queueName); //TODO：创建失败，队列已存在
        $client = self::getClient();
        $queue = $client->getQueueRef($queueName);
        $bodyMD5 = strtoupper(md5(base64_encode($msg)));
        try {
            $request = new SendMessageRequest($msg, $delaySeconds, $priority);
            $res = $queue->sendMessage($request);
            //校验md5是否正确
            $sendMD5 = strtoupper($res->getMessageBodyMD5());
            if ($sendMD5 != $bodyMD5) {
                throw new KZException("MD5计算有误.before:{$bodyMD5}.after:{$sendMD5}");
            }
            $messageId = $res->getMessageId();
        } catch (\Exception $e) {
            throw new PFException($e->getMessage(), ERR_QUEUE_SEND_MSG);
        }
        return $messageId;
    }

    /**
     * 从队列中获取消息.如果队列中无消息可获取,返回null
     * @param type $queueName 队列名
     * @param type $needDelete 使用过后是否删除消息
     * @return null
     * @throws PFException
     */
    public static function receiveMessage($queueName, &$msgId = '', $needDelete = true)
    {
        // $queue = new Queue(self::$client, $queueName);  报错：队列已创建
        $client = self::getClient();
        $queue = $client->getQueueRef($queueName);
        try {
            $res = $queue->receiveMessage();
            $msgId = $res->getMessageId();
            $body = $res->getMessageBody();
        } catch (\Exception $e) {
            if ($e->getCode() == 404) {
                $body = null;
                return $body;
            } else {
                Log::error();
            }
            throw new PFException($e->getMessage(), ERR_QUEUE_REV_MSG);
        }
        try {
            if ($needDelete) {
                $receiptHandle = $res->getReceiptHandle();
                $delRes = $queue->deleteMessage($receiptHandle);
            }
        } catch (\Exception $e) {
            throw new PFException($e->getMessage(), ERR_QUEUE_DEL_MSG);
        }
        return $body;
    }
}
