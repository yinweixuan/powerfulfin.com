@extends('org.common.base')
@section('title',  '订单查询')
@section('content')
<?php
/**
 * 机构分期订单查询
 * User: haoxiang
 * Date: 2018/12/25
 * Time: 10:33 AM
 */
?>
<div id="pjax-content">
    <div class="panel panel-default">
        <div class="panel-heading">搜索</div>
        <form class="form_inline" name="form1" id="loginform" action="/stat/list" method="get">
            <input type="hidden" name="query" value="1"/>
            <input type="hidden" name="excel" value="0"/>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>订单号</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-group"></i>
                                </div>
                                <input type="text" name="lid" class="input-sm form-control" laceholder="订单号" value="{{$form['lid']}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>姓名</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-group"></i>
                                </div>
                                <input type="text" name="full_name" class="input-sm form-control" laceholder="学员姓名" value="{{$form['full_name']}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>身份证</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-credit-card"></i>
                                </div>
                                <input type="text" name="identity_number" class="input-sm form-control" laceholder="学员身份证号" value="{{$form['identity_number']}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>手机号</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-credit-card"></i>
                                </div>
                                <input type="text" name="phone" class="input-sm form-control" laceholder="学员手机号" value="{{$form['phone']}}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!--<div class="col-md-3">
                        <div class="form-group">
                            <label>校区</label>
                            <select class="form-control select2 select2-hidden-accessible" style="width: 100%;"
                                    tabindex="-1" aria-hidden="true" name="oid">
                                <option value="">全部</option>
                            </select>
                        </div>
                    </div>-->
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>状态</label>
                            <select class="form-control select2 select2-hidden-accessible" style="width: 100%;"
                                    tabindex="-1" aria-hidden="true" name="status">
                                <option value="">请选择状态</option>
                                <?php
                                foreach ($statusConfig as $k => $v) {
                                    echo "<option value='{$k}' " . (isset($form['status']) && $form['status'] == $k ? 'selected' : '') . ">" . $v . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>申请时间</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right datepicker" placeholder="开始时间"
                                       name="begin_time"
                                       value="<?php if (isset($form['begin_time'])) echo $form['begin_time']; ?>">
                                <div class="input-group-addon">至</div>
                                <input type="text" class="form-control pull-right datepicker" name="end_time"
                                       placeholder="结束时间"
                                       value="<?php if (isset($form['end_time'])) echo $form['end_time']; ?>">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>资金方</label>
                            <select class="form-control select2 select2-hidden-accessible" style="width: 100%;"
                                    tabindex="-1" aria-hidden="true" name="resource">
                                <option value="0">全部</option>
                                <?php
                                foreach ($resourceConfig as $k => $v) {
                                    echo "<option value='{$k}' " . (isset($form['resource']) && $form['resource'] == $k ? 'selected' : '') . ">" . $v . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <input type="button" class="btn btn-default btn-sm" id="log_btn"
                       onclick="document.getElementsByName('excel')[0].value=0;document.form1.submit();" value='查询'/>
                <!--<input type="button" class="btn btn-default btn-sm"
                       onclick="document.getElementsByName('excel')[0].value=1;document.form1.submit();" value="导出"/>-->
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">明细</div>
                <div class="panel-body" style="overflow-x: auto;">
                    <table class="table table-hover table-bordered">
                        <thead>
                        <tr>
                            <td><b>单号</b></td>
                            <td><b>姓名</b></td>
                            <td><b>电话</b></td>
                            <td><b>课程</b></td>
                            <td><b>资金方</b></td>
                            <td><b>金额</b></td>
                            <td><b>类型</b></td>
                            <td><b>时间</b></td>
                            <td><b>状态</b></td>
                        </tr>
                        </thead>
                        <tbody>
                        <?php
                        $count = 0;
                        if (isset($lists) && !empty($lists)) {
                        foreach ($lists as $l) {
                        ?>
                        <tr data-id="<?php echo $l['id']; ?>"
                            style="background-color:<?php if ($l['status'] == LOAN_11100_OVERDUE) {
                                echo "#44C8F4";
                            } ?>">
                            <td><?php echo $l['id']; ?></td>
                            <td>{{$l['full_name']}}<br/>{{$l['identity_number']}}</td>
                            <td>{{$l['phone']}}</td>
                            <td>{{$l['org_short_name']}}<br />{{$l['class_name']}}</td>
                            <td>{{$l['resource_desc']}}</td>
                            <td>申请 <?php echo \App\Components\OutputUtil::echoMoney($l['borrow_money']); ?><br />放款 <?php echo \App\Components\OutputUtil::echoMoney($l['org_receivable']); ?></td>
                            <td>{{$l['loan_product_desc']}}</td>
                            <td>申请{{$l['create_time']}}<br />放款<?php echo $l['loan_time']; ?></td>
                            <td><?php
                                if (strtotime($l['loan_time']) > 0) {
                                    echo $l['loan_time'];
                                }
                                ?></td>
                            <td style="color:<?php if ($l['status'] == LOAN_11100_OVERDUE) {echo "red";} ?>"><?php
                                echo $l['status_B'];
                                if (in_array($l['status'], array(LOAN_2100_SCHOOL_REFUSE, LOAN_3100_KZ_REFUSE, LOAN_4100_P2P_REFUSE, LOAN_2000_SCHOOL_CONFIRM))) {
                                    echo '<br />(原因:' . html_entity_decode($l['audit_opinion']) . ')';
                                }
                                ?></td>
                        </tr>
                        <?php }
                        } else {
                            echo "<tr><td align='center' colspan='12'>暂时没有符合条件的数据！</td><tr>";
                        }
                        ?>
                        </tbody>
                    </table>
                    <?= html_entity_decode($page); ?>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        //$(".select2").select2();
        //Date picker
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });

    function goUrl(page) {
        var page = "page_" + page;
        var url = $("#" + page + "").data();
        $.pjax({
            url: url['url'],
            container: '#pjax-content',
            timeout: 10000,
        });
    }

    $("#loginform").keydown(function (e) {
        var e = e || event,
            keycode = e.which || e.keyCode;
        if (keycode == 13) {
            $("#log_btn").trigger("click");
        }
    });

</script>

@endsection
