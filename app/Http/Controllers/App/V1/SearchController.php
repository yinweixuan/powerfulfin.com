<?php

namespace App\Http\Controllers\App\V1;

use Illuminate\Support\Facades\Input;
use App\Components\OutputUtil;
use App\Components\AliyunOpenSearchUtil;

/**
 * 搜索接口
 */
class SearchController {

    /**
     * 查询机构
     * @param string $keyword 关键字
     * @param double $lng 经度
     * @param double $lat 纬度
     */
    public function school() {
        $keyword = Input::get('keyword');
        $lng = Input::get('lng');
        $lat = Input::get('lat');
        $page = Input::get('page', '1');
        $pagesize = Input::get('pagesize', '10');
        try {
            $data = AliyunOpenSearchUtil::searchSchool($keyword, $lng, $lat, $page, $pagesize);
            if (!empty($data['list'])) {
                $new_list = [];
                foreach ($data['list'] as $item) {
                    $new_item = [];
                    $new_item['id'] = $item['id'];
                    $new_item['name'] = $item['name'];
                    $new_item['address'] = $item['address'];
                    $new_list[] = $new_item;
                }
                $data['list'] = $new_list;
            }
            OutputUtil::info(ERR_OK_CONTENT, ERR_OK, $data);
        } catch (\Exception $ex) {
            OutputUtil::err($ex->getMessage(), $ex->getCode());
        }
    }

}
