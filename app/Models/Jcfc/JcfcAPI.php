<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:11 PM
 */

namespace App\Models\Jcfc;


use App\Components\PFException;
use App\Models\Jcfc\api\CF201001;
use App\Models\Jcfc\api\CF201002;
use App\Models\Jcfc\api\CF201003;
use App\Models\Jcfc\api\CF201004;
use App\Models\Jcfc\api\CF201005;
use App\Models\Jcfc\api\CF201006;
use App\Models\Jcfc\api\CF201007;
use App\Models\Jcfc\api\CF201008;
use App\Models\Jcfc\api\CF201009;
use App\Models\Jcfc\api\CF201010;
use App\Models\Jcfc\api\CF201011;
use App\Models\Jcfc\api\CF201012;
use App\Models\Jcfc\api\CF201013;
use App\Models\Jcfc\api\CF201014;
use App\Models\Jcfc\api\CF201015;
use App\Models\Jcfc\api\CF201016;
use App\Models\Jcfc\api\CF201017;
use App\Models\Jcfc\api\CF201018;
use App\Models\Jcfc\api\CF201019;
use App\Models\Jcfc\api\CF201020;
use App\Models\Jcfc\api\CF201021;
use App\Models\Jcfc\api\CF201022;
use App\Models\Jcfc\api\CF201024;
use App\Models\Jcfc\api\CF201025;
use App\Models\Jcfc\api\CF201035;

class JcfcAPI extends JcfcInit
{
    /**
     * 接口返回交易成功
     */
    const API_RESULT_SUCCESS = 0;

    const API_RESULT_EC = 'ec';


    /**
     * API接口迎神
     * @var array
     */
    protected static $_api_map = array(
        'CF201001' => array('models' => 'CF201001', 'function' => 'getParams', 'is_object' => false),
        'CF201002' => array('models' => 'CF201002', 'function' => 'getParams', 'is_object' => false),
        'CF201003' => array('models' => 'CF201003', 'function' => 'getParams', 'is_object' => false),
        'CF201004' => array('models' => 'CF201004', 'function' => 'getParams', 'is_object' => false),
        'CF201005' => array('models' => 'CF201005', 'function' => 'getParams', 'is_object' => false),
        'CF201006' => array('models' => 'CF201006', 'function' => 'getParams', 'is_object' => false),
        'CF201007' => array('models' => 'CF201007', 'function' => 'getParams', 'is_object' => false),
        'CF201008' => array('models' => 'CF201008', 'function' => 'getParams', 'is_object' => false),
        'CF201009' => array('models' => 'CF201009', 'function' => 'getParams', 'is_object' => false),
        'CF201010' => array('models' => 'CF201010', 'function' => 'getParams', 'is_object' => false),
        'CF201011' => array('models' => 'CF201011', 'function' => 'getParams', 'is_object' => false),
        'CF201012' => array('models' => 'CF201012', 'function' => 'getParams', 'is_object' => false),
        'CF201013' => array('models' => 'CF201013', 'function' => 'getParams', 'is_object' => false),
        'CF201014' => array('models' => 'CF201014', 'function' => 'getParams', 'is_object' => false),
        'CF201015' => array('models' => 'CF201015', 'function' => 'getParams', 'is_object' => false),
        'CF201016' => array('models' => 'CF201016', 'function' => 'getParams', 'is_object' => false),
        'CF201017' => array('models' => 'CF201017', 'function' => 'getParams', 'is_object' => false),
        'CF201018' => array('models' => 'CF201018', 'function' => 'getParams', 'is_object' => false),
        'CF201019' => array('models' => 'CF201019', 'function' => 'getParams', 'is_object' => false),
        'CF201020' => array('models' => 'CF201020', 'function' => 'getParams', 'is_object' => false),
        'CF201021' => array('models' => 'CF201021', 'function' => 'getParams', 'is_object' => false),
        'CF201022' => array('models' => 'CF201022', 'function' => 'getParams', 'is_object' => false),
        'CF201024' => array('models' => 'CF201024', 'function' => 'getParams', 'is_object' => false),
        'CF201025' => array('models' => 'CF201025', 'function' => 'getParams', 'is_object' => false),
        'CF201035' => array('models' => 'CF201035', 'function' => 'getParams', 'is_object' => false),
    );

    /**
     * 执行方法
     * @param $api 接口名称
     * @param array $params 参数
     * @return mixed
     * @throws PFException
     */
    public static function exec($api, $params = array())
    {
        try {
            $api = strtoupper($api);
            if (!array_key_exists($api, self::$_api_map)) {
                throw new PFException("API接口不存在，请检查后重试");
            }
            $apiArr = self::$_api_map[$api];
            $model = $apiArr['models'];
            $function = $apiArr['function'];
            $isObject = $apiArr['is_object'];
            self::$_api = $api;

            if (!file_exists(dirname(__FILE__) . "/api/{$api}.php")) {
                throw new PFException("API-SDK缺失");
            } else {
                if ($isObject) {
                    $new = new $model();
                    $newParams = $new->$function($params);
                } else {
                    $newParams = $model::$function($params);
                }

                $result = self::httpRequest($newParams);
                return json_decode($result, true);
            }
        } catch (PFException $exception) {
            throw new PFException($exception->getMessage());
        }
    }
}
