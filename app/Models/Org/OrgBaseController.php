<?php
/**
 * Created by PhpStorm.
 * User: haoxiang
 * Date: 2018/12/21
 * Time: 11:09 AM
 */

namespace App\Models\Org;


use App\Components\HttpUtil;
use App\Http\Controllers\Controller;
use App\Models\Org\OrgDataBus;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class OrgBaseController extends Controller
{

    public function __construct()
    {
        parent::__construct();
        $this->checkLogin(true);
    }

    /**
     * 检查是否登录,未登录跳登录页
     * @param bool $toLogin
     */
    protected function checkLogin($toLogin = true, $isAjax = null)
    {
        $isLogin = OrgDataBus::get('isLogin');
        if ($isAjax === null) {
            $isAjax = Request::ajax();
        }
        //判断登录态,如果未登录,则直接跳转到登录页.如果是登录页自己,则不检查该属性
        if (Request::path() == 'home/login') {
            if ($isLogin) {
                Redirect::to('/')->send();
            } else {
                return;
            }
        } else if (!$isLogin) {
            if (!$isAjax) {
                $url = URL::current();
                Redirect::to("/home/login?url={$url}")->send();
            } else {
                OutputUtil::err(ERR_NOLOGIN, ERR_NOLOGIN_CONTENT);
            }
        }
    }
}