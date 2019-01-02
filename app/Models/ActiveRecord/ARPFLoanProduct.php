<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:48 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\RedisUtil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFLoanProduct extends Model
{
    protected $table = 'pf_loan_product';
    const TABLE_NAME = 'pf_loan_product';

    public $timestamps = false;

    /**
     * X+Y模式的费率模板
     */
    const LOAN_TYPE_XY = 1;

    /**
     * 贴息模式的费率模板
     */
    const LOAN_TYPE_DISCOUNT = 2;
    /**
     * 贴息等额模式的费率模板
     */
    const LOAN_TYPE_EQUAL = 3;

    /**
     * 资金方ID对照资金方
     * @var array
     */
    public static $resourceCompany = [
        RESOURCE_JCFC => RESOURCE_JCFC_COMPANY,
        RESOURCE_FCS => RESOURCE_FCS_COMPANY,
        RESOURCE_FCS_SC => RESOURCE_FCS_SC_COMPANY
    ];

    /**
     * 资金方ID对照资金方简称
     * @var array
     */
    public static $resourceCompanySimple = [
        RESOURCE_JCFC => RESOURCE_JCFC_COMPANY_SIMPLE,
        RESOURCE_FCS => RESOURCE_FCS_COMPANY_SIMPLE,
        RESOURCE_FCS_SC => RESOURCE_FCS_SC_COMPANY_SIMPLE,
    ];

    public static function getLoanProductByProduct($loan_product)
    {
        if (empty($loan_product)) {
            return [];
        }

        return DB::table(self::TABLE_NAME)->select('*')
            ->where('loan_product', $loan_product)
            ->first();
    }

    public static function getLoanProductAll($isMemcache = false, $status)
    {
        if ($isMemcache) {
            $redis = RedisUtil::getInstance();
            $key = "PF_LOAN_TYPE_ALL";
            if ($redis) {
                $data = $redis->get($key);
                if ($data) {
                    return json_decode($data, true);
                }
            }
        }
        $query = DB::table(self::TABLE_NAME)
            ->select(['resource', 'loan_product', 'loan_type', 'loan_channel', 'rate_time_x', 'rate_x', 'rate_time_y', 'rate_y']);
        if ($status == STATUS_SUCCESS) {
            $result = $query->where('status', STATUS_SUCCESS)->get()->toArray();
        } else if ($status == STATUS_FAIL) {
            $result = $query->where('status', STATUS_FAIL)->get()->toArray();
        } else {
            $result = $query->get()->toArray();
        }

        if ($isMemcache) {
            if ($redis) {
                $redis->set($key, json_encode($result), 1800);
            }
        }
        return $result;
    }
}
