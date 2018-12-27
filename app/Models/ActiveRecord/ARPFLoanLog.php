<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/22
 * Time: 2:14 PM
 */

namespace App\Models\ActiveRecord;


use App\Components\OutputUtil;
use App\Models\DataBus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ARPFLoanLog extends Model
{
    protected $table = 'pf_loan_log';

    const TABLE_NAME = 'pf_loan_log';

    public $timestamps = false;

    /**
     * @param $loanInfo
     * @param string $status
     * @param string $remark
     * @param array $change 变更内容，数据格式
     * @return bool|int
     */
    public static function insertLog($loanInfo, $status = '', $remark = '', array $change = [])
    {
        if (empty($loanInfo)) {
            return false;
        }
        $logData = array(
            'lid' => $loanInfo['id'],
            'status_before' => $loanInfo['status'],
            'status_after' => $status,
            'remark' => $remark,
            'uid_op' => DataBus::get('uid'),
            'create_time' => date('Y-m-d H:i:s'),
        );
        $change_info = ['status_before' => $loanInfo['status'], 'status_after' => $status];
        if (!empty($change)) {
            $change_info = array_merge($change_info, $change);
        }
        $logData['change_info'] = OutputUtil::json_encode($change_info);
        return DB::table(self::TABLE_NAME)->insertGetId($logData);
    }

}
