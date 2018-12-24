<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/6
 * Time: 4:10 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\ArrayUtil;
use App\Components\CheckUtil;
use App\Components\PFException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPfUsers extends Model
{
    /**
     * 数据表
     */
    const  TABLE_NAME = 'pf_users';

    /**
     * 根据用户ID获取用户信息
     * @param $id
     * @return array
     * @throws PFException
     */
    public static function getUserInfoByID($id)
    {
        if (is_null($id) || !is_numeric($id) || $id < 0) {
            throw new PFException(ERR_SYS_PARAM_CONTENT . "id:" . $id, ERR_SYS_PARAM);
        }

        return DB::table(self::TABLE_NAME)->where('id', $id)->first();
    }

    /**
     * 根据手机号获取用户信息
     * @param $phone
     * @return array
     * @throws PFException
     */
    public static function getUserInfoByPhone($phone)
    {
        if (!CheckUtil::checkPhone($phone)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT . "phone:" . $phone, ERR_SYS_PARAM);
        }
        return DB::table(self::TABLE_NAME)->where('phone', $phone)->first();
    }

    /**
     * 新增用户
     * @param array $userInfo
     * @return bool
     */
    public static function addUserInfo(array $userInfo)
    {
        $userInfo = ArrayUtil::trimArray($userInfo);
        $userInfo['created_at'] = date('Y-m-d H:i:s');
        $userInfo['updated_at'] = date('Y-m-d H:i:s');
        return DB::table(self::TABLE_NAME)->insert($userInfo);
    }

    /**
     * 根据用户UID更新用户信息
     * @param $id
     * @param $update
     * @return int
     */
    public static function updateUserInfo($id, $update)
    {
        $update = ArrayUtil::trimArray($update);
        $userInfo['updated_at'] = date('Y-m-d H:i:s');
        return DB::table(self::TABLE_NAME)->where('id', $id)->update($update);
    }


    public static function getUserAllInfo($uid)
    {
        if (is_null($uid) || !is_numeric($uid) || $uid < 0) {
            return [];
        }
        $data = DB::table(self::TABLE_NAME . ' as u')
            ->leftJoin(ARPFUsersReal::TABLE_NAME . ' as ur', 'u.id', '=', 'ur.uid')
            ->leftJoin(ARPFUsersWork::TABLE_NAME . ' as uw', 'u.id', '=', 'uw.uid')
            ->leftJoin(ARPFUsersContact::TABLE_NAME . ' as uc', 'u.id', '=', 'uc.uid')
            ->select('*')
            ->where('u.id', $uid)
            ->first();
        return $data;
    }
}
