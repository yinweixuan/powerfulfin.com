<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/14
 * Time: 11:28 AM
 */

namespace App\Admin\Controllers;


use App\Admin\AdminController;
use App\Components\HttpUtil;
use App\Components\PFException;
use App\Models\ActiveRecord\ARPFLoan;
use App\Models\ActiveRecord\ARPFLoanLog;
use Illuminate\Support\Facades\Input;

class ToolsController extends AdminController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function loanStatus()
    {
        try {
            $lid = Input::get('lid');
            $status = Input::get('status');

            $loan = ARPFLoan::getLoanById($lid);
            if (empty($loan)) {
                throw new PFException(ERR_LOAN_INFO_CONTENT, ERR_LOAN_INFO);
            }

            if ($status == $loan['status']) {
                throw new PFException('目前该订单状态已经为：' . $status . '，请勿重复操作', ERR_SYS_PARAM);
            }

            ARPFLoan::_update($lid, ['status' => $status]);
            ARPFLoanLog::insertLogForAdmin($loan, $status, '状态变更', ['status' => $status]);
            HttpUtil::adminSuccessHtml(ERR_OK_CONTENT, '/admin/loan/tools');
        } catch (PFException $exception) {
            HttpUtil::adminErrHtml($exception->getMessage(), '/admin/loan/tools');
        }
    }
}
