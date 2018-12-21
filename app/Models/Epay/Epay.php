<?php

namespace App\Models\Epay;

require_once __DIR__ . '/models/KZPayRouter.php';

/**
 * 支付基类
 */
abstract class Epay {

    /**
     * 代付
     */
    const SCENE_PAY = 'pay';

    /**
     * 充值
     */
    const SCENE_RECHARGE = 'recharge';

    /**
     * 代扣
     */
    const SCENE_WITHHOLD = 'withhold';

    /**
     * 验证
     */
    const SCENE_VERIFY = 'verify';

    /**
     * 线上环境
     */
    const ENV_ONLINE = 'production';

    /**
     * 开发和测试环境
     */
    const ENV_DEV = 'local';

    /**
     * 交易成功
     */
    const STATUS_SUCCESS = 'success';

    /**
     * 退款成功
     */
    const STATUS_REFUND = 'refund';

    /**
     * 交易进行中
     */
    const STATUS_DOING = 'doing';

    /**
     * 交易结果失败,如余额不足等
     */
    const STATUS_FAIL = 'fail';

    /**
     * 交易参数错误
     */
    const STATUS_ERROR = 'error';

    /**
     * 场景
     */
    protected $scene = '';

    /**
     * 渠道
     */
    protected $channel = '';

    /**
     * 当前支付环境
     */
    protected $env = '';

    /**
     * 支付配置
     */
    protected $config = array();

    /**
     * 默认渠道
     */
    const CHANNEL_DEFAULT = 'baofoo';

    /**
     * 渠道配置,对应类名
     */
    protected static $channelConfig = array(
        'baofoo' => 'BaofooPay',
        'baofoo_app' => 'BaofooApp',
        'xinyan' => 'Xinyan',
        'yeepay' => 'YeePay',
    );

    /**
     * 构造函数
     * @param type $scene 场景,可根据场景判断使用哪个渠道
     * @param type $config $config['channel'] 强制指定某个渠道
     */
    protected function __construct($scene, $config = array()) {
        $this->scene = $scene;
        $this->config = $config;
        if (array_key_exists('env', $config)) {
            $this->env = $config['env'];
        } else {
            $this->env = self::ENV_DEV;
        }
        if (array_key_exists('channel', $config)) {
            $this->channel = $config['channel'];
        } else {
            $this->channel = self::CHANNEL_DEFAULT;
        }
    }

    /**
     * 创建支付对象的工厂
     * @param type $scene
     * @param type $config
     */
    public static function createPay($scene, $config = array()) {
        if (array_key_exists('channel', $config)) {
            $channel = $config['channel'];
        } else {
            $channel = self::CHANNEL_DEFAULT;
        }
        if (!array_key_exists($channel, self::$channelConfig)) {
            throw new KZException("支付渠道有误");
        }
        $className = self::$channelConfig[$channel];
        $obj = new $className($scene, $config);
        return $obj;
    }

    /**
     * 统一调用入口
     */
    public static function exec($params = array()) {
//        Yii::log('[' . __CLASS__ . '][' . __FUNCTION__ . '][' . __LINE__ . ']:请求参数：' . var_export($params, true), CLogger::LEVEL_INFO, CalcRepay::YII_LOG_NAME);
        $data = \KZPayRouter::getFormedData($params);
        $php_file = __DIR__ . '/models/' . $data['class'] . '.php';
        if (is_file($php_file)) {
            require_once $php_file;
            $class = new $data['class']($data['scene'], $data['config']);
            $func = $data['func'];
            $result = $class->$func($data['params']);
        }
        return $result;
    }

    /**
     * 根据当前参数产生系统订单id
     * 8-30 位字母和 数字
     */
    protected function calcOrderId() {
        $oriId = date('YmdHis') . $this->getDateu() . mt_rand(10000000, 99999999);
        $ret = substr($oriId, 0, 30);
        return $ret;
    }

    /**
     * 产生流水号
     * 8-20 位字母和数字,每次请 求都不可重复
     */
    protected function calcFlowId() {
        $oriId = date('YmdHis') . $this->getDateu();
        return $oriId;
    }

    public function getDateu() {
        $mt = microtime();
        $mtarr = preg_split('/ |\./ ', $mt);
        return substr($mtarr[1], 0, 6);
    }

}
