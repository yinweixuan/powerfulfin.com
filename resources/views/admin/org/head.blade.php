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
                        <label>商户ID：</label>
                        <input type="text" name="oid" class="form-control input-sm" placeholder="商户ID"
                               value="{{$hid}}">
                    </div>
                    <div class="form-group">
                        <label>开户行：</label>
                        <select name="org_bank_code" style="width: 100%">
                            <option value="">请选择开户行</option>
                            @foreach(\App\Models\Server\BU\BUBanks::getBanksInfo() as $item)
                                <option value="{{ $item['bank_code'] }}"
                                        @if($org_bank_code == $item['bank_code']) selected @endif>{{ $item['bankname'] }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>商户名称：</label>
                        <input type="text" name="full_name" class="form-control input-sm"
                               placeholder="请输入商户名称，模糊查询"
                               value="{{$full_name}}">
                    </div>
                    <div class="form-group">
                        <label>开户账号：</label>
                        <input type="text" name="org_bank_account" class="form-control input-sm"
                               placeholder="请输入开户账号"
                               value="{{$org_bank_account}}">
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>营业执照：</label>
                        <input type="text" name="business_license" class="form-control input-sm"
                               placeholder="请输入统一社会信用代码，模糊查询"
                               value="{{$business_license}}">
                    </div>
                    <div class="form-group">
                        <label>业务类型：</label>
                        <select name="business_type" style="width: 100%">
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
                        <label>法人：</label>
                        <input type="text" name="legal_person" class="form-control input-sm"
                               placeholder="请输入法人姓名，模糊查询"
                               value="{{$legal_person}}">
                    </div>
                    <div class="form-group">
                        <label>法人身份证：</label>
                        <input type="text" name="legal_person_idcard" class="form-control input-sm"
                               placeholder="请输入法人姓名，模糊查询"
                               value="{{$legal_person_idcard}}">
                    </div>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <button type="submit" class="btn btn-sm btn-danger">查询</button>
            <a href="/admin/org/addhead" class="btn btn-sm btn-danger">新增</a>
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
                                    <td>商户ID</td>
                                    <td>全称</td>
                                    <td>营业执照</td>
                                    <td>银行开户</td>
                                    <td>法人</td>
                                    <td>联系人</td>
                                    <td>联系电话</td>
                                    <td>状态</td>
                                    <td>创建时间</td>
                                    <td>操作</td>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach ($info as $org)
                                    <tr>
                                        <td>{{ $org['hid'] }}</td>
                                        <td>
                                            <a href="/admin/org/headinfo?hid={{ $org['hid'] }}"
                                               style="color: #dd4b39">{{ $org['full_name'] }}</a>
                                        </td>
                                        <td>{{ $org['business_license'] }}</td>
                                        <td><img
                                                src="{{\App\Models\Server\BU\BUBanks::getBankLogo($org['org_bank_code'])}}"
                                                style="height: 20px">
                                            {{ $org['org_bank_account'] }}</td>
                                        <td>{{ $org['legal_person'] }}</td>
                                        <td>{{ $org['contact_name'] }}</td>
                                        <td>{{ $org['contact_phone'] }}</td>
                                        <td>
                                            @if($org['status'] == STATUS_SUCCESS)
                                                可用
                                            @else
                                                不可用
                                            @endif
                                        </td>
                                        <td>{{ date('Y-m-d',strtotime($org['create_time'])) }}</td>
                                        <td>
                                            <a href="/admin/org/addorg?hid={{ $org['hid'] }}"
                                               class="btn btn-sm btn-danger">添加分校</a>
                                            <a href="/admin/org/addorgclass?hid={{ $org['hid'] }}"
                                               class="btn btn-sm btn-danger">添加课程</a>
                                            <a href="/adin/org/edithead?hid={{ $org['hid'] }}"
                                               class="btn btn-sm btn-danger">修改</a>
                                        </td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <td>商户ID</td>
                                    <td>全称</td>
                                    <td>营业执照</td>
                                    <td>银行开户</td>
                                    <td>法人</td>
                                    <td>联系人</td>
                                    <td>联系电话</td>
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
