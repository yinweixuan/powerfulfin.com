<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/20
 * Time: 4:39 PM
 */
?>
<div class="row">
    <div class="col-md-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">数据汇总</h3>
            </div>
            <div class="box-body">
                <table class="table table-hover table-bordered table-striped ">
                    <tr>
                        <td colspan="4" align="center"><b>总览</b></td>
                    </tr>
                    <tr>
                        <td>学校总数</td>
                        <td><?php echo $school_count; ?></td>
                        <td>可分期总数</td>
                        <td><?php echo $school_count_can_loan; ?></td>
                    </tr>
                    <tr>
                        <td>课程总数</td>
                        <td><?php echo $course_count; ?></td>
                        <td>用户总数</td>
                        <td><?php echo $user_count; ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" align="center"><b>分期</b></td>
                    </tr>
                    <tr>
                        <td>单数</td>
                        <td><?php echo $loan_count; ?></td>
                        <td>学员申请</td>
                        <td>￥<?php echo $loan_sum_apply; ?></td>
                    </tr>
                    <tr>
                        <td>机构应收</td>
                        <td>￥<?php echo $loan_sum_school; ?></td>
                        <td>已放款</td>
                        <td>￥<?php echo $loan_sum_get_money; ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" align="center"><b>拒绝</b></td>
                    </tr>
                    <tr>
                        <td>拒绝单数</td>
                        <td><?php echo $reject_loan_count; ?></td>
                        <td>学员申请</td>
                        <td>￥<?php echo $reject_loan_sum_apply; ?></td>
                    </tr>
                    <tr>
                        <td>机构应收</td>
                        <td>￥<?php echo $reject_loan_sum_school; ?></td>
                        <td></td>
                        <td></td>
                    </tr>
                </table>
                <table class="table table-hover table-bordered table-striped ">
                    <thead>
                    <td><b>状态</b></td>
                    <td><b>单数</b></td>
                    <td><b>学员申请</b></td>
                    <td><b>机构应收</b></td>
                    <td><b>已放款</b></td>
                    </thead>
                    <tbody>
                    <?php
                    //把拒绝的审批状态放到后面来处理
                    $rejectLoans = array();
                    foreach ($loan as $item) {
                    if (in_array($item['status'], array(111, 130, 360, 331,))) {
                        continue;
                    }
                    if (in_array($item['status'], array(21, 31, 41,))) {
                        $rejectLoans[] = $item;
                        continue;
                    }
                    ?>
                    <tr>
                        <td><?php echo $item['status_desp']; ?></td>
                        <td><?php echo $item['count']; ?></td>
                        <td>￥<?php echo $item['sum_apply_desp']; ?></td>
                        <td>￥<?php echo $item['sum_school_desp']; ?></td>
                        <td>￥<?php echo $item['sum_get_money_desp']; ?></td>
                    </tr>
                    <?php }
                    foreach ($rejectLoans as $item) {
                    ?>
                    <tr>
                        <td><?php echo $item['status_desp']; ?></td>
                        <td><?php echo $item['count']; ?></td>
                        <td>￥<?php echo $item['sum_apply_desp']; ?></td>
                        <td>￥<?php echo $item['sum_school_desp']; ?></td>
                        <td>￥<?php echo $item['sum_get_money_desp']; ?></td>
                    </tr>
                    <?php } ?>

                    </tbody>
                    <tfoot>
                    <td><b>状态</b></td>
                    <td><b>单数</b></td>
                    <td><b>学员申请</b></td>
                    <td><b>机构应收</b></td>
                    <td><b>已放款</b></td>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>
</div>
