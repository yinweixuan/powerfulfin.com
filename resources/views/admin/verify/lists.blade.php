<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">搜索</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i>
            </button>
        </div>
    </div>
    <form role="form" name='form1' action="" method="get">
        <input type="hidden" name="page" value="{{ $page }}">
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>姓&emsp;名&emsp;:</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-child"></i>
                            </div>
                            <input type="text" name="stuname" class="input-sm form-control"
                                   placeholder="学员姓名/UID/单号" value="{{$stuname}}">
                        </div>
                    </div>
                    <div class="form-group ">
                        <label>学&emsp;&emsp;校:</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-university"></i>
                            </div>
                            <input type="text" name="org_name" class="input-sm form-control" placeholder="学校名字"
                                   value="{{$org_name}}">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>身&ensp;份&ensp;证:</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-credit-card"></i>
                            </div>
                            <input type="text" name="identity_number" class="input-sm form-control"
                                   placeholder="身份证号"
                                   value="{{$identity_number}}">
                        </div>
                    </div>
                    <div class="form-group ">
                        <label>总&emsp;&emsp;校:</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-university"></i>
                            </div>
                            <input type="number" name="hid" class="input-sm form-control" placeholder="总校ID"
                                   value="{{ $hid }}">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>手&ensp;机&ensp;号:</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-phone"></i>
                            </div>
                            <input type="text" class="input-sm form-control" name="phone" placeholder="手机号"
                                   value="{{$phone}}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label>风控人员:</label>
                        <select class="form-control" style="width: 100%;"
                                tabindex="-1" aria-hidden="true" name="check_user">
                            <option value="0">请选择...</option>
                            @foreach($check_users as $key=>$value)
                                <option value="{{$value['id']}}"
                                        @if($check_user == $value['id']) selected @endif>
                                    {{$value['id']}}-{{ $value['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>资&ensp;金&ensp;方:</label>
                        <select class="form-control" style="width: 100%;"
                                tabindex="-1" aria-hidden="true" name="resource">
                            <option value="">请选择...</option>
                            @if(\App\Models\ActiveRecord\ARPFLoanProduct::$resourceCompany)
                                @foreach(\App\Models\ActiveRecord\ARPFLoanProduct::$resourceCompany as $key=>$value)
                                    <option value="{{ $key }}"
                                            @if($key == $resource) selected @endif> {{ $value }} </option>
                                @endforeach

                            @endif
                        </select>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <input type="submit" class="btn btn-sm btn-danger" value='查询'/>
            <input id="btnaudit" type="button" class="btn btn-sm btn-danger" value="抢单">
        </div>
    </form>
</div>
<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger">
            <div class="box-header">
                <h3 class="box-title">订单列表</h3>
            </div>
            <div class="box-body">
                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="row">
                        <div class="col-sm-12" style="overflow-x: auto;">
                            <table id="example2" class="table table-bordered table-hover" role="grid"
                                   aria-describedby="example2_info">
                                <thead>
                                <tr role="row">
                                    <th>单号</th>
                                    <th>资方</th>
                                    <th>机构</th>
                                    <th>姓名</th>
                                    <th>借款</th>
                                    <th>金融产品</th>
                                    <th>状态</th>
                                    <th>申请时间</th>
                                    <th>抢单</th>
                                    <th>操作</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($info as $item)
                                    <tr>
                                        <td>{{ $item['id'] }}</td>
                                        <td>{{ \App\Models\ActiveRecord\ARPFLoanProduct::$resourceCompanySimple[$item['resource']] }}</td>
                                        <td>{{ $item['org_name'] }}</td>
                                        <td>{{ $item['full_name'] }}</td>
                                        <td>￥{{ $item['borrow_money'] }}</td>
                                        <td>{{ $loan_product[$item['id']]['loan_product_name'] }}</td>
                                        <td>{{ \App\Models\Server\BU\BULoanStatus::getStatusDescriptionForAdmin($item['status']) }}</td>
                                        <td>{{ date('m-d H:i',$item['create_time']) }}</td>
                                        <td>
                                            @if(empty($item['auditer']))
                                                @if(array_key_exists(\Encore\Admin\Facades\Admin::user()->id ,$check_users))
                                                    <input type="checkbox" value="{{ $item['id'] }}" class="auditor">
                                                @else
                                                    无权抢单
                                                @endif
                                            @else
                                                {{ $check_users[$item['auditer']]['name'] }}
                                            @endif
                                        </td>
                                        <td>
                                            <a href="/admin/loan/info?lid={{ $item['id'] }}">
                                                <i class="fa fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr role="row">
                                    <th>单号</th>
                                    <th>资方</th>
                                    <th>机构</th>
                                    <th>姓名</th>
                                    <th>借款</th>
                                    <th>金融产品</th>
                                    <th>状态</th>
                                    <th>申请时间</th>
                                    <th>抢单</th>
                                    <th>操作</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-sm-7">
                            <div class="dataTables_paginate paging_simple_numbers">
                                {{ $info->links() }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        $('#btnaudit').click(function () {
            var ids = [];
            $('.auditor:checked').each(function () {
                ids.push(this.value);
            });
            if (ids.length == 0) {
                alert("请选中要抢的订单");
                return false;
            }
            $.ajax({
                type: 'POST',
                url: '/admin/verify/collect',
                data: {ids: ids.join(',')},
                dataType: 'json',
                success: function (responseData) {
                    if (responseData.code == 0) {
                        alert('操作成功.锁定订单:' + responseData.data.ids);
                        document.location.reload();
                        return true;
                    } else if (responseData.msg) {
                        alert(responseData.msg);
                        return false;
                    } else {
                        alert('未知错误');
                        return false;
                    }
                }
            });
        });
    });
</script>
