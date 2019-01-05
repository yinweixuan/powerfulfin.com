<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/1/5
 * Time: 10:13 PM
 */
?>
<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">新增</h3>
    </div>
    <form class="form-horizontal" role="form" name="form" action="" method="post">
        <div class="box-body">
            <div class="form-group">
                <label class="col-sm-2 control-label">商户名称</label>
                <div class="col-sm-9">
                    <input type="hidden" value="{{ $hid }}" name="hid">
                    <input type="hidden" value="{{ $oid }}" name="oid">
                    <input type="hidden" value="addclass" name="type">
                    <input type="text" class="form-control" value="{{ $orgHead['full_name'] }}" readonly>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">分校名称</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" value="{{ $org['org_name'] }}" readonly>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">课程名称</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" placeholder="请填写课程名称" value="" name="class_name">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">课程状态</label>
                <div class="col-sm-9">
                    <select name="status" style="width: 100%;">
                        <option value="">请选择</option>
                        <option value="{{ STATUS_SUCCESS }}">可用</option>
                        <option value="{{ STATUS_FAIL }}">不可用</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">是否网络授课</label>
                <div class="col-sm-9">
                    <select name="class_online" style="width: 100%;">
                        <option value="">请选择</option>
                        <option value="{{ STATUS_SUCCESS }}">支持网络授课</option>
                        <option value="{{ STATUS_FAIL }}">不支持网络授课</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">课程类型</label>
                <div class="col-sm-9">
                    <select name="class_type" style="width: 100%">
                        <option value="">请选择</option>
                        IT、语言、财会、学历、驾校、健身、K12、其他
                        <option value="IT">IT</option>
                        <option value="语言">语言</option>
                        <option value="财会">财会</option>
                        <option value="学历">学历</option>
                        <option value="驾校">驾校</option>
                        <option value="健身">健身</option>
                        <option value="K12">K12</option>
                        <option value="其他">其他</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">课程价格</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" placeholder="请填写课程价格，单位：元" value="" name="class_price">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">课程时间</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" placeholder="请填写课程时长，单位：天" value="" name="class_days">
                </div>
            </div>

        </div>
        <div class=" box-footer">
            <a href="/admin/org/class" class="btn btn-sm btn-default">取消</a>
            <button name="submit" type="submit" class="btn btn-sm btn-danger pull-right">提交</button>
        </div>
    </form>
</div>

<script type="text/javascript">
    $(function () {
        $("button[name='submit']").click(function () {
            var class_name = $("input[name='class_name']").val();
            if (class_name.length == 0 || class_name == "") {
                alert("请填写课程名称");
                return false;
            }

            var status = $("select[name='status']").val();
            if (status == "") {
                alert("请选择课程状态");
                return false;
            }

            var class_online = $("select[name='class_online']").val();
            if (class_online == "") {
                alert("请选择课程是否支持网络授课");
                return false;
            }
            var class_type = $("select[name='class_type']").val();
            if (class_type == "") {
                alert("请选择课程类型");
                return false;
            }

            var class_price = $("input[name='class_price']").val();
            if (class_price.length == 0 || class_price == "") {
                alert("请填写课程价格");
                return false;
            }

            var class_days = $("input[name='class_days']").val();
            if (class_days.length == 0 || class_days == "") {
                alert("请填写课程时长");
                return false;
            }
            $("form[name='form']").submit();
        })
    });
</script>
