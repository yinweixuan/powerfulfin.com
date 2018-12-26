<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/26
 * Time: 10:31 AM
 */

namespace App\Models\ActiveRecord;


use App\Components\ArrayUtil;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFSimg extends Model
{
    protected $table = 'pf_simg';

    const TABLE_NAME = 'pf_simg';

    const TYPE_IDCARD_INFORMATION_PIC = 'idcard_information_pic';
    const TYPE_IDCARD_NATIONAL_PIC = 'idcard_national_pic';
    const TYPE_FACE_LIVING_PIC = 'face_living_pic';
    const TYPE_FACE_IDCARD_PORTRAIT_PIC = 'face_idcard_portrait_pic';
    const TYPE_EDU_PIC = 'edu_pic';
    const TYPE_SCENE_PIC = 'scene_pic';
    const TYPE_PERSON_PIC= 'person_pic';
    const TYPE_TRAIN_CONTRACT_PIC='train_contract_pic';
    const TYPE_AUDIT_RECORDING='audit_recording';
    const TYPE_TRAIN_STATEMENT_PIC='train_statement_pic';

    public static function addSimg(array $info)
    {
        if (empty($info)) {
            return [];
        }
        $info = ArrayUtil::trimArray($info);
        $info['create_time'] = date('Y-m-d H:i:s');
        return DB::table(self::TABLE_NAME)->insertGetId($info);
    }

}
