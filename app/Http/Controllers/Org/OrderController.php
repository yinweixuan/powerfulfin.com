<?php
/**
 * 订单相关操作
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


class OrderController extends OrgBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 报名确认列表
     */
    public function bookinglist()
    {
        $data = [];
        return $this->view('org.order.bookinglist', $data);
    }

    /**
     * 确认上课放款列表
     */
    public function confirmlist()
    {
        $data = [];
        return $this->view('org.order.confirmlist', $data);
    }

    /**
     * 分期详情
     */
    public function detail()
    {
        $data = [];
        return $this->view('org.order.detail', $data);
    }
}