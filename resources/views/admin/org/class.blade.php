<div class="box box-danger collapsed-box">
    <div class="box-header with-border">
        <h3 class="box-title">搜索</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
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
                                   value="{{$cid}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>商户ID：</label>
                        <input type="text" name="hid" class="form-control input-sm"
                               placeholder="商户ID"
                               value="{{$hid}}">
                    </div>
                    <div class="form-group">
                        <label>课程价格(大于)：</label>
                        <input type="text" name="class_price_min" class="form-control input-sm"
                               placeholder="请输入课程价格"
                               value="{{$class_price_min}}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>课程名称：</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-university"></i>
                            </div>
                            <input type="text" name="class_name" class="form-control input-sm"
                                   placeholder="请输入课程名称，模糊查询"
                                   value="{{$class_name}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>分校名称：</label>
                        <input type="text" name="full_name" class="form-control input-sm"
                               placeholder="请输入商户名称，模糊查询"
                               value="{{$full_name}}">
                    </div>
                    <div class="form-group">
                        <label>课程价格(小于)：</label>
                        <input type="text" name="class_price_max" class="form-control input-sm"
                               placeholder="请输入课程价格"
                               value="{{$class_price_max}}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>分校ID：</label>
                        <input type="text" name="oid" class="form-control input-sm"
                               placeholder="分校ID"
                               value="{{$oid}}">
                    </div>
                    <div class="form-group">
                        <label>状态:</label>
                        <select class="form-control" style="width: 100%;"
                                tabindex="-1" aria-hidden="true" name="status">
                            <option value="">请选择……</option>
                            <option value="SUCCESS" @if($status == 'SUCCESS') selected @endif>可用</option>
                            <option value="FAIL" @if($status == 'FAIL') selected @endif>不可用</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>课程类型：</label>
                        <select name="class_type" style="width: 100%">
                            <option value="">请选择</option>
                            IT、语言、财会、学历、驾校、健身、K12、其他
                            <option value="IT" @if($business_type == "IT") selected @endif>IT</option>
                            <option value="语言" @if($business_type == "语言") selected @endif>语言</option>
                            <option value="财会" @if($business_type == "财会") selected @endif>财会</option>
                            <option value="学历" @if($business_type == "学历") selected @endif>学历</option>
                            <option value="驾校" @if($business_type == "驾校") selected @endif>驾校</option>
                            <option value="健身" @if($business_type == "健身") selected @endif>健身</option>
                            <option value="K12" @if($business_type == "K12") selected @endif>K12</option>
                            <option value="其他" @if($business_type == "其他") selected @endif>其他</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>分校名称：</label>
                        <input type="text" name="oid" class="form-control input-sm"
                               placeholder="请输入分校名称，模糊查询"
                               value="{{$org_name}}">
                    </div>
                    <div class="form-group">
                        <label>网络授课:</label>
                        <select class="form-control" style="width: 100%;"
                                tabindex="-1" aria-hidden="true" name="class_online">
                            <option value="">请选择……</option>
                            <option value="SUCCESS" @if($status == 'SUCCESS') selected @endif>支持网络授课</option>
                            <option value="FAIL" @if($status == 'FAIL') selected @endif>不支持网络授课</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>课程时长：</label>
                        <input type="text" name="class_days" class="form-control input-sm"
                               placeholder="请输入分校名称，模糊查询"
                               value="{{$class_days}}">
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <button type="submit" class="btn btn btn-danger">查询</button>
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
                                    <td>课程ID</td>
                                    <td>课程</td>
                                    <td>分校</td>
                                    <td>商户</td>
                                    <td>价格</td>
                                    <td>类型</td>
                                    <td>课时</td>
                                    <td>网络授课</td>
                                    <td>状态</td>
                                    <td>创建时间</td>
                                    <td>操作</td>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($info as $item)
                                    <tr>
                                        <td>{{ $item['cid'] }}</td>
                                        <td>{{ $item['class_name'] }}</td>
                                        <td>{{ $item['org_name'] }}({{ $item['oid'] }})</td>
                                        <td>{{ $item['full_name'] }}({{ $item['hid'] }})</td>
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
                                        <td>{{ date('Y-m-d',strtotime($item['create_time'])) }}</td>
                                        <td><a href="/admin/org/editclass?cid={{ $item['cid'] }}"
                                               class="btn btn-sm btn-danger">更新</a></td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td>课程ID</td>
                                    <td>课程</td>
                                    <td>分校</td>
                                    <td>商户</td>
                                    <td>价格</td>
                                    <td>类型</td>
                                    <td>课时</td>
                                    <td>网络授课</td>
                                    <td>状态</td>
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
