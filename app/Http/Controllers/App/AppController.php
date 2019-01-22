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
use App\Models\ActiveRecord\ARPFAppRequestLog;
use App\Models\DataBus;
use App\Models\Server\BU\BUAppMobile;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;


class AppController extends Controller
{
    public $isAndroid = false;
    public $isIOS = false;
    public $isWX = false;
    public $isAppcan = false;

    public function __construct()
    {

        parent::__construct();

        $this->addRequst();

        $detect = new \Mobile_Detect();
        $this->isAndroid = $detect->isAndroidOS();
        $this->isIOS = $detect->is('iphone');
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
        if ($this->isIOS || $this->isPFIOS()) {
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
    public function isPFIOS()
    {
        $ua = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
        if (strpos($ua, 'iPhone') && strpos($ua, 'iOS')) {
            return true;
        }
        return false;
    }

    public function addRequst()
    {
        $info = [
            'request_url' => $_SERVER['REQUEST_URI'],
            'version' => Input::get('version'),
            'phone_type' => DataBus::get('plat'),
            'request' => json_encode(Input::get()),
            'create_time' => date('Y-m-d H:i:s')
        ];
        $plat = DataBus::get('plat');
        Log::info($plat);
        if ($plat == 2) {
            $info['phone_type'] = PHONE_TYPE_ANDROID;
        } else if ($plat == 1) {
            $info['phone_type'] = PHONE_TYPE_IOS;
        } else if ($this->isWX) {
            $info['phone_type'] = 'WeChat';
        } else {
            $info['phone_type'] = 'UNKnow';
        }
        ARPFAppRequestLog::addInfo($info);
    }
}
