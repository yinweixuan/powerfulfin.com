<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">搜索</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <form class="form_inline" role="form" name='form1' action="" method="get">
        <input type="hidden" name="page" value="{{$page}}">
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>用户UID：</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-university"></i>
                            </div>
                            <input type="text" name="uid" class="form-control input-sm" placeholder="用户UID"
                                   value="{{$uid}}">
                        </div>
                    </div>

                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>手机号：</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-university"></i>
                            </div>
                            <input type="number" name="phone" class="form-control input-sm" placeholder="手机号"
                                   value="{{$phone}}">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>姓名：</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-university"></i>
                            </div>
                            <input type="text" name="full_name" class="form-control input-sm" placeholder="姓名"
                                   value="{{$full_name}}">
                        </div>
                    </div>

                </div>
                <div class="col-md-3">
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
                                <tr>
                                    <th>用户UID</th>
                                    <th>用户名</th>
                                    <th>手机号</th>
                                    <th>姓名</th>
                                    <th>注册时间</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($info as $item)
                                    <tr>
                                        <td>{{$item['id']}}</td>
                                        <td>{{$item['username']}}</td>
                                        <td>{{$item['phone']}}</td>
                                        <td>{{$item['full_name']}}</td>
                                        <td>{{substr($item['created_at'],0,10)}}</td>
                                        <td>
                                            <a class="btn btn-sm btn-danger"
                                               href="{{ admin_base_path('users/real') }}?uid={{ $item['id'] }}">查看实名</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>用户UID</th>
                                    <th>用户名</th>
                                    <th>手机号</th>
                                    <th>姓名</th>
                                    <th>注册时间</th>
                                    <th>操作</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                {{$info->links()}}
            </div>
        </div>
    </div>
</div>
