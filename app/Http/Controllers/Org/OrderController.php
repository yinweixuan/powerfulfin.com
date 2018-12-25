<?php
/**
 * 订单相关操作
 * User: haoxiang
 * Date: 2018/12/24
 * Time: 7:21 PM
 */
namespace App\Http\Controllers\Org;

use App\Components\ArrayUtil;
use App\Components\CookieUtil;
use App\Components\PFException;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFUsersReal;
use App\Models\Org\OrgBaseController;
use App\Models\Org\OrgDataBus;
use App\Models\Org\OrgDataHelper;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;


class OrderController extends OrgBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * 报名确认列表,支持翻页,根据订单号,姓名,身份证号检索
     */
    public function bookinglist()
    {
        //获取待报名的列表
        $lid = Input::get('lid');
        $fullName = Input::get('full_name');
        $identityNumber = Input::get('identity_number');
        $p = Input::get('p', 1);
        $ps = 15;
        $data = ['page' => ''];
        $data['form'] = ['lid' => $lid, 'full_name' => $fullName, 'identity_number' => $identityNumber, 'p' => $p, 'ps' => $ps,];
        try {
            $data['lists'] = [];
            //查询结果集,先查到id,然后做完分页,再拼装展示的数据
            if (Input::get('query') == 1) {
                $query = DB::table(ARPFLoan::TABLE_NAME)
                    ->join(ARPFUsersReal::TABLE_NAME, ARPFUsersReal::TABLE_NAME .'.uid', '=', ARPFLoan::TABLE_NAME . '.uid')
                    ->where(ARPFLoan::TABLE_NAME . '.oid', '=', OrgDataBus::get('org_id'))
                    ->where(ARPFLoan::TABLE_NAME . '.status', '=', LOAN_1100_CREATE_ACCOUNT);
                if ($lid) {
                    $query->where(ARPFLoan::TABLE_NAME . '.id', '=', $lid);
                }
                if ($fullName) {
                    $query->where(ARPFUsersReal::TABLE_NAME . '.full_name', '=', $fullName);
                }
                if ($identityNumber) {
                    $query->where(ARPFUsersReal::TABLE_NAME . '.identity_number', '=', $identityNumber);
                }
                $query->select(ARPFLoan::TABLE_NAME . '.id')->orderBy(ARPFLoan::TABLE_NAME . '.id', 'desc');
                $ids = $query->get()->all();
                $ids = ArrayUtil::getSomeKey($ids, 'id');
                //处理分页
                $resultIds = ArrayUtil::pageWithKey($ids, $p, $ps);
                //获取分期列表
                $result = OrgDataHelper::getLoanByIds($resultIds);
                $data['lists'] = $result;
            }
            $data['page'] = '';
        } catch (\Exception $e) {
            $data['errmsg'] = $e->getMessage();
        }
        return $this->view('org.order.bookinglist', $data);
    }

    /**
     * 确认上课放款列表
     */
    public function confirmlist()
    {
        $data = [];
        return $this->view('org.order.confirmlist', $data);
    }

    /**
     * 分期详情
     */
    public function detail()
    {
        $data = [];
        return $this->view('org.order.detail', $data);
    }
}