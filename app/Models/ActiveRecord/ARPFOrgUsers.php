<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2018/12/17
 * Time: 4:47 PM
 */

namespace App\Models\ActiveRecord;


use Illuminate\Database\Eloquent\Model;

class ARPFOrgUsers extends Model
{
    protected $table = 'pf_org_users';
    const TABLE_NAME = 'pf_org_users';

    public $timestamps = false;
}
