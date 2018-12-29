<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:59 PM
 */

namespace App\Models\Server\BU;


use App\Components\AliyunOSSUtil;
use App\Components\PFException;
use App\Models\ActiveRecord\ARPFSimg;
use App\Models\ActiveRecord\ARPFUsersAuthLog;

class BUUdcredit
{
    /**
     * 插入用户数据
     * @param $data
     * @return array|bool
     * @throws PFException
     */
    public static function updateData($data)
    {
        if (!$data) {
            return false;
        }
        $picArr = ['front_card', 'back_card', 'photo_get', 'photo_grid', 'photo_living'];
        foreach ($picArr as $key) {
            if (isset($data[$key]) && $data[$key]) {
                $result = BUUdcredit::saveImage($data['uid'], $data[$key], $key);
                $data[$key] = $result['path'] ? $result['path'] : '';
            } else {
                $data[$key] = '';
            }
        }
        return ARPFUsersAuthLog::_updateByOrder($data);
    }

    /**
     * 保存图片
     * @param $uid
     * @param $base64_image_content
     * @param $file_type
     * @return array
     */
    public static function saveImage($uid, $base64_image_content, $file_type)
    {
        try {
            $dir = 'simg' . DIRECTORY_SEPARATOR . date("Ym");
            $fileName = $uid . '_' . date('His') . '_' . intval(microtime(true)) . '_' . rand(1, 9999) . ".png";
            $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;
            if (!file_exists(PATH_STORAGE . '/' . $dir)) {
                mkdir(PATH_STORAGE . '/' . $dir,0777,true);
            }
            file_put_contents(PATH_STORAGE . '/' . $filePath, base64_decode($base64_image_content));
            AliyunOSSUtil::upload(AliyunOSSUtil::getLoanBucket(), $filePath, PATH_STORAGE . '/' . $filePath);
            $tmp = [
                'uid' => $uid,
                'path' => $filePath,
                'file_name' => $fileName,
                'bucket' => 'simg',
                'file_size' => filesize(PATH_STORAGE . '/' . $filePath),
                'file_suffix' => 'png',
                'file_type' => $file_type,
            ];
            ARPFSimg::addSimg($tmp);
            return ['path' => $filePath];
        } catch (PFException $exception) {
            //TODO
        }
    }

    /**
     * 有盾异步通知处理逻辑
     * @param $data
     * @return array|bool
     * @throws PFException
     */
    public static function NotifyHandle($data)
    {
        if ($data['result_auth'] === 'T') {
            $data['result_auth'] = ARPFUsersAuthLog::RESULT_AUTH_TRUE;
        } else {
            $data['result_auth'] = ARPFUsersAuthLog::RESULT_AUTH_FALSE;
        }
        $data['order'] = $data['no_order'];
        list($data['uid'], $order) = explode('_', $data['no_order']);
        unset($data['no_order']);
        $data['id_card'] = $data['id_no'];
        unset($data['id_no']);
        $validityDate = explode('-', $data['validity_period']);
        $data['idcard_start'] = str_replace('.', '-', $validityDate[0]);
        $data['idcard_expired'] = $validityDate[1] == '长期' ? '2099-01-01' : str_replace('.', '-', $validityDate[1]);
        $data['birthday'] = str_replace('.', '-', $data['birthday']);

        $params = ['creation_time', 'classify', 'oid_partner', 'sign', 'sign_field', 'risk_tag', 'validity_period', 'verify_status'];
        foreach ($params as $param) {
            if (array_key_exists($param, $data)) {
                unset($data[$param]);
            }
        }
        return BUUdcredit::updateData($data);
    }
}
