<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 5:06 PM
 */

namespace App\Models\Message;

use App\Components\PFException;
use JPush\Client;

require_once PATH_VENDOR . '/autoload.php';


class MsgJPUSH
{
    private static $_log = null;
    private static $_client = null;

    /**
     * 扩展link
     * @var array
     */
    public static $_extras_link = array(
        'loan_detail' => 'kezhanwang://loandetail?id=',
        'loan_sure' => 'kezhanwang://loansure?lid=',
        'loan_sure_contract' => 'kezhanwang://loansurecontract?lid=',
    );


    private static function getInstance()
    {
        if (self::$_client) {
            return self::$_client;
        } else {
            self::$_log = PATH_STORAGE . "/logs/" . date('Ymd') . "/jpush.log";
            self::$_client = new Client(env("JPUSH_APP_KEY"), env("JPUSH_MASTER_SECRET"), self::$_log);
            return self::$_client;
        }
    }

    /**
     * 消息推送场景
     */
    const SEND_SCENES_ALL = '1';    //全量推送
    const SEND_SCENES_SPECIFY = "2";    //指定推送

    /**
     * 发送消息
     * @param string $type 场景类型
     * @param $alert
     * @param $title
     * @param $content
     * @param $extras
     * @param array $registrationId //极光推送注册id
     * @return bool
     * @throws PFException
     */
    public static function sendPush($type = self::SEND_SCENES_ALL, $alert, $title, $content, $extras, $registrationId = array())
    {
        self::getInstance();
        try {
            $push = self::$_client->push();
            $push->setPlatform(array('ios', 'android'));
            if ($type == self::SEND_SCENES_ALL) {
                $push->addAllAudience();
            } else {
                if (empty($registrationId)) {
                    throw new PFException("无推送用户");
                }
                $push->addRegistrationId($registrationId);
            }
            $push->setNotificationAlert($alert)
                ->iosNotification($title, array(
                    'sound' => 'sound.caf',
                    'badge' => '+1',
                    'content-available' => true,
                    'mutable-content' => true,
                    'extras' => $extras,
                ))
                ->androidNotification($title, array(
                    'title' => $title,
                    'extras' => $extras,
                ))
                ->message($content, array(
                    'title' => $title,
                    'content_type' => 'text',
                    'extras' => $extras,
                ))
                ->options(array(
                    'apns_production' => config("app.env") == 'production' ? true : false,
                ))
                ->send();
            return true;
        } catch (\JPush\Exceptions\APIConnectionException $e) {
            throw new PFException($e->getMessage());
        } catch (\JPush\Exceptions\APIRequestException $e) {
            throw new PFException($e->getMessage());
        }
    }
}
