@extends('org.common.base')
@section('title',  '报名确认')
@section('content')
<?php
/**
 * 等待机构报名确认订单列表
 * User: haoxiang
 * Date: 2018/12/25
 * Time: 10:29 AM
 */
?>
<div id="pjax-content">
    <div class="panel panel-default">
        <div class="panel-heading">搜索</div>
        <form class="form_inline">
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>订&ensp;单&ensp;号</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-dot-circle-o"></i>
                                </div>
                                <input type="text" name="lid" class="input-sm form-control" placeholder="订单号"
                                       value="{{$form['lid']}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>姓&emsp;&emsp;名</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-group"></i>
                                </div>
                                <input type="text" name="full_name" class="input-sm form-control" laceholder="学员姓名"
                                       value="{{$form['full_name']}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>身&ensp;份&ensp;证</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-credit-card"></i>
                                </div>
                                <input type="text" name="identity_number" class="input-sm form-control" placeholder="身份证号"
                                       value="{{$form['identity_number']}}">
                            </div>
                        </div>
                    </div>
                </div>

            </div>
            <div class="panel-footer">
                <input type="hidden" name="query" value="1" />
                <button class="btn btn-default btn-sm">查询</button>
            </div>
        </form>
    </div>

    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">报名确认</div>
                <div class="panel-body" style="overflow-x: auto;">
                    <table class="table table-bordered table-hover general-table">
                        <thead>
                        <tr>
                            <th>单号</th>
                            <th>姓名</th>
                            <th>身份证号</th>
                            <th>课程</th>
                            <th>分期金额</th>
                            <th>申请时间</th>
                            <th>资金方</th>
                            <th>操作</th>
                        </tr>
                        </thead>
                        <tbody>
                        <?php foreach ($lists as $list) { ?>
                        <tr>
                            <td>{{$list['lid']}}</td>
                            <td>{{$list['full_name']}}</td>
                            <td>{{$list['identity_number']}}</td>
                            <td>{{$list['class_name']}}</td>
                            <td>￥{{$list['borrow_money']}}({{$list['class_price']}})</td>
                            <td>{{$list['create_time']}}</td>
                            <td>{{$list['resource_desc']}}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-success" onclick="classLoan({{$list['lid']}},1)">确认</button>
                                <button type="button" class="btn btn-sm btn-danger" style="margin-left: 20px;" onclick="classLoan({{$list['lid']}},2)">拒绝</button>
                                <button href="/order/detail?lid={{$list['lid']}}" style="margin-left: 20px;" class="btn btn-sm btn-default">详情</button>
                            </td>
                        </tr>
                        <?php } ?>
                        </tbody>
                    </table>
                    <?= html_entity_decode($page); ?>
                </div>
            </div>
        </div>
    </div>
    <!--loading -->
    <div id="loading" class="modal fade bs-example-modal-sm" style="padding-top: 300px;">
        <div class="modal-dialog modal-sm" style="width: 300px; height: 200px">
            <div class="modal-content">
                <div class="modal-header">
                    <h4 class="modal-title">业务操作中……</h4>
                </div>
                <div class="modal-body">
                    <div class="overlay">
                        <p style="text-align: center">
                            <i class="fa fa-refresh fa-spin"></i>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- 确认上课 -->
    <!-- 模态框（Modal） -->
    <div class="modal fade" id="class_do" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true" style="padding-top: 200px;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        &times;
                    </button>
                    <h4 class="modal-title" id="myModalLabel">
                        确认上课
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row" align="center">
                        成功放款后，每月15日为固定还款日（节假日顺延至工作日）
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                    <button type="button" id="sub_confirm" class="btn btn-primary php_submit" data-id="" value=""
                            onclick="sub_query();">提交
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal -->
    </div>
    <!-- 拒绝 -->
    <!-- 模态框（Modal） -->
    <div class="modal fade" id="refuse" tabindex="-1" role="dialog"
         aria-labelledby="myModalLabel" aria-hidden="true" style="padding-top: 200px;">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">
                        &times;
                    </button>
                    <h4 class="modal-title" id="myModalLabel">
                        拒绝报名
                    </h4>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <div class="form-group">
                            <label class="col-md-3 pr_0">拒绝申请原因是：</label>
                            <div class="col-lg-10">
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="optionsRadios" value="1">
                                        非报名学员
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="optionsRadios" value="2">
                                        申请课程错误
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="optionsRadios" value="3">
                                        申请金额错误
                                    </label>
                                </div>
                                <div class="radio">
                                    <label>
                                        <input type="radio" name="optionsRadios" value="-1">
                                        其他原因：
                                        <div class="input-group">
                                            <div class="input-group-sm">
                                            </div>
                                            <input type="text" class="input-sm form-group" id="remark" value="">
                                        </div>
                                    </label>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default clear_param" data-dismiss="modal">取消</button>
                    <button type="button" id="sub_refuse" class="btn btn-primary php_submit" data-id="" value=""
                            onclick="sub_refuse();">提交
                    </button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal -->
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        //Date picker
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });

    function classLoan(lid, type) {
        if (type == 1) {
            $("#class_do").modal('show');
            $("#sub_confirm").val(lid);
        } else if (type == 2) {
            $("#refuse").modal('show');
            $("#sub_refuse").val(lid);
        }
    }

    function sub_query() {
        $('#loading').modal('show');
        $('#class_do').modal('hide');
        var lid = $("#sub_confirm").val();
        $.ajax({
            type: "POST",
            url: "/organize/sure/class_do",
            data: {lid: lid, type: 1},
            dataType: "json",
            success: function (responseData) {
                $('#loading').modal('hide');
                if (responseData.code == 0) {
                    alert("已确认上课,等待金融机构放款", "提示", function () {
                        history.go(0);
                    });
                } else {
                    alert(responseData.msg, "提示", function () {
                        history.go(0);
                    });
                }
            },
        });
    }

    function sub_refuse() {
        var remark = $("input[name='optionsRadios']:checked").val();
        if (remark == undefined) {
            alert("请选择拒绝原因", '信息错误');
            return false;
        }
        if (remark == -1) {
            var other_reason = $('#remark').val();
            if (other_reason == undefined) {
                alert('请填写其他原因！', '信息错误');
                return false;
            }
            remark = other_reason;
        }
        $('#loading').modal('show');
        $('#refuse').modal('hide');
        var lid = $("#sub_refuse").val();
        $.ajax({
            type: "POST",
            url: "/organize/sure/class_refuse",
            data: {lid: lid, type: 2, remark: remark},
            dataType: "json",
            success: function (responseData) {
                $('#loading').modal('hide');
                if (responseData.code == 0) {
                    alert("已拒绝上课", "提示", function () {
                        history.go(0);
                    });
                } else {
                    alert(responseData.msg, "提示", function () {
                        history.go(0);
                    });
                }
            },
        });
    }

    function goUrl(page) {
        var page = "page_" + page;
        var url = $("#" + page + "").data();
        console.log(url);
        $.pjax({
            url: url['url'],
            container: '#pjax-content',
            timeout: 10000,
        });
    }
</script>
@endsection
