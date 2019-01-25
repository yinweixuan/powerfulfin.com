<?php

namespace App\Models\Fcs;

class FcsFtp {

    public static $conn;

    /**
     * 获取根目录
     */
    public static function getRootPath() {
        //富登测试和正式环境使用的ftp是一个，区分目录，前后带/
        if (config('app.env') == 'local') {
            $root_path = config('fcs.ftp_root') . '_test/';
        } else {
            $root_path = config('fcs.ftp_root');
        }
        return $root_path;
    }

    /**
     * 计算上传路径
     */
    public static function getUploadDir($lid) {
        return floor($lid / 10000) . '/' . $lid;
    }

    /**
     * 获取ftp连接
     */
    public static function getConnection() {
        if (is_resource(self::$conn)) {
            return self::$conn;
        }
        self::$conn = ftp_ssl_connect(config('fcs.ftp_server'));
        ftp_login(self::$conn, config('fcs.ftp_username'), config('fcs.ftp_password'));
        $root_path = self::getRootPath();
        ftp_chdir(self::$conn, $root_path);
        ftp_pasv(self::$conn, true);
        return self::$conn;
    }

    /**
     * 上传文件，不支持Windows
     */
    public static function upload($lid, $file) {
        $conn = self::getConnection();
        $upload_dir = self::getUploadDir($lid);
        @ftp_mkdir($conn, $upload_dir);
        $exist_files = ftp_nlist($conn, $lid);
        $remote_file = $upload_dir . '/' . basename($file);
        //已有文件就删除重新传
        if (in_array($remote_file, $exist_files)) {
            ftp_delete($conn, $remote_file);
        }
        $r = ftp_put($conn, $remote_file, $file, FTP_BINARY);
        if ($r) {
            $root_path = self::getRootPath();
            return str_replace('/', '\\', config('fcs.fcs_ftp_prefix') . $root_path . $remote_file);
        } else {
            //上传失败
            throw new \Exception('文件上传失败');
        }
    }

    /**
     * 下载文件
     * @param array $file_list 二维数组，[[本地文件，远程文件],[],[]]
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
