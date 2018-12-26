<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/26
 * Time: 10:04 AM
 */

namespace App\Http\Controllers\App\V1;


use App\Components\AliyunOSSUtil;
use App\Components\OutputUtil;
use App\Components\PFException;
use App\Components\PicUtil;
use App\Http\Controllers\App\AppController;
use App\Models\ActiveRecord\ARPFSimg;
use App\Models\DataBus;

class PicController extends AppController
{
    public function __construct()
    {
        parent::__construct();
        $this->checkLogin();
    }

    public function upload()
    {
        try {
            $fileInfos = PicUtil::uploadPic();
            $urls = [];
            foreach ($fileInfos as $fileInfo) {
                AliyunOSSUtil::upload(AliyunOSSUtil::getLoanBucket(), 'simg' . $fileInfo['fullName'], $fileInfo['tmp_name']);
                $simg = [
                    'path' => $fileInfo['path'],
                    'file_name' => $fileInfo['fileName'],
                    'file_size' => $fileInfo['size'],
                    'file_suffix' => $fileInfo['file_suffix'],
                    'year_month' => $fileInfo['year_month'],
                    'day' => $fileInfo['day'],
                    'type' => $fileInfo['type'],
                    'uid' => DataBus::get('uid'),
                ];
                ARPFSimg::addSimg($simg);
                $url = AliyunOSSUtil::getAccessUrl(AliyunOSSUtil::getLoanBucket(), 'simg' . $fileInfo['fullName']);
                $urls[$fileInfo['type']] = $url;
            }
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK_CONTENT, $urls);
        } catch (PFException $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode());
        }
    }
}
