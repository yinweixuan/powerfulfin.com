<?php
/**
 * Created by PhpStorm.
 * User: haoxiang
 * Date: 2018/12/27
 * Time: 4:32 PM
 */

namespace App\Models\Server\BU;

use App\Components\RedisUtil;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFOrg;
use Illuminate\Support\Facades\DB;

/**
 * 处理机构相关的统计信息,用户机构后台和管理后台的展示
 * Class BUBanks
 * @package App\Models\Server
 */
class BUOrgStat
{
    /**
     * 统计校区大体情况,各状态的情况,放款,逾期等
     * @param $oids
     */
    public static function orgGeneral($oids)
    {
        $ret = [];
        if (empty($oids)) {
            return $ret;
        }
        $oidStr = implode(',', $oids);
        //统计放款各状态
        $sql = "select oid,status,count(id) c,sum(borrow_money) s from pf_loan where oid in ({$oidStr}) group by oid,status";
        $statusInfo = DB::select($sql);
        foreach ($statusInfo as $s) {
            if (!array_key_exists($s['oid'], $ret)) {
                $ret[$s['oid']] = ['total' => 0, 'total_money' => 0,
                    'audit' => 0, 'audit_money' => 0,        //审核中
                    'repayed' => 0, 'repayed_money' => 0,                   //已放款
                    'repaying' => 0,'repaying_money' => 0,                  //等待放款
                    'delay' => 0, 'delay_money' => 0,                       //逾期
                    'total' => 0, 'total_money' => 0,                       //总计
                    'done_principal' => 0, 'overdue_principal' => 0, 'doing_principal' => 0,        //剩余本金
                    'statusInfo' => [],     //状态统计
                    'billInfo' => [],       //待还本金
                    'delayInfo' => [],      //逾期
                    'mInfo' => [],          //逾期M
                    'baseInfo' => [],       //基本信息
                    ];
            }
            $ret[$s['oid']]['baseInfo'] = ARPFOrg::getOrgById($s['oid']);
            $ret[$s['oid']]['statusInfo'][$s['status']] = $s;
            $ret[$s['oid']]['total'] += $s['c'];
            $ret[$s['oid']]['total_money'] += $s['s'];
            if (in_array($s['status'], [LOAN_2000_SCHOOL_CONFIRM, LOAN_3000_KZ_CONFIRM, LOAN_4200_DATA_P2P_SEND,])) {
                $ret[$s['oid']]['audit'] += $s['c'];
                $ret[$s['oid']]['audit_money'] += $s['s'];
            } else if (in_array($s['status'], [LOAN_5000_SCHOOL_BEGIN, LOAN_6000_NOTICE_MONEY,])) {
                $ret[$s['oid']]['repaying'] += $s['c'];
                $ret[$s['oid']]['repaying_money'] += $s['s'];
            } else if (in_array($s['status'], [LOAN_10000_REPAY, LOAN_11100_OVERDUE_KZ, LOAN_11000_FINISH, LOAN_12000_DROP, LOAN_13000_EARLY_FINISH,])) {
                $ret[$s['oid']]['repayed'] += $s['c'];
                $ret[$s['oid']]['repayed_money'] += $s['s'];
                if ($s['status'] == LOAN_11100_OVERDUE_KZ) {
                    $ret[$s['oid']]['delay'] += $s['c'];
                    $ret[$s['oid']]['delay_money'] += $s['s'];
                }
            }
        }
        //统计还款信息
        $statusStr = implode(',', [LOAN_10000_REPAY, LOAN_11000_FINISH, LOAN_11100_OVERDUE_KZ, LOAN_12000_DROP, LOAN_13000_EARLY_FINISH,]);
        $sql = "select l.oid oid,b.status status, sum(b.principal) s from pf_loan l, pf_loan_bill b where l.oid in ($oidStr) and l.status in ({$statusStr}) and l.id = b.lid group by l.oid,b.status";
        $billInfo = DB::select($sql);
        foreach ($billInfo as $b) {
            if (!array_key_exists($b['oid'], $ret)) {
                continue;
            }
            $ret[$b['oid']]['billInfo'][$b['status']] = $b;
            if ($b['status'] == BILL_STATUS_OVERDUE) {
                $ret[$b['oid']]['overdue_principal'] += $b['s'];
            } else if ($b['status'] == BILL_STATUS_DOING) {
                $ret[$b['oid']]['doing_principal'] += $b['s'];
            } else if (in_array($b['status'], [BILL_STATUS_DONE, BILL_STATUS_EARLY, BILL_STATUS_DROP,])) {
                $ret[$b['oid']]['done_principal'] += $b['s'];
            }
        }
        //统计逾期中M的数据
        $sql = "select oid oid, m, count(r.lid) c from (select l.oid oid,b.lid lid, count(b.id) m from pf_loan l,pf_loan_bill b where l.oid in ({$oidStr}) and b.status = 2 and b.lid = l.id group by l.oid,b.lid) r group by oid,m";
        $delayInfo = DB::select($sql);
        foreach ($delayInfo as $d) {
            $ret[$d['oid']]['delayInfo'][$d['m']] = $d;
            if ($d['m'] >= 6) {
                $key = 6;
            } else {
                $key = $d['m'];
            }
            if (!array_key_exists($key, $ret[$d['oid']['mInfo']])) {
                $ret[$d['oid']]['mInfo'][$key] = 0;
            }
            $ret[$d['oid']]['mInfo'][$key] += $d['c'];
        }

        return $ret;
    }

    /**
     * 统计总校大体情况,各状态的情况,放款,逾期等
     * @param array $hid
     */
    public static function headGeneral($ids, $withChild = false)
    {
        return self::orgGeneral($ids);
    }




}
