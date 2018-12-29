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
use App\Components\OutputUtil;
use App\Components\PFException;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFUsersReal;
use App\Models\Org\OrgBaseController;
use App\Models\Org\OrgDataBus;
use App\Models\Org\OrgDataHelper;
use App\Models\Server\BU\BULoanApply;
use App\Models\Server\BU\BULoanUpdate;
use Illuminate\Pagination\LengthAwarePaginator;
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
        return $this->list(LOAN_1100_CREATE_ACCOUNT, 'org.order.bookinglist');
    }

    /**
     * 确认上课放款列表
     */
    public function confirmlist()
    {
        return $this->list(LOAN_4000_P2P_CONFIRM, 'org.order.confirmlist');
    }

    /**
     * 整合报名确认和上课放款两个列表,逻辑基本相同.
     * @param $status
     * @param $view
     */
    private function list($status, $view)
    {
        $lid = Input::get('lid');
        $fullName = Input::get('full_name');
        $identityNumber = Input::get('identity_number');
        $p = Input::get('page', 1);
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
                    ->where(ARPFLoan::TABLE_NAME . '.status', '=', $status);
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
                $page = new LengthAwarePaginator($ids, count($ids), $ps);
                $page->setPath("/" . Request::path() . "?query=1&lid={$lid}&full_name={$fullName}&identity_number={$identityNumber}");
                $data['page'] = $page->render();
            }
        } catch (\Exception $e) {
            $data['errmsg'] = $e->getMessage();
        }
        return $this->view($view, $data);
    }

    /**
     * 分期详情
     */
    public function detail()
    {
        $data = [];
        $lid = Input::get('lid');
        $data['lid'] = $lid;
        try {
            $loan = BULoanApply::getDetailById($lid);
            //检查是否属于该机构
            var_dump($loan);
        } catch (\Exception $e) {
            $data['errmsg'] = $e->getMessage();
        }
        return $this->view('org.order.detail', $data);
    }

    /**
     * 对订单进行审核操作
     */
    public function operate()
    {
        $lid = Input::get('lid');
        $period = Input::get('period');
        $op = Input::get('op');
        $remark = Input::get('remark');
        //检查参数
        if (empty($lid) || !is_numeric($lid)) {
            OutputUtil::err("订单号格式错误", ERR_SYS_PARAM);
        }
        if (!in_array($period, ['booking', 'confirm',])) {
            OutputUtil::err("阶段错误", ERR_SYS_PARAM);
        }
        if (!in_array($op, ['pass', 'refuse',])) {
            OutputUtil::err("操作类型错误", ERR_SYS_PARAM);
        }
        $auditMap = [
            'booking' => [LOAN_1100_CREATE_ACCOUNT =>['pass' => LOAN_2000_SCHOOL_CONFIRM, 'refuse' => LOAN_2100_SCHOOL_REFUSE,]],
            'confirm' => [LOAN_4000_P2P_CONFIRM => ['pass' => LOAN_5000_SCHOOL_BEGIN, 'refuse' => LOAN_5100_SCHOOL_REFUSE,]]
            ];
        //获取订单详情
        try {
            $loan = ARPFLoan::getLoanById($lid);
            if (empty($loan)) {
                throw new PFException("订单不存在", ERR_SYS_PARAM);
            }
            if (!array_key_exists('oid', $loan) || $loan['oid'] != OrgDataBus::get('org_id')) {
                throw new PFException("订单非本机构订单,不能进行操作", ERR_SYS_PARAM);
            }
            if (!array_key_exists('status', $loan) || !array_key_exists($loan['status'], $auditMap[$period])) {
                throw new PFException("订单{$lid}在当前阶段拒绝该操作", ERR_SYS_UNKNOWN);
            }
            $newStatus = BULoanUpdate::changeStatus($lid, ['status' => $auditMap[$period][$loan['status']][$op], 'audit_opinion' => $remark,]);
            OutputUtil::info();
        } catch (\Exception $e) {
            OutputUtil::err($e->getMessage(), ERR_SYS_UNKNOWN);
        }
    }
}