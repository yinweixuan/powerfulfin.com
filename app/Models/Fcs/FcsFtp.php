<?php

namespace App\Models\Fcs;

use App\Models\Fcs\FcsCommon;
use App\Models\Fcs\FcsContract;
use App\Models\Fcs\FcsField;
use App\Models\Fcs\FcsFtp;
use App\Models\Fcs\FcsHttp;
use App\Models\Fcs\FcsLoan;
use App\Models\Fcs\FcsQueue;
use App\Models\Fcs\FcsSoap;

class FcsFtp {

    const SERVER = 'ftp.hifcs.com';
    const USERNAME = 'semmchina\ftp_kezhanw';
    const PASSWORD = 'Password@201709';
    const ROOTPATH = '/ftproot/Kezhanw/';

    public static function getRootPath() {
        //富登测试和正式环境使用的ftp是一个，区分目录
        if (config('app.env') == 'local') {
            $root_path = self::ROOTPATH . '_test/';
        } else {
            $root_path = self::ROOTPATH;
        }
        return $root_path;
    }

    public static function getConnection() {
        $conn = ftp_ssl_connect(self::SERVER);
        ftp_login($conn, self::USERNAME, self::PASSWORD);
        $root_path = self::getRootPath();
        ftp_chdir($conn, $root_path);
        ftp_pasv($conn, true);
        return $conn;
    }

    /**
     * 上传文件，不支持Windows
     */
    public static function upload($lid, $file_list, $index = false) {
        $conn = self::getConnection();
        try {
            @ftp_mkdir($conn, $lid);
        } catch (Exception $e) {
            
        }
        $exist_files = ftp_nlist($conn, $lid);
        $n = 1;
        foreach ($file_list as $file) {
            if ($index) {
                $remote_file = $lid . '/' . $n . '_' . basename($file);
                $n++;
            } else {
                $remote_file = $lid . '/' . basename($file);
            }
            //已有文件就删除重新传
            if (in_array($remote_file, $exist_files)) {
                ftp_delete($conn, $remote_file);
            }
            $r = ftp_put($conn, $remote_file, $file, FTP_BINARY);
            if (!$r) {
                //上传失败
                ftp_close($conn);
                throw new Exception('上传文件失败');
            }
        }
        ftp_close($conn);
    }

    /**
     * 取上传后的文件
     */
    public static function getFile($lid, $file, $prefix = null) {
        $root_path = self::getRootPath();
        if ($prefix !== null) {
            return str_replace('/', '\\', '//idcnas' . $root_path . $lid . '/' . $prefix . '_' . basename($file));
        } else {
            return str_replace('/', '\\', '//idcnas' . $root_path . $lid . '/' . basename($file));
        }
    }

    /**
     * 下载文件
     * @param array $file_list 二维数组，[[本地文件，远程文件],[],[]]
     */
    public static function download($file_list) {
        $conn = self::getConnection();
        foreach ($file_list as $item) {
            $r = ftp_get($conn, $item[0], $item[1], FTP_BINARY);
            if (!$r) {
                //合同下载失败
                throw new Exception('下载失败');
            }
        }
        ftp_close($conn);
    }

}
