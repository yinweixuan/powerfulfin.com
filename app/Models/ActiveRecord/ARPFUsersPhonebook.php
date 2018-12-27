<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:46 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\ArrayUtil;
use App\Components\PFException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ARPFUsersPhonebook extends Model
{
    protected $table = 'pf_users_phonebook';
    const TABLE_NAME = 'pf_users_phonebook';

    public $timestamps = false;

    public static function addUserPhoneBook($info = array())
    {
        $info = ArrayUtil::trimArray($info);

        if (empty($info)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        if (empty($info['uid'])) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        $ar = new self();
        $columns = Schema::getColumnListing(self::TABLE_NAME);
        foreach ($columns as $key) {
            if (array_key_exists($key, $info)) {
                $ar->$key = $info[$key];
            }
        }
        $ar->create_time = date('Y-m-d H:i:s');
        $ar->save();
        return $ar->getAttributes();
    }

    public static function getPhoneBookLastOneByUid($uid)
    {
        return DB::table(self::TABLE_NAME)->select('*')
            ->where('uid', $uid)
            ->orderByDesc('id')
            ->first();
    }
}
