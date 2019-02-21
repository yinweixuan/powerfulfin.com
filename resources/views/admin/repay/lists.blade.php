<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/21
 * Time: 10:34 AM
 */
?>
<link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/select2/dist/css/select2.min.css") }}">
<div class="row">
    <div class="col-md-12">
        <div class="box box-default">
            <div class="box-header with-border">
                <h3 class="box-title">搜索</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                    </button>
                </div>
            </div>
            <form name='form1' action="" method="get">
                <input type="hidden" name="page" value="{{ $page }}"/>
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>账期：</label>
                                <select class="form-control select2 select2-hidden-accessible" style="width: 100%;"
                                        tabindex="-1" aria-hidden="true" name="bill_date">
                                    <?php
                                    $beginRepay = '201901';
                                    $beginTime = strtotime($beginRepay . '01');
                                    for ($i = 0; $i < 60; $i++) {
                                        $curRepay = date('Ym', strtotime("+{$i} month", $beginTime));
                                        echo "<option value='{$curRepay}' " . ($curRepay == $bill_date ? 'selected' : '') . ">{$curRepay}</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>相关商务：</label>
                                <select class="form-control select2 select2-hidden-accessible" style="width: 100%;"
                                        tabindex="-1" aria-hidden="true" id="docking_business" name="docking_business">
                                    <option value="">请选择相关商务</option>
                                    @foreach(\App\Admin\Models\AdminUsersModel::getBusinessUsers() as $key=>$val)
                                        <option value="{{ $val['id'] }}"
                                                @if($busi_id == $val['id']) selected @endif>{{ $val['name'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>资金方:</label>
                                <select class="form-control select2 select2-hidden-accessible" style="width: 100%;"
                                        tabindex="-1" aria-hidden="true" name="resource">
                                    <option value="">请选择资金方</option>
                                    @foreach(\App\Models\ActiveRecord\ARPFLoanProduct::$resourceCompany as $key=>$value)
                                        <option value="{{ $key }}"
                                                @if($resource == $key) selected @endif>{{ $value }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>是否还款：</label>
                                <select class="form-control select2 select2-hidden-accessible" style="width: 100%;"
                                        tabindex="-1" aria-hidden="true" name="hasPayType">
                                    <option value="">请选择是否还款</option>
                                    <option value="1" <?php if ($hasPayType == 1) echo ' selected ' ?>>已还款</option>
                                    <option value="2" <?php if ($hasPayType == 2) echo ' selected ' ?>>未还款</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>还款方式:</label>
                                <select class="form-control select2 select2-hidden-accessible" style="width: 100%;"
                                        tabindex="-1" aria-hidden="true" name="loan_product">
                                    <option value="">请选择还款方式</option>
                                    @foreach(\App\Models\Server\BU\BULoanProduct::getAllLoanType() as $key=>$val)
                                        <option
                                            value="{{ $val['loan_product'] }}"
                                            @if($loan_product == $val['loan_product']) selected @endif>{{ $val['name'].'--'.$val['loan_product'] }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group">
                                <label>放款时间:</label>
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="date" class="form-control pull-right" placeholder="开始时间"
                                           name="beginDate" value="">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label>金融总校:</label>
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-child"></i>
                                    </div>
                                    <input type="number" name="hid" class="input-sm form-control " placeholder="金融总校"
                                           value="{{ $hid }}">
                                </div>
                            </div>
                            <div class="form-group">
                                <label>放款时间:</label>
                                <div class="input-group date">
                                    <div class="input-group-addon">
                                        <i class="fa fa-calendar"></i>
                                    </div>
                                    <input type="date" class="form-control pull-right" placeholder="结束时间"
                                           name="endDate" value="">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-sm btn-danger">查询</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-xs-12">
        <div class="box">
            <div class="box-header"></div>
            <div class="box-body">
                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="row">
                        <div class="col-sm-6"></div>
                        <div class="col-sm-6"></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12" style="overflow-x: auto;">
                            <table id="example2" class="table table-bordered table-hover dataTable" role="grid"
                                   aria-describedby="example2_info">
                                <thead>
                                <tr>
                                    <th rowspan="1" colspan="1">订单</th>
                                    <th rowspan="1" colspan="1">学校</th>
                                    <th rowspan="1" colspan="1">姓名</th>
                                    <th rowspan="1" colspan="1">资金方</th>
                                    <th rowspan="1" colspan="1">放款时间</th>
                                    <th rowspan="1" colspan="1">分期金额</th>
                                    <th rowspan="1" colspan="1">本期总额</th>
                                    <th rowspan="1" colspan="1">本期本金</th>
                                    <th rowspan="1" colspan="1">本期服务费</th>
                                    <th rowspan="1" colspan="1">本期逾期天数</th>
                                    <th rowspan="1" colspan="1">本期逾期金额</th>
                                    <th rowspan="1" colspan="1">分期方式</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($info as $key=>$value)
                                    <tr>
                                        <td>{{ $value['lid'] }}</td>
                                        <td>{{ $value['full_name'] }}</td>
                                        <td>{{ $value['ur_full_name'] }}</td>
                                        <td>{{ \App\Models\ActiveRecord\ARPFLoanProduct::$resourceCompany[$value['resource']] }}</td>
                                        <td>{{ $value['loan_time'] }}</td>
                                        <td>{{ \App\Models\Calc\CalcMoney::calcMoney($value['borrow_money']) }}</td>
                                        <td>{{ \App\Models\Calc\CalcMoney::calcMoney($value['total']) }}</td>
                                        <td>{{ \App\Models\Calc\CalcMoney::calcMoney($value['principal']) }}</td>
                                        <td>{{ \App\Models\Calc\CalcMoney::calcMoney($value['interest']) }}</td>
                                        <td>{{ $value['overdue_days'] }}</td>
                                        <td>{{ \App\Models\Calc\CalcMoney::sum($value['overdue_fees'],$value['overdue_fine_interest'],2) }}</td>
                                        <td>{{ $value['loan_product'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th rowspan="1" colspan="1">订单</th>
                                    <th rowspan="1" colspan="1">学校</th>
                                    <th rowspan="1" colspan="1">姓名</th>
                                    <th rowspan="1" colspan="1">资金方</th>
                                    <th rowspan="1" colspan="1">放款时间</th>
                                    <th rowspan="1" colspan="1">分期金额</th>
                                    <th rowspan="1" colspan="1">本期总额</th>
                                    <th rowspan="1" colspan="1">本期本金</th>
                                    <th rowspan="1" colspan="1">本期服务费</th>
                                    <th rowspan="1" colspan="1">本期逾期天数</th>
                                    <th rowspan="1" colspan="1">本期逾期金额</th>
                                    <th rowspan="1" colspan="1">分期方式</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                        {{ $info->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{ \Encore\Admin\Admin::js('/vendor/laravel-admin/select2/dist/js/select2.full.min.js') }}
<script type="text/javascript">
    jQuery(document).ready(function () {
        $('.select2').select2();
    });
</script>
