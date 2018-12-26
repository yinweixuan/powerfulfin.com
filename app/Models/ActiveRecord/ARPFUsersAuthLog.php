<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:46 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\PFException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ARPFUsersAuthLog extends Model
{
    protected $table = 'pf_users_auth_log';
    const TABLE_NAME = 'pf_users_auth_log';

    public $timestamps = false;

    /**
     * 认证结果
     */
    const RESULT_AUTH_FALSE = 1;    //认真不通过
    const RESULT_AUTH_TRUE = 2; //认证通过
    /**
     * 性别
     */
    const SEX_MALE = 1;     //男
    const SEX_FEMALE = 2;   //女

    const USER_ID_SUFFIX = 'PF_';
    const REDIS_KEY = 'PF_UDCREDIT_';

    const SAFE_MODE_HIGH = 0;
    const SAFE_MODE_MIDDLE = 1;
    const SAFE_MODE_LOW = 2;

    public static function getUserAuthSuccessLast($uid)
    {
        $info = DB::table(self::TABLE_NAME)->select('*')
            ->where('uid', $uid)
            ->where('result_auth', self::RESULT_AUTH_TRUE)
            ->where('be_idcard', '>=', '0.8')
            ->orderByDesc('id')
            ->first();
        if (empty($info)) {
            return [];
        } else {
            return $info;
        }
    }

    public static function getInfoByOrder($order)
    {
        if (empty($order)) {
            return [];
        }

        $info = DB::table(self::TABLE_NAME)->select('*')
            ->where('order', $order)
            ->first();

        return $info;
    }

    /**
     * 更新数据通过订单号
     * @param array $info
     * @return array|bool
     * @throws PFException
     */
    public static function _updateByOrder($info = array())
    {
        if (!isset($info['order']) || empty($info)) {
            return false;
        }

        $ar = new self();
        $columns = Schema::getColumnListing(self::TABLE_NAME);
        var_dump($columns);
        foreach ($columns as $key) {
            if (array_key_exists($key, $info)) {
                $ar->$key = $info[$key];
                var_dump($ar);
            }
        }
        $ar->save();

//        if (!$ar->save()) {
//
//        }
        return $ar->getAttributes();
    }

}
