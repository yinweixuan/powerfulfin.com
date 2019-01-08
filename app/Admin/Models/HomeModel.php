<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/1/8
 * Time: 4:06 PM
 */

namespace App\Admin\Models;


use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPfUsers;
use Illuminate\Support\Facades\DB;

class HomeModel
{
    public static function getHomeData()
    {
        $users = DB::table(ARPfUsers::TABLE_NAME)
            ->where('created_at', '>=', date('Y-m-d 00:00:00'))
            ->count('id');

        $loans = DB::table(ARPFLoan::TABLE_NAME)
            ->where('create_time', '>=', date('Y-m-d 00:00:00'))
            ->count('id');

        $loanTimes = DB::table(ARPFLoan::TABLE_NAME)
            ->where('loan_time', '>=', date('Y-m-d 00:00:00'))
            ->count('id');

        $borrowMoney = DB::table(ARPFLoan::TABLE_NAME)
            ->where('loan_time', '>=', date('Y-m-d 00:00:00'))
            ->sum('borrow_money');

        $info = [
            'users' => $users,
            'loans' => $loans,
            'loanTimes' => $loanTimes,
            'borrowMoney' => $borrowMoney,
        ];
        return $info;
    }
}
