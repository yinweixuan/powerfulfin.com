<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/22
 * Time: 2:13 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\PFException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFLoan extends Model
{
    protected $table = 'pf_loan';

    const TABLE_NAME = 'pf_loan';

    public $timestamps = false;

    public static function getLoanById($id)
    {
        if (is_null($id) || !is_numeric($id)) {
            return [];
        }

        return DB::table(self::TABLE_NAME)->select('*')
            ->where('id', $id)
            ->first();
    }

    public static function getLoanByUid($uid)
    {
        if (is_null($uid) || !is_numeric($uid)) {
            return [];
        }

        return DB::table(self::TABLE_NAME)->select('*')
            ->where('uid', $uid)
            ->get()->toArray();
    }

    public static function addLoan($info)
    {
        if (empty($info)) {
            throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
        }

        $data = [
            'uid' => $info['uid'],
            'oid' => $info['oid'],
            'hid' => $info['hid'],
            'cid' => $info['cid'],
            'status' => $info['status'],
            'borrow_money' => $info['borrow_money'],
            'loan_product' => $info['loan_product'],
            'resource' => $info['resource'],
            'class_start_date' => $info['class_start_date'],
            'supply_info' => $info['supply_info'],
            'phone_type' => $info['phone_type'],
            'create_time' => date('Y-m-d H:i:s'),
            'update_time' => date('Y-m-d H:i:s'),
        ];

        foreach ($data as $datum) {
            if (empty($datum)) {
                throw new PFException(ERR_SYS_PARAM_CONTENT, ERR_SYS_PARAM);
            }
        }

        $data['scene_pic'] = $info['scene_pic'];
        $data['person_pic'] = $info['person_pic'];
        $data['train_contract_pic'] = $info['train_contract_pic'];
        $data['train_statement_pic'] = $info['train_statement_pic'];
        $data['phone_id'] = $info['phone_id'];
        $data['version'] = $info['version'];

        return DB::table(self::TABLE_NAME)->insertGetId($data);
    }

    public static function _update($id, $info)
    {
        return DB::table(self::TABLE_NAME)->where('id', $id)
            ->update($info);
    }

}
