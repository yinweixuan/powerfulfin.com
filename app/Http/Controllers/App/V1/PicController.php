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
            if (empty($_FILES)) {
                throw new PFException(ERR_UPLOAD_CONTENT . ":无文件", ERR_UPLOAD);
            }
            $fileInfos = PicUtil::uploadSimgPic(DataBus::get('uid'));
            $urls = [];
            foreach ($fileInfos as $fileInfo) {
                AliyunOSSUtil::upload(AliyunOSSUtil::getLoanBucket(), $fileInfo['path'], $fileInfo['tmp_name']);
                $fileInfo['uid'] = DataBus::get('uid');
                $fileInfo['bucket'] = 'simg';
                unset($fileInfo['tmp_name']);
                ARPFSimg::addSimg($fileInfo);
                $url = AliyunOSSUtil::getAccessUrl(AliyunOSSUtil::getLoanBucket(), $fileInfo['path']);
                $urls[] = ['url' => $url, 'path' => $fileInfo['path'], 'type' => $fileInfo['file_type']];
            }

            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $urls);
        } catch (\Exception $exception) {
            OutputUtil::err($exception->getMessage(), $exception->getCode());
        }
    }
}
