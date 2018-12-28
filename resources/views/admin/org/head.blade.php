<section class="content">
    <div class="box box-default">
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
                            <label>商户ID：</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-university"></i>
                                </div>
                                <input type="text" name="oid" class="form-control input-sm" placeholder="商户ID"
                                       value="{{$hid}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>商户名称：</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-university"></i>
                                </div>
                                <input type="text" name="org_name" class="form-control input-sm"
                                       placeholder="请输入商户名称，模糊查询"
                                       value="{{$full_name}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">

                    </div>
                    <div class="col-md-3">

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
            <div class="box">
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
                                        <td>商户ID</td>
                                        <td>全称</td>
                                        <td>营业执照</td>
                                        <td>银行开户</td>
                                        <td>状态</td>
                                        <td>创建时间</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($info as $org)
                                        <tr>
                                            <td>{{ $org['hid'] }}</td>
                                            <td>{{ $org['full_name'] }}</td>
                                            <td>{{ $org['business_license'] }}</td>
                                            <td><img
                                                    src="{{\App\Models\Server\BU\BUBanks::getBankLogo($org['org_bank_code'])}}" style="height: 20px">
                                                {{ $org['org_bank_account'] }}</td>
                                            <td>
                                                @if($org['status'] == STATUS_SUCCESS)
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
                                        <td>商户ID</td>
                                        <td>全称</td>
                                        <td>营业执照</td>
                                        <td>银行开户</td>
                                        <td>状态</td>
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
