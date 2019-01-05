<div class="box box-danger collapsed-box">
    <div class="box-header with-border">
        <h3 class="box-title">搜索</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus-square"></i>
            </button>
        </div>
    </div>
    <form class="form_inline" role="form" name='form1' action=""
          method="get">
        <input type="hidden" name="page" value="{{$page}}"/>
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>分校ID：</label>
                        <input type="text" name="oid" class="form-control input-sm" placeholder="请输入分校ID"
                               value="{{$oid}}">
                    </div>
                    <div class="form-group">
                        <label>省份：</label>
                        <select name="org_province_select" class="form-control" style="width: 100%;"
                                tabindex="-1" aria-hidden="true">
                            <option value="0">请选择...</option>
                            @foreach($province as $item)
                                <option value="{{ $item['areaid'] }}"
                                        @if($item['areaid'] == $org_province) selected @endif>{{ $item['joinname'] }}</option>
                            @endforeach
                        </select>
                        <input type="hidden" value="{{ $org_province }}" name="org_province">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>分校名称：</label>
                        <input type="text" name="org_name" class="form-control input-sm"
                               placeholder="请输入分校名称，模糊查询"
                               value="{{$org_name}}">
                    </div>
                    <div class="form-group">
                        <label>城市：</label>
                        <input type="hidden" name="org_city" value="{{ $org_city }}">
                        <select name="org_city_select" class="form-control" style="width: 100%;"
                                tabindex="-1" aria-hidden="true">
                            <option value="0">请选择...</option>

                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>商户ID：</label>
                        <input type="text" name="hid" class="form-control input-sm"
                               placeholder="请输入商户ID"
                               value="{{$hid}}">
                    </div>
                    <div class="form-group">
                        <label>状态：</label>
                        <select name="status" style="width: 100%;">
                            <option value="">请选择</option>
                            <option value="{{ STATUS_SUCCESS }}" @if($sttus == STATUS_SUCCESS) selected @endif>可用
                            </option>
                            <option value="{{ STATUS_FAIL }}" @if($sttus == STATUS_FAIL) selected @endif>不可用
                            </option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>商户名称：</label>
                        <input type="text" name="full_name" class="form-control input-sm"
                               placeholder="请输入商户名称，模糊查询"
                               value="{{ $full_name }}">
                    </div>
                    <div class="form-group">
                        <label>是否分期:</label>
                        <select class="form-control" style="width: 100%;"
                                tabindex="-1" aria-hidden="true" name="can_loan">
                            <option value="">请选择是否支持分期</option>
                            <option value="SUCCESS" @if($can_loan == 'SUCCESS') selected @endif>支持分期</option>
                            <option value="FAIL" @if($can_loan == 'FAIL') selected @endif>不支持分期</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <button type="submit" class="btn btn-sm btn-danger">查询</button>
        </div>
    </form>
</div>
<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger">
            <!-- /.box-header -->
            <div class="box-body">
                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="row">
                        <div class="col-sm-6"></div>
                        <div class="col-sm-6"></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12" style="overflow-x: auto;">
                            <table id="example2" class="table table-bordered table-hover dataTable"
                                   role="grid" aria-describedby="example2_info">
                                <thead>
                                <tr role="row">
                                    <td>分校ID</td>
                                    <td>名称</td>
                                    <td>商户</td>
                                    <td>简称</td>
                                    <td>状态</td>
                                    <td>分期</td>
                                    <td>创建时间</td>
                                    <td>操作</td>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($info as $org)
                                    <tr>
                                        <td>{{ $org['id'] }}</td>
                                        <td>{{ $org['org_name'] }}</td>
                                        <td>{{ $org['full_name'] }}({{ $org['hid'] }})</td>
                                        <td>{{ $org['short_name'] }}</td>

                                        <td>
                                            @if($org['status'] == STATUS_SUCCESS)
                                                可用
                                            @else
                                                不可用
                                            @endif
                                        </td>
                                        <td>
                                            @if($org['can_loan'] == STATUS_SUCCESS)
                                                可用
                                            @else
                                                不可用
                                            @endif
                                        </td>
                                        <td>{{ date('Y-m-d',strtotime($org['create_time'])) }}</td>
                                        <td>
                                            <a href="/admin/org/addorgclass?hid={{ $org['hid'] }}&oid={{ $org['id'] }}"
                                               class="btn btn-sm btn-danger">添加课程</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td>分校ID</td>
                                    <td>名称</td>
                                    <td>商户</td>
                                    <td>简称</td>
                                    <td>状态</td>
                                    <td>分期</td>
                                    <td>创建时间</td>
                                    <td>操作</td>
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
<script type="text/javascript">
    $(function () {
        $("select[name='org_province_select']").change(function () {
            var org_province = $(this).val();
            if (org_province.length > 0 && org_province != "") {
                $("input[name='org_province']").val(org_province);
                if ($("input[name='org_city']").val().length > 0) {
                    $("input[name='org_city']").val("");
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
            }
        });

        org_city();
    });

    function org_city() {
        $.ajax({
            type: 'POST',
            url: '/admin/area/city',
            data: {province: $("input[name='org_province']").val()},
            dataType: 'json',
            success: function (response) {
                var data = response.data;
                var html = '<option value="">请选择分校所在市或区</option>';
                $.each(data, function (key, val) {
                    if (val.areaid == $("input[name='org_city']").val()) {
                        html += '<option value="' + val.areaid + '" selected>' + val.name + '</option>';
                    } else {
                        html += '<option value="' + val.areaid + '">' + val.name + '</option>';
                    }
                })
                $("select[name='org_city_select']").html(html);
            }
        });
    }
</script>
