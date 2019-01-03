<section class="content">
    <div class="box box-danger">
        <div class="box-header with-border">
            <h3 class="box-title">搜索</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
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
                            <label>机构id：</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-university"></i>
                                </div>
                                <input type="text" name="oid" class="form-control input-sm" placeholder="机构ID/机构简称"
                                       value="{{$oid}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>省份：</label>
                            <select class="form-control" style="width: 100%;"
                                    tabindex="-1" aria-hidden="true" id="select_province" name="province">
                                <option value="0">请选择...</option>

                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>机构名称：</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-university"></i>
                                </div>
                                <input type="text" name="org_name" class="form-control input-sm"
                                       placeholder="请输入机构名称，模糊查询"
                                       value="{{$org_name}}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>城市：</label>
                            <select class="form-control" style="width: 100%;"
                                    tabindex="-1" aria-hidden="true" id="select_city" name="city">
                                <option value="0">请选择...</option>

                            </select>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>金融ID：</label>
                            <input type="text" name="org_name" class="form-control input-sm"
                                   placeholder="请输入金融ID"
                                   value="{{$hid}}">
                        </div>
                    </div>
                    <div class="col-md-3">
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
                <button type="submit" class="btn btn-primary">查询</button>
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
                                        <td>机构ID</td>
                                        <td>金融ID</td>
                                        <td>简称</td>
                                        <td>全称</td>
                                        <td>状态</td>
                                        <td>分期</td>
                                        <td>创建时间</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($info as $org)
                                        <tr>
                                            <td>{{ $org['id'] }}</td>
                                            <td>{{ $org['hid'] }}</td>
                                            <td>{{ $org['short_name'] }}</td>
                                            <td>{{ $org['org_name'] }}</td>
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
                                            <td>{{$org['create_time']}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td>机构ID</td>
                                        <td>金融ID</td>
                                        <td>简称</td>
                                        <td>全称</td>
                                        <td>状态</td>
                                        <td>分期</td>
                                        <td>创建时间</td>
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
</section>
<script type="text/javascript">

</script>
