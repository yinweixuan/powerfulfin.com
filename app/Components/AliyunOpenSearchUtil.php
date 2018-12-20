<?php

namespace App\Components;

require_once __DIR__ . '/../Libraries/aliyun-opensearch-3.1.0/OpenSearch/Autoloader/Autoloader.php';

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
    const APP_SCHOOL = 'kz_school';

    public static $client = null;
    public static $search_client = null;

    /**
     * 获取访问域名
     */
    public static function getHost() {
        if (config('app.env') == 'local') {
            return self::HOST_PUBLIC;
        } else {
            return self::HOST_INNER;
        }
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
        $query = 'default:"' . $keyword . '" OR id:"' . $keyword . '"';
        if ($lng > 1 && $lat > 1) {
            $query .= '&&sort=+distance(lng,lat,"' . $lng . '","' . $lat . '")';
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

}
