<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/20
 * Time: 10:20 AM
 */

namespace App\Http\Controllers\App\V1;


use App\Components\OutputUtil;
use App\Components\PFException;
use App\Components\RedisUtil;
use App\Http\Controllers\App\AppController;
use App\Models\ActiveRecord\ARPFAreas;
use App\Models\DataBus;
use Illuminate\Support\Facades\Input;

class AreaController extends AppController
{
    public function __construct()
    {
        DataBus::get();
    }

    public function province()
    {
        try {
            $redis = RedisUtil::getInstance();
            $key = "PF_PROVINCE";
            $data = $redis->get($key);
            if ($data) {
                $list = json_decode($data, true);
            } else {
            $list = ARPFAreas::getAreas(0);
                $redis->set($key, json_encode($list), 86400);
            }
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $list);
        } catch (\Exception $exception) {
            OutputUtil::err(ERR_AREA_CONTENT, ERR_AREA);
        }
    }

    public function city()
    {
        try {
            $province = Input::get('province');
            if (empty($province) || !is_numeric($province)) {
                throw new PFException("提交参数错误");
            }

            $redis = RedisUtil::getInstance();
            $key = "PF_CITY_BY_PROVINCE_" . $province;
            $data = $redis->get($key);
            if ($data) {
                $list = json_decode($data, true);
            } else {
                $list = ARPFAreas::getAreas($province);
                $redis->set($key, json_encode($list), 86400);
            }
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $list);
        } catch (PFException $exception) {
            OutputUtil::err(ERR_AREA_CONTENT, ERR_AREA);
        }
    }

    public function area()
    {
        try {
            $city = Input::get('city');
            if (empty($city) || !is_numeric($city)) {
                throw new PFException("提交参数错误");
            }
            $redis = RedisUtil::getInstance();
            $key = "PF_GET_AREA_BY_CITY_" . $city;
            $data = $redis->get($key);
            if ($data) {
                $list = json_decode($data, true);
            } else {
                $list = ARPFAreas::getAreas($city);
                $redis->set($key, json_encode($list), 86400);
            }

            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $list);
        } catch (PFException $exception) {
            OutputUtil::err(ERR_AREA_CONTENT, ERR_AREA);
        }
    }
}

