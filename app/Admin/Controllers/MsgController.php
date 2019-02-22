<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/21
 * Time: 4:52 PM
 */

namespace App\Admin\Controllers;


use App\Admin\AdminController;
use App\Admin\Models\MsgModel;
use Encore\Admin\Layout\Content;
use Illuminate\Support\Facades\Input;

class MsgController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function smslists(Content $content)
    {
        $data = [
            'page' => Input::get('page'),
            'phone' => Input::get('phone'),
            'uid' => Input::get('uid'),
        ];
        $data['info'] = MsgModel::getSmsLists($data);
        return $content->header('短信查询')
            ->description('短信查询列表')
            ->breadcrumb(
                ['text' => '短信查询', 'url' => 'msg/smslists']
            )
            ->row(view('admin.msg.smslists', $data));
    }

}
