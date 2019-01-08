<?php
/**
 * 首页相关功能,包括登入登出
 * User: haoxiang
 * Date: 2018/12/21
 * Time: 11:11 AM
 */

namespace App\Http\Controllers\Org;

use App\Components\CookieUtil;
use App\Components\HttpUtil;
use App\Components\OutputUtil;
use App\Components\PFException;
use App\Components\QRCodeUtil;
use App\Models\ActiveRecord\ARPFOrgUsers;
use App\Models\Org\OrgBaseController;
use App\Models\Org\OrgDataBus;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;


class HomeController extends OrgBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 机构管理后台的首页
     */
    public function index()
    {
        //return $this->view('org.home.index');


        return $this->view('org.home.index2');
    }

    /**
     * 登录
     */
    public function login()
    {
        $url = Input::get('url');
        $data = ['url' => $url, 'errmsg' => '', 'name' => '', 'passwd' => '',];
        //处理提交密码验证请求
        if (strtolower(Request::method()) == 'post') {
            try {
                $name = Input::get("name");
                $passwd = Input::get("passwd");
                $data['name'] = $name;
                $data['passwd'] = $passwd;

                if (empty($name) || empty($passwd)) {
                    throw new PFException("请填写用户名和密码", ERR_SYS_PARAM);
                }
                $userInfo = ARPFOrgUsers::query()->where(['org_username' => $name])->first();
                if (empty($userInfo)) {
                    throw new PFException("用户名或密码错误", ERR_NOLOGIN);
                }
                //判断密码是否匹配
                if (md5($passwd) != $userInfo['org_password']) {
                    throw new PFException("用户名或密码错误.", ERR_NOLOGIN);
                }
                $strCookie = $userInfo['org_uid'] . "|" . $userInfo['org_username'] . "|" . $userInfo['org_username'] . '|' . CookieUtil::createSafecv();
                CookieUtil::setCookie(CookieUtil::db_cookiepre . '_' . OrgDataBus::COOKIE_KEY, CookieUtil::strCode($strCookie, 'ENCODE'));
                //判断有没有跳入前页面地址,如果有,跳入之前地址,如果没有跳入首页
                if ($url) {
                    Redirect::to($url)->send();
                } else {
                    Redirect::to('/')->send();
                }
                return;
            } catch (\Exception $exception) {
                $data['errmsg'] = $exception->getMessage();
            }
        }
        return $this->view('org.home.login', $data);
    }

    /**
     * 登出
     */
    public function logout()
    {
        CookieUtil::setCookie(CookieUtil::db_cookiepre . '_' . OrgDataBus::COOKIE_KEY,'');
        Redirect::to('/')->send();
    }

    /**
     * 站内信
     */
    public function msglist()
    {
        $data = [];
        return $this->view('org.home.msglist', $data);
    }

    /**
     * 常见问题
     */
    public function faq()
    {
        return $this->view('org.home.faq');
    }

    /**
     * 资金方信息
     */
    public function capital()
    {
        return $this->view('org.home.capital');
    }

    /**
     * 扫码申请的二维码的页面展示
     */
    public function applyqr()
    {
        return $this->view('org.home.applyqr');
    }

    /**
     * 扫码申请的二维码.如果非大圣分期APP扫描的,直接跳转到下载页
     */
    public function qr()
    {
        //检查二维码路径下是否有,如果没有,生成二维码,并叠加大圣分期logo在中心
        $oid = OrgDataBus::get('org_id');
        $path = PATH_STORAGE . "/org_qr/";
        if (!file_exists($path)) {
            mkdir($path, 0777, true);
        }
        $path .= "/{$oid}.png";
        if (true || !file_exists($path)) {
            $url = "powerfulfin://apply?oid={$oid}";
            $url = "http://" . DOMAIN_WEB . "/index/qrscan?f=qr&oid={$oid}";
            QRCodeUtil::png($url, $path, QR_ECLEVEL_H);
        }
        OutputUtil::file("apply_{$oid}.png", $path, 'image/png');
    }

}