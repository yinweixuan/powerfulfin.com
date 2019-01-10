<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/1/10
 * Time: 10:16 AM
 */
?>
<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">搜索</h3>
    </div>
    <form name='form1' action="/admin/astat/loanbill" method="get" role="form">
        <input type="hidden" name="query" value="1"/>
        <input type="hidden" name="excel" value="0"/>
        <!-- /.box-header -->
        <div class="box-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-3 control-label">订单ID</label>
                        <div class="col-sm-9">
                            <input type="number" style="width: 100%" class="form-control" name="lid"
                                   placeholder="分期订单号" value="{{ $lid }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-3 control-label">用户ID</label>
                        <div class="col-sm-9">
                            <input type="number" style="width: 100%" class="form-control" name="uid"
                                   placeholder="用户ID" value="{{ $uid }}">
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="box-footer">
            <button type="submit" class="btn btn-danger">查询</button>
        </div>
    </form>
</div>
<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">还款计划-订单：{{ $lid }}</h3>
    </div>
    <div class="box-body">
        <div class="row col-md-12" style="overflow: auto;">
            <table class="table table-hover table-bordered">
                <thead>
                <tr style="white-space: nowrap;">
                    <td>账期</td>
                    <td>状态</td>
                    <td>应还本金</td>
                    <td>应还利息</td>
                    <td>逾期天数</td>
                    <td>应还手续费</td>
                    <td>应还罚息</td>
                    <td>应还总额</td>
                    <td>应还日期</td>
                    <td>未还本金</td>
                    <td>未还利息</td>
                    <td>未还手续费</td>
                    <td>未还罚息</td>
                    <td>未还总额</td>
                    <td>已还本金</td>
                    <td>已还利息</td>
                    <td>已还手续费</td>
                    <td>已还罚息</td>
                    <td>已还总额</td>
                    <td>实际还款日期</td>
                    <td>划扣状态</td>
                    <td>备注&emsp;&emsp;&emsp;</td>
                </tr>
                </thead>
                <tbody>
                <?php foreach ($info as $item) { ?>
                <tr <?php if (in_array($item['status'], array(1, 3, 4))) {
                    echo 'class="success"';
                } else if ($item['status'] == 2) {
                    echo 'class="danger"';
                } else {
                    echo 'class="info"';
                } ?>>
                    <td>{{ $item['bill_date'] }}<br>{{  $item['installment_plan'] . '/' . $item['installment'] }}</td>
                    <td>{{ \App\Models\ActiveRecord\ARPFLoanBill::$statusDesp[$item['status']] }}</td>
                    <td>￥{{ $item['principal'] }}</td>
                    <td>￥{{ $item['interest'] }}</td>
                    <td>{{ $item['overdue_days'] }}</td>
                    <td>￥{{ $item['overdue_fees'] }}</td>
                    <td>￥{{ $item['overdue_fine_interest'] }}</td>
                    <td>￥{{$item['total']}}</td>
                    <td>{{ $item['should_repay_date'] }}</td>
                    <td>￥{{ $item['miss_principal'] }}</td>
                    <td>￥{{ $item['miss_interest'] }}</td>
                    <td>￥{{ $item['miss_overdue_fees'] }}</td>
                    <td>￥{{ $item['miss_overdue_fine_interest'] }}</td>
                    <td>￥{{ $item['miss_total'] }}</td>
                    <td>￥{{ $item['repay_principal'] }}</td>
                    <td>￥{{ $item['repay_interest'] }}</td>
                    <td>￥{{ $item['repay_overdue_fees'] }}</td>
                    <td>￥{{ $item['repay_overdue_fine_interest'] }}</td>
                    <td>￥{{ $item['repay_total'] }}</td>
                    <td>
                        <span class="label label-info">{{ $item['repay_date'] }}</span>
                    </td>
                    <td>
                    </td>
                    <td><?php if ($item['kz_deduction'] == \App\Models\ActiveRecord\ARPFLoanBill::PF_DEDUCTION_TRUE) {
                            echo "代偿 ";
                            echo $item['remark'] ? '(' . $item['remark'] . ')' : "";
                        } else {
                            echo "非代偿 ";
                            echo $item['remark'] ? '(' . $item['remark'] . ')' : "";
                        }
                        ?>
                    </td>
                </tr>
                <?php } ?>
                </tbody>
                <tfoot>
                <tr>
                    <td>账期</td>
                    <td>状态</td>
                    <td>应还本金</td>
                    <td>应还利息</td>
                    <td>逾期天数</td>
                    <td>应还手续费</td>
                    <td>应还罚息</td>
                    <td>应还总额</td>
                    <td>应还日期</td>
                    <td>未还本金</td>
                    <td>未还利息</td>
                    <td>未还手续费</td>
                    <td>未还罚息</td>
                    <td>未还总额</td>
                    <td>已还本金</td>
                    <td>已还利息</td>
                    <td>已还手续费</td>
                    <td>已还罚息</td>
                    <td>已还总额</td>
                    <td>实际还款日期</td>
                    <td>划扣状态</td>
                    <td>备注</td>
                </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
