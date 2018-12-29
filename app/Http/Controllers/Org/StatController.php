<?php
/**
 * 统计相关操作
 * User: haoxiang
 * Date: 2018/12/24
 * Time: 7:21 PM
 */
namespace App\Http\Controllers\Org;

use App\Components\ArrayUtil;
use App\Components\CookieUtil;
use App\Components\PFException;
use App\Models\ActiveRecord\ARPFLoanProduct;
use App\Models\Org\OrgBaseController;
use App\Models\Org\OrgDataBus;
use App\Models\Org\OrgDataHelper;
use App\Models\ActiveRecord\ARPFUsers;
use App\Models\ActiveRecord\ARPFUsersReal;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\Server\BU\BULoanStatus;
use App\Models\Server\BU\BUOrgStat;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;




class StatController extends OrgBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 订单查询
     * 根据订单号,姓名,身份证号,手机号,分校,状态,申请时间,资金方,
     */
    public function list()
    {
        $lid = Input::get('lid');
        $fullName = Input::get('full_name');
        $identityNumber = Input::get('identity_number');
        $phone = Input::get('phone');
        $oid = Input::get('oid');
        $status = Input::get('status');
        $beginTime = Input::get('begin_time');
        $endTime = Input::get('end_time');
        $resource = Input::get('resource');
        $p = Input::get('page', 1);
        $ps = 15;
        $data = ['page' => ''];
        $data['form'] = ['lid' => $lid, 'full_name' => $fullName, 'identity_number' => $identityNumber, 'phone' => $phone,
            'oid' => $oid, 'status' => $status, 'begin_time' => $beginTime, 'end_time' => $endTime, 'resource' => $resource,
            'p' => $p, 'ps' => $ps,];
        $data['resourceConfig'] = ARPFLoanProduct::$resourceCompanySimple;
        $data['statusConfig'] = BULoanStatus::getStatusDescriptionForB();
        try {
            $data['lists'] = [];
            //查询结果集,先查到id,然后做完分页,再拼装展示的数据
            if (Input::get('query') == 1) {
                $query = DB::table(ARPFLoan::TABLE_NAME)
                    ->join(ARPFUsersReal::TABLE_NAME, ARPFUsersReal::TABLE_NAME .'.uid', '=', ARPFLoan::TABLE_NAME . '.uid')
                    ->where(ARPFLoan::TABLE_NAME . '.oid', '=', OrgDataBus::get('org_id'));
                if ($lid) {
                    $query->where(ARPFLoan::TABLE_NAME . '.id', '=', $lid);
                }
                if ($fullName) {
                    $query->where(ARPFUsersReal::TABLE_NAME . '.full_name', '=', $fullName);
                }
                if ($identityNumber) {
                    $query->where(ARPFUsersReal::TABLE_NAME . '.identity_number', '=', $identityNumber);
                }
                if ($phone) {
                    //从用户信息关联手机号,如果手机号不存在,强制指定uid=0作为查询条件,使结果集为空
                    $user = ARPFUsers::getUserInfoByPhone($phone);
                    if (!empty($user)) {
                        $query->where(ARPFUsersReal::TABLE_NAME . '.uid', '=', $user->id);
                    } else {
                        $query->where(ARPFUsersReal::TABLE_NAME . '.uid', '=', 0);
                    }
                }
                /*
                if ($oid) {
                    $query->where(ARPFLoan::TABLE_NAME . '.oid', '=', $oid);
                }
                */
                if ($status) {
                    $query->whereIn(ARPFLoan::TABLE_NAME . '.status', [$status]);
                }
                if ($resource) {
                    $query->where(ARPFLoan::TABLE_NAME . '.resource', '=', $resource);
                }
                if ($beginTime) {
                    $query->where(ARPFLoan::TABLE_NAME . '.create_time', '>=', $beginTime);
                }
                if ($endTime) {
                    $query->where(ARPFLoan::TABLE_NAME . '.create_time', '<=', $endTime);
                }
                $query->select(ARPFLoan::TABLE_NAME . '.id')->orderBy(ARPFLoan::TABLE_NAME . '.id', 'desc');
                $ids = $query->get()->all();
                $ids = ArrayUtil::getSomeKey($ids, 'id');
                //处理分页
                $resultIds = ArrayUtil::pageWithKey($ids, $p, $ps);
                //获取分期列表
                $result = OrgDataHelper::getLoanByIds($resultIds);
                $data['lists'] = $result;
                $page = new LengthAwarePaginator($ids, count($ids), $ps);
                $page->setPath("/" . Request::path() . "?query=1&lid={$lid}&full_name={$fullName}&identity_number={$identityNumber}&phone={$phone}&oid={$oid}&status={$status}&resource={$resource}&begin_time={$beginTime}&end_time={$endTime}");
                $data['page'] = $page->render();
            }
        } catch (\Exception $e) {
            $data['errmsg'] = $e->getMessage();
        }
        return $this->view('org.stat.list', $data);
    }

    /**
     * 校区统计
     */
    public function sumup()
    {
        $data = [];
        try {
            $data['stat'] = BUOrgStat::orgGeneral([OrgDataBus::get('org_id')]);
            $org = OrgDataBus::get('org');
            //先不打开总校统计开关
            //$data['headStat'] = BUOrgStat::headGeneral([$org['hid']]);
        } catch (\Exception $e) {
            $data['errmsg'] = $e->getMessage();
        }
        return $this->view('org.stat.sumup', $data);
    }
}