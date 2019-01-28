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
use App\Http\Controllers\App\AppController;
use App\Models\ActiveRecord\ARPFAreas;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redis;

class AreaController extends AppController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function province()
    {
        try {
            $key = "PF_PROVINCE";
            if (Redis::exists($key)) {
                $data = Redis::get($key);
                $list = json_decode($data, true);
            } else {
                $list = ARPFAreas::getAreas(0);
                Redis::set($key, json_encode($list), 86400);
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

            $key = "PF_CITY_BY_PROVINCE_" . $province;
            if (Redis::exists($key)) {
                $data = Redis::get($key);
                $list = json_decode($data, true);
            } else {
                $list = ARPFAreas::getAreas($province);
                Redis::set($key, json_encode($list), 86400);
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
            $key = "PF_GET_AREA_BY_CITY_" . $city;
            if (Redis::exists($key)) {
                $data = Redis::get($key);
                $list = json_decode($data, true);
            } else {
                $list = ARPFAreas::getAreas($city);
                Redis::set($key, json_encode($list), 86400);
            }
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $list);
        } catch (PFException $exception) {
            OutputUtil::err(ERR_AREA_CONTENT, ERR_AREA);
        }
    }
}

