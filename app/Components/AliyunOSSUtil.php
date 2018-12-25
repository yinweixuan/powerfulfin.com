<?php

namespace App\Components;

require_once __DIR__ . '/../Libraries/aliyun-oss-2.3.0/autoload.php';
require_once __DIR__ . '/../Libraries/aliyun-oss-2.3.0/OSS/OssClient.php';

/**
 * 阿里云对象存储服务
 */
class AliyunOSSUtil {

    /**
     * 订单信息bucket
     */
    const BUCKET_LOAN = 'kz-loan';

    /**
     * 测试bucket
     */
    const BUCKET_TEST = 'hx-emr-dev';

    /**
     * 访问域名（外网）
     */
    const ENDPOINT_PUBLIC = 'http://oss-cn-beijing.aliyuncs.com';

    /**
     * 访问域名（内网)
     */
    const ENDPOINT_INNER = 'http://oss-cn-beijing-internal.aliyuncs.com';

    public static $oss_client = null;

    /**
     * 获取访问域名
     */
    public static function getEndpoint() {
        if (config('app.env') == 'local') {
            return self::ENDPOINT_PUBLIC;
        } else {
            return self::ENDPOINT_INNER;
        }
    }

    /**
     * 获取贷款bucket
     */
    public static function getLoanBucket() {
        if (config('app.env') == 'local') {
            return self::BUCKET_TEST;
        } else {
            return self::BUCKET_LOAN;
        }
    }

    /**
     * 获取oss client
     */
    public static function getOSSClient() {
        if (!is_object(self::$oss_client)) {
            self::$oss_client = new \OSS\OssClient(env('ALIYUN_OSS_ACCESS_KEY_ID'), env('ALIYUN_OSS_ACCESS_KEY_SECRET'), self::getEndpoint());
        }
        return self::$oss_client;
    }

    /**
     * 计算分期文件前缀
     */
    public static function getObjectPrefix($resource, $sbid, $lid) {
        return 'data/' . $resource . '/' . $sbid . '/' . $lid;
    }

    /**
     * 上传文件
     * @param string $object 上传后文件的名称（包含前缀）
     * @param string $file 本地文件
     * @param bool $overwrite 是否覆盖已上传文件，默认否
     */
    public static function upload($bucket, $object, $file, $overwrite = false) {
        if (!is_file($file)) {
            return false;
        }
        $oss_client = self::getOSSClient();
        if (!$overwrite) {
            $exist = $oss_client->doesObjectExist($bucket, $object);
            if ($exist) {
                return true;
            }
        }
        $content = file_get_contents($file);
        $result = $oss_client->putObject($bucket, $object, $content);
        return $result;
    }

    /**
     * 上传贷款文件
     * @param mixed $files 单文件字符串或多文件数组
     */
    public static function uploadLoanFile($resource, $sbid, $lid, $files, $overwrite = false) {
        if (is_string($files)) {
            $file_list = [$files];
        } elseif (is_array($files)) {
            $file_list = $files;
        } else {
            return;
        }
        foreach ($file_list as $file) {
            if (!is_file($file)) {
                continue;
            }
            $object = self::getObjectPrefix($resource, $sbid, $lid) . '/' . basename($file);
            self::upload(self::getLoanBucket(), $object, $file, $overwrite);
        }
        return;
    }

    /**
     * 上传分期协议
     */
    public static function uploadLoanContract($resource, $sbid, $lid, $file, $overwrite = false) {
        if (!is_file($file)) {
            return;
        }
        $object = self::getObjectPrefix($resource, $sbid, $lid) . '/signed_loan_contract.pdf';
        $r = self::upload(self::getLoanBucket(), $object, $file, $overwrite);
        return $r;
    }

    /**
     * 判断文件是否存在
     */
    public static function isFileExist($bucket, $object) {
        $oss_client = self::getOSSClient();
        $exist = $oss_client->doesObjectExist($bucket, $object);
        return $exist;
    }

    public static function getObjectList($bucket, $options) {
        $oss_client = self::getOSSClient();
        $list_object_info = $oss_client->listObjects($bucket, $options);
        $object_list = $list_object_info->getObjectList();
        return $object_list;
    }

    /**
     * 获取订单资料文件列表
     */
    public static function getLoanFileList($resource, $sbid, $lid) {
        $options = array(
            'delimiter' => '',
            'prefix' => self::getObjectPrefix($resource, $sbid, $lid),
            'max-keys' => 100,
            'marker' => '',
        );
        $file_list = array();
        $oss_client = self::getOSSClient();
        $list_object_info = $oss_client->listObjects(self::getLoanBucket(), $options);
        $object_list = $list_object_info->getObjectList();
        foreach ($object_list as $object) {
            if ($object->getKey()) {
                $file_list[] = $object->getKey();
            }
        }
        return $file_list;
    }

    /**
     * 下载文件
     * @param string $object 需要下载的文件
     * @param string $local_file 下载到本地的文件名（包含路径）
     */
    public static function download($bucket, $object, $local_file) {
        $options = array(
            \OSS\OssClient::OSS_FILE_DOWNLOAD => $local_file
        );
        try {
            $oss_client = self::getOSSClient();
            $result = $oss_client->getObject($bucket, $object, $options);
        } catch (\OSS\Core\OssException $ex) {
            
        }
        return $result;
    }

    /**
     * 获取文件访问url
     * @param string $object 需要访问的文件
     * @param int $timeout 链接过期时间，秒
     */
    public static function getAccessUrl($bucket, $object, $timeout = 3600) {
        $oss_client = self::getOSSClient();
        $signed_url = $oss_client->signUrl($bucket, $object, $timeout);
        return $signed_url;
    }

    /**
     * 获取订单文件访问链接
     */
    public static function getLoanFileUrl($resource, $sbid, $lid, $filename, $timeout = 3600) {
        $bucket = self::getLoanBucket();
        $object = self::getObjectPrefix($resource, $sbid, $lid) . '/' . $filename;
        return self::getAccessUrl($bucket, $object, $timeout);
    }

}
