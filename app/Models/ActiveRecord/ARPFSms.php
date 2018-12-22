<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/19
 * Time: 3:16 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\ArrayUtil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFSms extends Model
{
    protected $table = 'pf_sms';
    const TABLE_NAME = 'pf_sms';

    /**
     * 新增短信记录
     * @param $uid
     * @param $phone
     * @param $msg
     * @param int $plat
     * @return int
     */
    public static function createNewSMS($uid, $phone, $msg, $plat = 1)
    {
        $info = [
            'uid' => $uid,
            'phone' => $phone,
            'msg' => $msg,
            'plat' => $plat,
            'create_time' => date('Y-m-d H:i:s'),
        ];
        return DB::table(self::TABLE_NAME)->insertGetId($info);
    }

    /**
     * 更新
     * @param $id
     * @param array $info
     * @return int
     */
    public static function _update($id, $info = [])
    {
        $info = ArrayUtil::trimArray($info);
        return DB::table(self::TABLE_NAME)->where('id', $id)->update($info);
    }
}
