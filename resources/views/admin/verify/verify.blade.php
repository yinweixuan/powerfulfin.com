<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/20
 * Time: 10:37 AM
 */
?>
<div class="row">
    <div class="col-md-12">
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">订单审核：{{$base['id']}}-{{ $real['full_name'] }}</h3>
            </div>
            <div class="box-header with-border">
                <h3 class="box-title">审核人员：{{ \Encore\Admin\Facades\Admin::user()->name }}</h3>
            </div>
            <div class="box-header with-border">
                <h3 class="box-title">审核日期：{{ date('Y年m月d日') }}</h3>
            </div>
            <form class="form-horizontal" id="form" action="{{ admin_base_path('verify/check') }}" method="post">
                <div class="box-body">
                    <div class="form-group">
                        <label for="inputEmail3" class="col-sm-2 control-label">审核结果</label>
                        <div class="col-sm-10">
                            <select class="form-control" id="result" name="result">
                                <option value="">请选择审核结果</option>
                                <option value="1">审核通过</option>
                                <option value="2">审核拒绝</option>
                                <option value="3">永久拒绝</option>
                                <option value="4">暂缓审核</option>
                                <option value="5">放弃审核</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group" id="div_remarks">
                        <label for="inputEmail3" class="col-sm-2 control-label">审核意见</label>
                        <div class="col-sm-10">
                            <input type="text" class="form-control" value="" placeholder="请填写审核意见" name="remarks"
                                   id="remarks">
                            <input type="hidden" name="lid" value="{{ $base['id'] }}">
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
                <div class="box-footer">
                    <button type="submit" id="submit" class="btn btn-sm btn-danger">提交</button>
                    <a href="{{ $_SERVER["HTTP_REFERER"] }}" class="btn btn-sm btn-default pull-right">返回</a>
                </div>
            </form>
            <!-- /.box-footer-->
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        $('#submit').click(function () {
            var rel = $('#result').val();
            if (rel == 1) {
                var c = confirm('确认通过审核？');
                if (!c) {
                    return false;
                }
            } else if (rel == 2) {
                var c = confirm('确认审核拒绝？');
                if (!c) {
                    return false;
                }
            } else if (rel == 3) {
                var c = confirm('确认永久拒绝？');
                if (!c) {
                    return false;
                }
            } else if (rel == 4) {
                var c = confirm('确认暂缓审核？');
                if (!c) {
                    return false;
                }
            } else if (rel == 5) {
                var c = confirm('确认放弃审核？如确认，订单可重新抢单');
                if (!c) {
                    return false;
                }
            } else {
                alert('请选择审核结果');
                return false;
            }

            var remarks = $('#remarks').val();
            if (rel == 2 || rel == 3 || rel == 4) {
                if (remarks == "" || remarks == undefined) {
                    alert('请填写审核意见！');
                    return false;
                }
            }
        });
    });
</script>
