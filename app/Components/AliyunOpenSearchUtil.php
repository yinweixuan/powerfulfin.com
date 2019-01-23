<?php

namespace App\Components;

require_once __DIR__ . '/../Libraries/aliyun-opensearch-3.1.0/OpenSearch/Autoloader/Autoloader.php';

use App\Models\ActiveRecord\ARPFOrg;
use OpenSearch\Client\DocumentClient;
use OpenSearch\Client\OpenSearchClient;
use OpenSearch\Client\SearchClient;
use OpenSearch\Util\SearchParamsBuilder;

class AliyunOpenSearchUtil {

    /**
     * 内网host
     */
    const HOST_INNER = 'http://intranet.opensearch-cn-beijing.aliyuncs.com';

    /**
     * 外网host
     */
    const HOST_PUBLIC = 'http://opensearch-cn-beijing.aliyuncs.com';

    /**
     * 机构搜索
     */
    const APP_SCHOOL = 'os_pf_org';

    /**
     * @var null
     */
    const TABLE_SCHOOL = 'pf_org';

    public static $client = null;
    public static $search_client = null;

    /**
     * 获取访问域名
     */
    public static function getHost() {
//        if (config('app.env') == 'local') {
            return self::HOST_PUBLIC;
//        } else {
//            return self::HOST_INNER;
//        }
    }

    /**
     * 获取client
     */
    public static function getClient() {
        if (!is_object(self::$client)) {
            self::$client = new OpenSearchClient(env('ALIYUN_OPENSEARCH_ACCESS_KEY'), env('ALIYUN_OPENSEARCH_ACCESS_SECRET'), self::getHost());
        }
        return self::$client;
    }

    /**
     * 获取搜索client
     */
    public static function getSearchClient() {
        if (!is_object(self::$search_client)) {
            self::$search_client = new SearchClient(self::getClient());
        }
        return self::$search_client;
    }

    /**
     * 搜索
     */
    public static function search($params) {
        $search_client = self::getSearchClient();
        $ret = $search_client->execute($params->build());
        $result = json_decode($ret->result, true);
        return $result;
    }

    /**
     * 获取基本搜索参数
     */
    public static function getSearchParams($app_name, $query, $page = 1, $pagesize = 10) {
        $params = new SearchParamsBuilder();
        $params->setAppName($app_name);
        $params->setQuery($query);
        $params->setFormat('json');
        $params->setStart(($page - 1) * $pagesize);
        $params->setHits($pagesize);
        return $params;
    }

    public static function searchSchool($keyword, $lng = null, $lat = null, $page = 1, $pagesize = 10) {
        $query = '(id:"' . $keyword . '"';
        $keyword_array = explode(' ', $keyword);
        foreach ($keyword_array as $word) {
            $query .= ' OR default:"' . $word . '")';
        }
        $query .= ' AND status:"SUCCESS" AND can_loan:"SUCCESS"';
        if ($lng > 1 && $lat > 1) {
            $query .= '&&sort=+distance(lng,lat,"' . $lng . '","' . $lat . '");-RANK';
        }
        $params = self::getSearchParams(self::APP_SCHOOL, $query, $page, $pagesize);
        $result = self::search($params);
        $school_list = [];
        $school_list['total'] = $result['result']['total'] ? $result['result']['total'] : 0;
        $school_list['page'] = $page;
        $school_list['pagesize'] = $pagesize;
        $school_list['list'] = empty($result['result']['items']) ? [] : $result['result']['items'];
        return $school_list;
    }

    public static function pushOrgData($oid1, $oid2 = null) {
        if (config('app.env') == 'local') {
            return null;
        }
        if (!$oid2) {
            $oid2 = $oid1;
        }
        $document_client = new DocumentClient(self::getClient());
        $op = 'ADD';//测试add也有更新功能
        $limit = 1000;//不知道最大限是多少，差不多就是1000了。
        $data = ARPFOrg::getSearchList($oid1, $oid2);
        $docs_to_upload = array();
        $i = 0;
        foreach ($data as $k => $org) {
            $item = array();
            $item['cmd'] = $op;
            $item['fields'] = array(
                'id' => $org['id'],
                'name' => $org['org_name'],
                'lat' => $org['org_lat'],
                'lng' => $org['org_lng'],
                'address' => $org['org_address'],
                'hid' => $org['hid'],
                'status' => $org['status'],
                'can_loan' => $org['can_loan'],
                'province' => $org['org_province'],
                'city' => $org['org_city'],
                'area' => $org['org_area'],
                'short_name' => $org['short_name'],
                'create_time' => $org['create_time'],
                'update_time' => $org['update_time'],
            );
            $docs_to_upload[] = $item;
            $i++;
            if ($i >= $limit || $k == count($data) - 1) {
                $json = json_encode($docs_to_upload);
                $document_client->push($json, self::APP_SCHOOL, self::TABLE_SCHOOL);
                $docs_to_upload = array();
                $i = 0;
            }
        }
        return count($data);
    }

}
