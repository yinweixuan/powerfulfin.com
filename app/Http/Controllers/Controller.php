<?php

namespace App\Http\Controllers;

use App\Components\HttpUtil;
use App\Components\OutputUtil;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    public function __construct()
    {
    }

    /**
     * 查询当前登录态
     */
    public function isLogin()
    {
        return DataBus::get('isLogin');
    }

    public function checkLogin($toLogin = false)
    {
        if (!$this->isLogin()) {
            if ($toLogin) {
                HttpUtil::goLogin();
            } else {
                OutputUtil::err(ERR_NOLOGIN, ERR_NOLOGIN_CONTENT);
            }
        }
    }
}
