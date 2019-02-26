<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 11:42 AM
 */

namespace App\Http\Controllers;


use App\Components\HttpUtil;
use Illuminate\Support\Facades\Input;

class HomeController extends Controller {
    public function index() {
        return view('web.home.index');
    }

    public function download() {
        if (HttpUtil::isWX()) {
            $content = file_get_contents(PATH_RESOURCES . '/views/wx_download/wx_download.html');
            echo $content;
            return;
        }
        $detect = new \Mobile_Detect();
        $is_iphone = $detect->is('iphone');
        if ($is_iphone) {
            $hearder = ['Content-Type' => 'application/ipa;charset=utf-8'];
            return response()->download(storage_path('apk/powerfulfin.ipa'), 'powerfulfin.ipa', $hearder);
            //header('Location: https://itunes.apple.com/app/id1026601319?mt=8');
            //return;
        } else {
            $hearder = ['Content-Type' => 'application/apk;charset=utf-8'];
            return response()->download(storage_path('apk/powerfulfin.apk'), 'powerfulfin.apk', $hearder);
        }
    }

    /**
     * 申请分期二维码扫描之后的
     */
    public function qrscan()
    {
        if (!HttpUtil::isSelf()) {
            $url = "http://www." . DOMAIN_WEB . "/download?f=qr";
        } else {
            $oid = Input::get('oid');
            $url = "powerfulfin://apply?oid={$oid}";
        }
        HttpUtil::goUrl($url);
    }

}
