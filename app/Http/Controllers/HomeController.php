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

    /**
     * 下载页面
     */
    public function download() {
        $detect = new \Mobile_Detect();
        $isIOS = $detect->is('iphone') || $detect->is('ios');
        $isWX = HttpUtil::isWX();
        return view('web.home.download', ['isWX' => $isWX, 'isIOS' => $isIOS,]);
        /*
        $content = file_get_contents(PATH_RESOURCES . '/views/wx_download/wx_download.html');
        echo $content;
        return;
        */
    }

    /**
     * 下载包的内容
     */
    public function downloadPackage()
    {
        $detect = new \Mobile_Detect();
        $isIOS = $detect->is('iphone') || $detect->is('ios');
        if ($isIOS) {
            $hearder = ['Content-Type' => 'application/octet-stream;charset=utf-8'];
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
