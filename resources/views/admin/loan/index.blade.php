<section class="content">
    <div class="box box-danger collapsed-box">
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
                        <div class="form-group">
                            <label>资方借据:</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-sticky-note-o"></i>
                                </div>
                                <input type="text" name="resource_loan_id" class="input-sm form-control"
                                       placeholder="晋商借据号"
                                       value="{{ $resource_loan_id }}">
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
                        <div class="form-group">
                            <label>分期状态:</label>
                            <select class="form-control" style="width: 100%;"
                                    tabindex="-1" aria-hidden="true" id="select_status" name="status">
                                <option value="">请选择...</option>
                                @foreach(\App\Models\Server\BU\BULoanStatus::getStatusDescriptionForAdmin() as $key=>$value)
                                    <option value="{{ $key }}" @if($key == $status) selected @endif>{{ $key }}
                                        -{{$value}} </option>
                                @endforeach
                            </select>
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
                            <label>放款时间:</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="date" class="form-control pull-right" placeholder="开始时间"
                                       name="beginDate" value="{{ $beginDate }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label>银行列表:</label>
                            <select class="form-control" style="width: 100%;"
                                    tabindex="-1" aria-hidden="true" name="bank_code">
                                <option value="0">请选择...</option>
                                @foreach(\App\Models\Server\BU\BUBanks::getBanksInfo() as $key=>$value)
                                    <option value="{{$value['bank_code']}}"
                                            @if($bank_code == $value['bank_code']) selected @endif>
                                        {{$value['bank_code']}}-{{ $value['bankname'] }}</option>
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
                        <div class="form-group">
                            <label>放款时间:</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="date" class="form-control pull-right" name="endDate"
                                       placeholder="结束时间" value="{{ $endDate }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                <input type="submit" class="btn btn-sm btn-primary" value='查询'/>
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
                                        <th>放款时间</th>
                                        <th>操作</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($info as $item)
                                        <tr>
                                            <td>{{ $item['id'] }}</td>
                                            <td>{{ \App\Models\ActiveRecord\ARPFLoanProduct::$resourceCompanySimple[$item['resource']] }}</td>
                                            <td>{{ $item['org_name'] }}</td>
                                            <td>{{ $item['full_name'] }}({{ $item['uid'] }}
                                                )<br/>{{ $item['identity_number'] }}</td>
                                            <td>￥{{ $item['borrow_money'] }}</td>
                                            <td>{{ $loan_product[$item['id']]['loan_product_name'] }}</td>
                                            <td>{{ \App\Models\Server\BU\BULoanStatus::getStatusDescriptionForAdmin($item['status']) }}</td>
                                            <td>{{ $item['create_time'] }}</td>
                                            <td>{{ $item['loan_time'] }}</td>
                                            <td>
                                                <a class="btn btn-sm btn-success"
                                                   href="/admin/loan/info?lid={{ $item['id'] }}">详情</a>
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
                                        <th>放款时间</th>
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
</section>

