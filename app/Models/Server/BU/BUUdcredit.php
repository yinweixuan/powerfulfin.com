<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:59 PM
 */

namespace App\Models\Server\BU;


use App\Models\ActiveRecord\ARPFUsersAuthLog;

class BUUdcredit
{
    /**
     * 插入用户数据
     * @param $data
     * @return array|bool
     */
    public static function updateData($data)
    {
        if (!$data) {
            return false;
        }
        $picArr = ['front_card', 'back_card', 'photo_get', 'photo_grid', 'photo_living'];
        foreach ($picArr as $key) {
            if (isset($data[$key]) && $data[$key]) {
                $result = BUUdcredit::saveImage($data[$key]);
                BUUdcredit::insertSimg($result['path'], $data['uid'], $data['sid']);
                $data[$key] = $result['result'] ? $result['path'] : '';
            } else {
                $data[$key] = '';
            }
        }

//        return ARPayUserAuth::_updateByOrder($data);
    }

    /**
     * 保存图片
     * @param $base64_image_content
     * @return array
     */
    public static function saveImage($base64_image_content)
    {
//        $date_dir = DIRECTORY_SEPARATOR . date("Ym") . DIRECTORY_SEPARATOR . date("d");
//        $dir = PATH_UPLOAD_SECRET . $date_dir;
//        if (!file_exists($dir)) {
//            mkdir($dir, 0777, true);
//        }
//        $fileName = date('His') . '_' . intval(microtime(true)) . '_' . rand(1, 9999) . ".png";
//        $filePath = $dir . DIRECTORY_SEPARATOR . $fileName;
//        $isSuccess = file_put_contents($filePath, base64_decode($base64_image_content));
//        $path = $date_dir . DIRECTORY_SEPARATOR . $fileName;
//        return ['result' => $isSuccess, 'path' => $path];
    }

    /**
     * 图片入库
     * @param $path 图片路径
     * @param int $uid 用户id
     * @param int $sid 机构id
     */
    public static function insertSimg($path, $uid = 0, $sid = 0)
    {
//        list($null, $yearMonth, $day, $fileName) = explode('/', $path);
//        $paySimg = array(
//            'path' => $path,
//            'year_month' => $yearMonth,
//            'day' => $day,
//            'file_name' => $fileName,
//            'file_suffix' => PicUtil::getSuffix($fileName),
//            'type' => ARPaySimg::SIMG_TYPE_INSTALMENT,
//            'uid' => $uid,
//            'lid' => 0,
//            'sid' => $sid,
//            'sbid' => 0,
//            'create_time' => date('Y-m-d H:i:s'),
//        );
//        $check = ARPaySimg::getSimgByPath($path);
//        if (!$check) {
//            ARPaySimg::_insert($paySimg);
//        }
    }

    /**
     * 有盾异步通知处理逻辑
     * @param $data
     * @return array|bool
     */
    public static function NotifyHandle($data)
    {
        if ($data['result_auth'] === 'T') {
            $data['result_auth'] = ARPFUsersAuthLog::RESULT_AUTH_TRUE;
        } else {
            $data['result_auth'] = ARPFUsersAuthLog::RESULT_AUTH_FALSE;
        }
        $data['order'] = $data['no_order'];
        list($data['uid'], $data['cid'], $data['sid'], $order) = explode('|', $data['no_order']);
        unset($data['no_order']);
        $data['id_card'] = $data['id_no'];
        unset($data['id_no']);
        $data['sex'] = $data['gender'] == '女' ? 2 : 1;
        unset($data['gender']);
        $validityDate = explode('-', $data['validity_period']);
        $data['idcard_start'] = str_replace('.', '-', $validityDate[0]);
        $data['idcard_expired'] = $validityDate[1] == '长期' ? '2099-01-01' : str_replace('.', '-', $validityDate[1]);
//        return BUUdcredit::updateData($data);
    }

    public static function checkUdCreditOnOROff($uid = 0, $sid = 0)
    {
//        if ($uid == 0 || !is_numeric($uid)) {
//            return true;
//        }
//
//        $userAuth = ARPayUserAuth::getByUidWithResultAuthTrue($uid, 0, 0, $sid);
//        if (empty($userAuth)) {
//            return true;
//        } else {
//            $pay_time = date('Y-m-d H:i:s', strtotime("-30 days"));
//            $sql = "SELECT * FROM " . ARLoan::getTableName() . " WHERE `status`>=" . LOAN_100_REPAY . " AND `pay_time` >='{$pay_time}';";
//            $loan = DBUtil::getRow($sql);
//            if (empty($loan)) {
//                return true;
//            }
//            if ($loan['sid'] != $sid) {
//                return true;
//            } else {
//                return false;
//            }
//        }
    }
}
