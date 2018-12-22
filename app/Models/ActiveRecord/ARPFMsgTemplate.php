<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/19
 * Time: 3:31 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\RedisUtil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFMsgTemplate extends Model
{
    protected $table = 'pf_msg_template';
    const TABLE_NAME = 'pf_msg_template';

    const SCENES_SMS = 1;
    const SCENES_JPUSH = 2;
    const SCENES_EMAIL = 3;
    const SCENES_LETTER = 4;


    public static function getMsgTemplateByScenesAndKey($scenes = null, $key = null)
    {
        if (empty($key) || empty($scenes)) {
            return false;
        }

        $redis = RedisUtil::getInstance();
        $redisKey = "PF-MSG-TEMPLATE-" . $scenes . '-' . $key;
        $data = $redis->get($redisKey);
        if (!empty($data)) {
            return json_decode($data, true);
        }

        $result = DB::table(self::TABLE_NAME)->select('*')->where(['scenes' => $scenes], ['key' => $key])->get()->toArray();
        if (empty($result)) {
            return array();
        } else {
            $result = array_shift($result);
            $redis->set($redisKey, json_encode($result), 86400);
            return $result;
        }
    }
}
