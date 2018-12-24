<?php
/**
 * 统计相关操作
 * User: haoxiang
 * Date: 2018/12/24
 * Time: 7:21 PM
 */
namespace App\Http\Controllers\Org;

use App\Components\CookieUtil;
use App\Components\PFException;
use App\Models\Org\OrgBaseController;
use App\Models\Org\OrgDataBus;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;


class StatController extends OrgBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 订单查询
     */
    public function list()
    {
        echo __FUNCTION__;
    }

    /**
     * 校区统计
     */
    public function sumup()
    {
        echo __FUNCTION__;
    }
}