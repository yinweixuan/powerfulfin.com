<?php
/**
 * 首页相关功能,包括登入登出
 * User: haoxiang
 * Date: 2018/12/21
 * Time: 11:11 AM
 */

namespace App\Http\Controllers\Org;

use App\Models\Org\OrgBaseController;

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
        echo __FILE__;
    }
}