<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/19
 * Time: 5:15 PM
 */

namespace App\Http\Controllers\App\V1;


use App\Components\OutputUtil;
use App\Components\PFException;
use App\Http\Controllers\App\AppController;
use App\Models\DataBus;
use App\Models\Server\BU\BUUserInfo;
use Illuminate\Support\Facades\Input;

class UserController extends AppController
{
    public function uconfig()
    {
        $this->checkLogin(false);
        $user = DataBus::get('user');
        if (empty($user)) {
            OutputUtil::err("未获取用户信息", ERR_NOLOGIN);
        }

        try {
            $part = Input::get('part');
            if (!in_array($part, array(1, 2, 3, 4, 5, 6))) {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
            $methodType = strtolower($_SERVER['REQUEST_METHOD']);
            $data = $methodType == 'post' ? $_POST : $_GET;
            $result = BUUserInfo::getUConfig($user, $data, $part);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $result);
        } catch (PFException $e) {
            OutputUtil::err($e->getMessage(), $e->getCode());
        }
    }

    public function usupply()
    {
        $this->checkLogin();
        $user = DataBus::get('user');
        if (empty($user)) {
            OutputUtil::err("未获取用户信息", ERR_NOLOGIN);
        }

        try {
            $part = Input::get('part');
            if (!in_array($part, array(1, 2, 3, 4, 5, 6))) {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
            $function = 'supplyUserStep' . $part;
            $result = BUUserInfo::$function($_POST, $user);
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $result);
        } catch (PFException $e) {
            OutputUtil::err(ERR_SYS_PARAM_CONTENT, $e->getCode());
        }
    }
}
