<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/1/6
 * Time: 12:04 AM
 */
?>
<style>
    .nav-tabs-custom > .nav-tabs > li.active {
        border-top-color: #dd4b39;
    }
</style>
<div class="row">
    <div class="col-md-3">
        <div class="box box-danger">
            <div class="box-body box-profile">
                <h3 class="profile-username text-center">{{ $org_head['full_name'] }}</h3>

                <p class="text-muted text-center">{{ $org_head['hid'] }}</p>

                <ul class="list-group list-group-unbordered">
                    <li class="list-group-item">
                        <b>法人代表</b> <a class="pull-right">{{ $org_head['legal_person'] }}</a>
                    </li>
                    <li class="list-group-item">
                        <b>入驻时间</b> <a class="pull-right">{{ $org_head['create_time'] }}</a>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="col-md-9">
        <div class="nav-tabs-custom" style="">
            <ul class="nav nav-tabs">
                <li class="active"><a href="#activity" data-toggle="tab">商户信息</a></li>
                <li><a href="#org" data-toggle="tab">分校列表</a></li>
            </ul>
            <div class="tab-content">
                <div class="active tab-pane" id="activity">
                    <table class="table table-hover table-bordered table-striped ">
                        <tr>
                            <td>商户名称</td>
                            <td>{{ $org_head['full_name'] }}</td>
                        </tr>
                        <tr>
                            <td>营业执照</td>
                            <td>{{ $org_head['business_license'] }}</td>
                        </tr>
                        <tr>
                            <td>注册地址</td>
                            <td>{{ $org_head['register_address'] }}</td>
                        </tr>
                        <tr>
                            <td>法人代表</td>
                            <td>{{ $org_head['legal_person'] }}</td>
                        </tr>
                        <tr>
                            <td>法人证件号</td>
                            <td>身份证号码-{{ $org_head['legal_person_idcard'] }}</td>
                        </tr>
                        <tr>
                            <td>开户行</td>
                            <td>
                                <img src="{{ \App\Models\Server\BU\BUBanks::getBankLogo($org_head['org_bank_code']) }}"
                                     style="width: 20px">
                                {{ $org_head['org_bank_code'] }} - {{ $org_head['org_bank_name'] }}
                                - {{ $org_head['org_bank_branch'] }}</td>
                        </tr>
                        <tr>
                            <td>账户</td>
                            <td>{{ $org_head['org_bank_account'] }}</td>
                        </tr>
                        <tr>
                            <td>联系人</td>
                            <td>{{ $org_head['contact_name'] }} - {{ $org_head['contact_phone'] }}</td>
                        </tr>
                        <tr>
                            <td>业务类型</td>
                            <td>{{ $org_head['business_type'] }}</td>
                        </tr>
                        <tr>
                            <td>授信额度</td>
                            <td>￥{{ \App\Models\Calc\CalcMoney::fenToYuan($org_head['credit_line']) }}</td>
                        </tr>
                        <tr>
                            <td>保证金比例</td>
                            <td>{{ \App\Models\Calc\CalcMoney::yuanToFen($org_head['security_deposit']) }}%</td>
                        </tr>
                        <tr>
                            <td>对接商务</td>
                            <td>{{ $business[$org_head['docking_business']]['name'] }}</td>
                        </tr>
                        <tr>
                            <td>对接运营</td>
                            <td>{{ $op[$org_head['docking_op']]['name'] }}</td>
                        </tr>
                        <tr>
                            <td>金融产品</td>
                            <td>
                                <table class="table table-bordered table-hover dataTable"
                                       role="grid" aria-describedby="example2_info">
                                    <thead>
                                    <tr role="row">
                                        <td>金融产品ID</td>
                                        <td>金融产品</td>
                                        <td>资金方</td>
                                        <td>期数</td>
                                        <td>还款模式</td>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    @foreach($loanProducts as $loanProduct)
                                        <tr>
                                            <td>{{ $loanProduct['loan_product'] }}</td>
                                            <td>{{ $loanProduct['name'] }}</td>
                                            <td>{{ $loanProduct['resource_company'] }}</td>
                                            <td>{{ $loanProduct['rate_time'] }}</td>
                                            <td>
                                                @if($loanProduct['rate_time_x'] >0)
                                                    {{ $loanProduct['rate_time_x'] }}期
                                                    x {{ $loanProduct['rate_x']*100 }}%
                                                @endif

                                                @if($loanProduct['rate_time_y'] >0)
                                                    {{ $loanProduct['rate_time_y'] }}期
                                                    x {{ $loanProduct['rate_y']*100 }}%
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                    </tbody>
                                </table>
                            </td>
                        </tr>
                    </table>
                </div>
                <div class="tab-pane" id="org">
                    <table class="table table-hover table-bordered table-striped ">
                        <thead>
                        <tr>
                            <td>分校ID</td>
                            <td>分校名称</td>
                            <td>简称</td>
                            <td>状态</td>
                            <td>分期</td>
                            <td>城市</td>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($orgs as $org)
                            <tr>
                                <td>{{ $org['id'] }}</td>
                                <td>{{ $org['org_name'] }}</td>
                                <td>{{ $org['short_name'] }}</td>
                                <td>
                                    @if($org['status']== STATUS_SUCCESS)
                                        可用
                                    @else
                                        不可用
                                    @endif
                                </td>
                                <td>
                                    @if($org['can_loan']== STATUS_SUCCESS)
                                        支持
                                    @else
                                        不支持
                                    @endif
                                </td>
                                <td>{{ $org['org_city'] }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
