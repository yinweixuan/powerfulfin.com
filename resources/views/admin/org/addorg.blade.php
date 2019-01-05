<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/1/5
 * Time: 8:42 PM
 */
?>
<section class="content">
    <div class="box box-danger">
        <div class="box-header with-border">
            <h3 class="box-title">新增</h3>
        </div>
        <form class="form-horizontal" role="form" name="form" action="" method="post">
            <div class="box-body">
                <div class="form-group">
                    <label class="col-sm-2 control-label">商户名称</label>
                    <div class="col-sm-9">
                        <input type="hidden" value="{{ $orgHead['hid'] }}" name="hid">
                        <input type="hidden" value="addorg" name="type">
                        <input type="text" class="form-control" value="{{ $orgHead['full_name'] }}" readonly>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">分校名称</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" placeholder="请填写分校名称" value=""
                               name="org_name">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">分校简称</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" placeholder="请填写分校简称" value="" name="short_name">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">分校状态</label>
                    <div class="col-sm-9">
                        <select name="status" style="width: 100%;">
                            <option value="">请选择</option>
                            <option value="{{ STATUS_SUCCESS }}">可用</option>
                            <option value="{{ STATUS_FAIL }}">不可用</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">是否分期</label>
                    <div class="col-sm-9">
                        <select name="can_loan" style="width: 100%;">
                            <option value="">请选择</option>
                            <option value="{{ STATUS_SUCCESS }}">支持分期</option>
                            <option value="{{ STATUS_FAIL }}">不支持分期</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">请选择机构所在省</label>
                    <div class="col-sm-9">
                        <input type="hidden" name="org_province" value="">
                        <select name="org_province_select" style="width: 100%">
                            <option value="">请选择分校所在省</option>
                            @foreach($province as $item)
                                <option value="{{ $item['areaid'] }}">{{ $item['joinname'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">请选择分校所在市或区</label>
                    <div class="col-sm-9">
                        <input type="hidden" name="org_city" value="">
                        <select name="org_city_select" style="width: 100%">
                            <option value="">请选择分校所在市或区</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-sm-2 control-label">请选择分校所在区域</label>
                    <div class="col-sm-9">
                        <input type="hidden" name="org_area" value="">
                        <select name="org_area_select" style="width: 100%">
                            <option value="">请选择分校所在区域</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">详细地址</label>
                    <div class="col-sm-9">
                        <input type="text" class="form-control" placeholder="请填写详细地址" value="" name="org_address">
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-2 control-label">GPS坐标</label>
                    <div class="col-sm-9 input-group">
                        <div class="col-sm-5">
                            <input type="text" class="form-control" name="org_lng" placeholder="请填写GPS经度坐标">
                        </div>
                        <div class="col-sm-5">
                            <input type="text" class="form-control" name="org_lat" placeholder="请填写GPS纬度度坐标">
                        </div>
                        <div class="col-sm-2">
                            <a href="http://api.map.baidu.com/lbsapi/getpoint/index.html" target="_blank"
                               class="btn btn-sm btn-danger">拾取坐标</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class=" box-footer">
                <a href="/admin/org/head" class="btn btn-sm btn-default">取消</a>
                <button name="submit" type="submit" class="btn btn-sm btn-danger pull-right">提交</button>
            </div>
        </form>
    </div>
</section>


<script type="text/javascript">
    $(function () {
        $("select[name='org_province_select']").change(function () {
            var org_province = $(this).val();
            if (org_province.length > 0 && org_province != "") {
                $("input[name='org_province']").val(org_province);
                if ($("input[name='org_city']").val().length > 0) {
                    $("input[name='org_city']").val("");
                }
                if ($("input[name='org_area']").val().length > 0) {
                    $("input[name='org_area']").val("");
                }
                $.ajax({
                    type: 'POST',
                    url: '/admin/area/city',
                    data: {province: org_province},
                    dataType: 'json',
                    success: function (response) {
                        var data = response.data;
                        var html = '<option value="">请选择分校所在市或区</option>';
                        $.each(data, function (key, val) {
                            html += '<option value="' + val.areaid + '">' + val.name + '</option>';
                        });
                        $("select[name='org_city_select']").html(html);
                    }
                });
            }
        });
        $("select[name='org_city_select']").change(function () {
            var org_city = $(this).val();
            if (org_city.length > 0 && org_city != "") {
                $("input[name='org_city']").val(org_city);
                if ($("input[name='org_area']").val().length > 0) {
                    $("input[name='org_area']").val("");
                }
                $.ajax({
                    type: 'POST',
                    url: '/admin/area/area',
                    data: {city: org_city},
                    dataType: 'json',
                    success: function (response) {
                        var data = response.data;
                        var html = '<option value="">请选择分校所在区域</option>';
                        $.each(data, function (key, val) {
                            html += '<option value="' + val.areaid + '">' + val.name + '</option>';
                        });
                        $("select[name='org_area_select']").html(html);
                    }
                });
            }
        });
        $("select[name='org_area_select']").change(function () {
            var org_area = $(this).val();
            if (org_area.length > 0 && org_area != "") {
                $("input[name='org_area']").val(org_area);
            }
        });
        $("button[name='submit']").click(function () {
            var org_name = $("input[name='org_name']").val();
            if (org_name.length == 0 || org_name == "") {
                alert("请填写分校名称");
                return false;
            }

            var short_name = $("input[name='short_name']").val();
            if (short_name.length == 0 || short_name == "") {
                alert("请填写分校简称");
                return false;
            }

            var status = $("select[name='status']").val();
            if (status == "") {
                alert("请选择分校状态");
                return false;
            }

            var can_loan = $("select[name='can_loan']").val();
            if (can_loan == "") {
                alert("请选择分校是否支持分期业务");
                return false;
            }

            var org_province = $("input[name='org_province']").val();
            if (org_province.length == 0 || org_province == "") {
                alert("请选择机构所在省");
                return false;
            }

            var org_city = $("input[name='org_city']").val();
            if (org_city.length == 0 || org_city == "") {
                alert("请选择分校所在市或区");
                return false;
            }

            var org_area = $("input[name='org_area']").val();
            if (org_area.length == 0 || org_area == "") {
                alert("请选择分校所在区域");
                return false;
            }

            var org_address = $("input[name='org_address']").val();
            if (org_address.length == 0 || org_address == "") {
                alert("请填写详细地址");
                return false;
            }

            var org_lng = $("input[name='org_lng']").val();
            if (org_lng.length == 0 || org_lng == "") {
                alert("请填写GPS经度坐标");
                return false;
            }

            var org_lat = $("input[name='org_lat']").val();
            if (org_lat.length == 0 || org_lat == "") {
                alert("请填写GPS维度坐标");
                return false;
            }
            $("form[name='form']").submit();
        })
    });
</script>
