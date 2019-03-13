<?php

/**
 * 富登ftp
 */

namespace App\Models\Fcs;

use App\Components\AliyunOSSUtil;

class FcsFtp {

    public static $conn;

    /**
     * 获取根目录
     */
    public static function getRootPath() {
        //富登测试和正式环境使用的ftp是一个，区分目录，前后带/
        //大圣与课栈使用同一ftp服务
        if (config('app.env') == 'local') {
            $root_path = config('fcs.ftp_root') . '_test/';
        } else {
            $root_path = config('fcs.ftp_root') . 'pf/';
        }
        return $root_path;
    }

    /**
     * 计算上传路径
     */
    public static function getUploadDir($lid) {
        $conn = self::getConnection();
        $upload_dir = floor($lid / 10000) . '/' . $lid;
        $dirs = explode('/', $upload_dir);
        foreach ($dirs as $dir) {
            if (!@ftp_chdir($conn, $dir)) {
                ftp_mkdir($conn, $dir);
                ftp_chdir($conn, $dir);
            }
        }
        $root_path = self::getRootPath();
        ftp_chdir($conn, $root_path);
        return $upload_dir;
    }

    /**
     * 获取本地文件路径
     */
    public static function getLocalFileDir($lid) {
        $file_dir = PATH_STORAGE . '/data/fcs/' . (floor($lid / 10000)) . '/' . $lid;
        if (!file_exists($file_dir)) {
            mkdir($file_dir, 0755, true);
        }
        return $file_dir;
    }

    /**
     * 获取ftp连接
     */
    public static function getConnection() {
        $root_path = self::getRootPath();
        if (is_resource(self::$conn)) {
            ftp_chdir(self::$conn, $root_path);
            return self::$conn;
        }
        self::$conn = ftp_ssl_connect(config('fcs.ftp_server'));
        ftp_login(self::$conn, config('fcs.ftp_username'), config('fcs.ftp_password'));
        ftp_chdir(self::$conn, $root_path);
        ftp_pasv(self::$conn, true);
        return self::$conn;
    }

    /**
     * 上传文件，不支持Windows
     */
    public static function upload($lid, $file) {
        if (!is_file($file)) {
            return '';
        }
        $conn = self::getConnection();
        $upload_dir = self::getUploadDir($lid);
        $remote_file = $upload_dir . '/' . basename($file);
        //已有文件就删除重新传
        $exist_files = ftp_nlist($conn, $upload_dir);
        if (is_array($exist_files)) {
            if (in_array($remote_file, $exist_files)) {
                ftp_delete($conn, $remote_file);
            }
        }
        $r = ftp_put($conn, $remote_file, $file, FTP_BINARY);
        if ($r) {
            return self::parsePath($remote_file);
        } else {
            //上传失败
            throw new \Exception('文件上传失败');
        }
    }

    /**
     * 转化地址为富登可解析的地址
     */
    public static function parsePath($remote_file) {
        $root_path = self::getRootPath();
        return str_replace('/', '\\', config('fcs.fcs_ftp_prefix') . $root_path . $remote_file);
    }

    /**
     * oss文件上传富登ftp
     */
    public static function uploadFromOss($lid, $oss_object) {
        $local_file_dir = self::getLocalFileDir($lid);
        $local_file = $local_file_dir . '/' . basename($oss_object);
        AliyunOSSUtil::download(AliyunOSSUtil::getLoanBucket(), $oss_object, $local_file);
        $fcs_filename = self::upload($lid, $local_file);
        return $fcs_filename;
    }

    /**
     * 下载文件
     * $file_list 二维数组，[[本地文件，远程文件],[],[]]
     */
    public static function download($file_list) {
        $conn = self::getConnection();
        foreach ($file_list as $item) {
            $r = ftp_get($conn, $item[0], mb_convert_encoding($item[1], 'GBK', 'UTF-8'), FTP_BINARY);
            if (!$r) {
                //合同下载失败
                throw new \Exception('文件下载失败');
            }
        }
    }

}
