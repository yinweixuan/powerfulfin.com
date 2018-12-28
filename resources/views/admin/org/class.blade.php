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
                            <label>课程id：</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-university"></i>
                                </div>
                                <input type="text" name="oid" class="form-control input-sm" placeholder="课程ID"
                                       value="{{$id}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>课程名称：</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-university"></i>
                                </div>
                                <input type="text" name="org_name" class="form-control input-sm"
                                       placeholder="请输入课程名称，模糊查询"
                                       value="{{$class_name}}">
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>机构ID：</label>
                            <input type="text" name="org_name" class="form-control input-sm"
                                   placeholder="请输入机构ID"
                                   value="{{$oid}}">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label>是否分期:</label>
                            <select class="form-control" style="width: 100%;"
                                    tabindex="-1" aria-hidden="true" name="can_loan">
                                <option value="">请选择……</option>
                                <option value="SUCCESS" @if($status == 'SUCCESS') selected @endif>可用</option>
                                <option value="FAIL" @if($status == 'FAIL') selected @endif>不可用</option>
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
                                        <td>课程ID</td>
                                        <td>课程</td>
                                        <td>机构</td>
                                        <td>价格</td>
                                        <td>类型</td>
                                        <td>课时</td>
                                        <td>网络授课</td>
                                        <td>状态</td>
                                        <td>创建时间</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach ($info as $item)
                                        <tr>
                                            <td>{{ $item['cid'] }}</td>
                                            <td>{{ $item['class_name'] }}</td>
                                            <td>{{ $item['org_name'] }}({{ $item['oid'] }})</td>
                                            <td>{{ $item['class_price'] }}</td>
                                            <td>{{ $item['class_type'] }}</td>
                                            <td>{{ $item['class_days'] }}</td>
                                            <td>
                                                @if($item['class_online'] == STATUS_SUCCESS)
                                                    支持
                                                @else
                                                    不支持
                                                @endif
                                            </td>
                                            <td>
                                                @if($item['status'] == STATUS_SUCCESS)
                                                    可用
                                                @else
                                                    不可用
                                                @endif
                                            </td>
                                            <td>{{$item['create_time']}}</td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                    <tfoot>
                                    <tr>
                                        <td>课程ID</td>
                                        <td>课程</td>
                                        <td>机构</td>
                                        <td>价格</td>
                                        <td>类型</td>
                                        <td>课时</td>
                                        <td>网络授课</td>
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
