<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 2:53 PM
 */

namespace App\Http\Controllers\App;
require PATH_VENDOR . '/autoload.php';

use App\Components\OutputUtil;
use App\Http\Controllers\Controller;
use App\Models\DataBus;
use App\Models\Server\BU\BUAppMobile;

class AppController extends Controller
{
    public $isAndroid = false;
    public $isIOS = false;
    public $isWX = false;
    public $isAppcan = false;

    public function __construct()
    {

        parent::__construct();
        config("app.env");

        env("DB_CONNECTION");

        $detect = new \Mobile_Detect();
        $this->isAndroid = $detect->isAndroidOS();
        $this->isIOS = $detect->isIOS();
        $this->isWX = (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'micromessenger') !== false ? true : false);
        $this->isAppcan = (isset($_SERVER['HTTP_USER_AGENT']) && stripos($_SERVER['HTTP_USER_AGENT'], 'appcan') !== false ? true : false);
        $this->mobileModel();
    }

    /**
     * 查询当前登录态
     */
    protected function isLogin()
    {
        return DataBus::get('isLogin');
    }

    protected function checkLogin()
    {
        if (!$this->isLogin()) {
            OutputUtil::err(ERR_NOLOGIN_CONTENT, ERR_NOLOGIN);
        }
    }

    /**
     * 计算手机分布情况
     */
    public function mobileModel()
    {
        if ($this->isIOS || $this->isKZIOS()) {
            BUAppMobile::ios();
        } elseif ($this->isAndroid) {
            BUAppMobile::android();
        } else {

        }
    }

    /**
     * IosAPP 返回UA中有部分 不符合大众模式，所以单独判断IOS设备类型
     * @return boolean
     */
    public function isKZIOS()
    {
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if (strpos($ua, 'iPhone') && strpos($ua, 'iOS')) {
            return true;
        }
        return false;
    }
}
