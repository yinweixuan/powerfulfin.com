<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/26
 * Time: 10:39 AM
 */

namespace App\Components;


class PicUtil
{
    const RAND_BEGIN = 1;
    const RAND_END = 9999;
    const THUMB_WIDTH = 960;
    const MIDDLE_WIDTH = 2000;
    const MIDDLE_SIZE = 3145728;

    /**
     * 获得后缀名
     * @param type $str
     * @return bool|string
     */
    public static function getSuffix($str)
    {
        $pos = strrpos($str, '.');
        if ($pos !== false) {
            return substr($str, $pos + 1);
        } else {
            return '';
        }
    }

    /**
     * 允许上传的文件类型
     * @var type
     */
    public static $picType = array(
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/x-png' => 'png',
        'application/octet-stream' => 'jpg',
    );

    /**
     * 上传图片到服务器
     * @return array
     * @throws PFException
     */
    public static function uploadSimgPic()
    {
        $ret = array();
        if (empty($_FILES)) {
            return $ret;
        }
        $files = array();
        foreach ($_FILES as $key => $file) {
            if (is_array($file['name'])) {
                foreach ($file as $k => $v) {
                    $count = count($v);
                    for ($i = 0; $i < $count; $i++) {
                        $files[$key . '_' . $i][$k] = $v[$i];
                    }
                }
            } else {
                $files[$key] = $file;
            }
        }
        $fileInfo = [];
        foreach ($files as $key => $file) {
            foreach ($file as $k => $v) {
                if (count($k) > 1) {
                    if (is_array($file[$k]) && $v) {
                        $file[$k] = array_shift($v);
                    }
                }
            }

            switch ($file['error']) {
                case 1:
                    $msg = '文件大小超出了服务器的空间大小';
                    break;
                case 2:
                    $msg = '要上传的文件大小超出浏览器限制';
                    break;
                case 3:
                    $msg = '文件仅部分被上传';
                    break;
                case 4:
                    $msg = '没有找到要上传的文件';
                    break;
                case 5:
                    $msg = '服务器临时文件夹丢失';
                    break;
                case 6:
                    $msg = '文件写入到临时文件夹出错';
                    break;
                default:
                    $msg = false;
                    break;
            }
            if ($msg) {
                throw new PFException($msg, ERR_UPLOAD);
            }
            if (empty($file['tmp_name'])) {
                continue;
            }

            $imageSize = getimagesize($file['tmp_name']);
            if (!$imageSize) {
                throw new PFException("文件上传失败,请选择正确的图片格式", ERR_UPLOAD);
            }
            if ($file['error'] != 0) {
                throw new PFException("文件 {$key} 上传失败,错误码:{$file['error']}", ERR_UPLOAD);
            }

            if (!isset(self::$picType[$file['type']])) {
                throw new PFException("不支持上传 {$file['type']} 类型的文件", ERR_UPLOAD);
            }

            $suffix = self::$picType[$file['type']];
            //计算保存路径
            $year_month = date("Ym");
            $dir = 'simg' . DIRECTORY_SEPARATOR . $year_month;
            do {
                $fileName = date('His') . '_' . intval(microtime(true)) . '_' . rand(self::RAND_BEGIN, self::RAND_END) . ".{$suffix}";
                $path = DIRECTORY_SEPARATOR . $fileName;
            } while (file_exists($dir . $path));
            $tmp = [
                'path' => $dir . $path,
                'file_name' => $fileName,
                'file_size' => $file['size'],
                'file_suffix' => $suffix,
                'tmp_name' => $file['tmp_name'],
                'file_type' => $key,
            ];
            $fileInfo[] = $tmp;
        }
        return $fileInfo;
    }
}
