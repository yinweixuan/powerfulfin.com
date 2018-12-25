<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/25
 * Time: 10:19 AM
 */

namespace App\Models\Server\BU;


class BULoanStatus
{
    public static function getStatusDescriptionForC($status)
    {
        return '待确认';
    }

    public static function getStatusDescriptionForB($status)
    {

    }

    public static function getStatusDescriptionForAdmin($status)
    {

    }
}
