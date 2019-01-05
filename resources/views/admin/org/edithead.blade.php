<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/1/5
 * Time: 6:52 PM
 */
?>
<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">新增</h3>
    </div>
    <form class="form-horizontal" role="form" action="" method="post">
        <div class="box-body">
            <div class="form-group">
                <label class="col-sm-2 control-label">商户ID</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" value="{{ $org_head['hid'] }}" name="hid" readonly>
                    <input type="hidden" class="form-control" value="updatehead" name="type" readonly>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">商户名称</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" value="{{ $org_head['full_name'] }}" name="full_name">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">营业执照号</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" value="{{ $org_head['business_license'] }}"
                           name="business_license">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">注册地址</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" placeholder="请填写注册地址"
                           value="{{ $org_head['register_address'] }}" name="register_address">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">法人代表</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" placeholder="请填写法人代表"
                           value="{{ $org_head['legal_person'] }}" name="legal_person">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">法人证件号</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" placeholder="请填写法人代表证件号码"
                           value="{{ $org_head['legal_person_idcard'] }}"
                           name="legal_person_idcard">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">开户银行</label>
                <div class="col-sm-9">
                    <select name="org_bank_code" style="width: 100%">
                        <option value="">请选择开户银行</option>
                        @foreach(\App\Models\Server\BU\BUBanks::getBanksInfo() as $item)
                            <option value="{{ $item['bank_code'] }}"
                                    @if($org_head['org_bank_code'] == $item['bank_code']) selected @endif>{{ $item['bankname'] }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">开户行</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" placeholder="请填写开户行详细信息，同《开户许可证》"
                           value="{{ $org_head['org_bank_branch'] }}"
                           name="org_bank_branch">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">账户</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" placeholder="请填写账户号码"
                           value="{{ $org_head['org_bank_account'] }}" name="org_bank_account">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">联系人</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" placeholder="请填写联系人姓名"
                           value="{{ $org_head['contact_name'] }}" name="contact_name">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">联系电话</label>
                <div class="col-sm-9">
                    <input type="tel" class="form-control" placeholder="请填写企业联系人的联系电话"
                           value="{{ $org_head['contact_phone'] }}"
                           name="contact_phone">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">业务类型</label>
                <div class="col-sm-9">
                    <select name="business_type" style="width: 100%">
                        <option value="">请选择</option>
                        <option value="IT" @if($org_head['business_type'] == "IT") selected @endif>IT</option>
                        <option value="语言" @if($org_head['business_type'] == "语言") selected @endif>语言</option>
                        <option value="财会" @if($org_head['business_type'] == "财会") selected @endif>财会</option>
                        <option value="学历" @if($org_head['business_type'] == "学历") selected @endif>学历</option>
                        <option value="驾校" @if($org_head['business_type'] == "驾校") selected @endif>驾校</option>
                        <option value="健身" @if($org_head['business_type'] == "健身") selected @endif>健身</option>
                        <option value="K12" @if($org_head['business_type'] == "K12") selected @endif>K12</option>
                        <option value="其他" @if($org_head['business_type'] == "其他") selected @endif>其他</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">授信额度</label>
                <div class="col-sm-9">
                    <input type="number" class="form-control" placeholder="请填写授信额度，单位：元"
                           value="{{ \App\Models\Calc\CalcMoney::fenToYuan($org_head['credit_line']) }}"
                           name="credit_line">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">保证金</label>
                <div class="col-sm-9 input-group">
                    <input type="text" class="form-control" placeholder="请填写保证金比例"
                           value="{{ \App\Models\Calc\CalcMoney::yuanToFen($org_head['security_deposit']) }}"
                           name="security_deposit">
                    <span class="input-group-addon">%</span>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">金融产品</label>
                <div class="col-sm-9">
                    <table id="example2" class="table table-bordered table-hover dataTable"
                           role="grid" aria-describedby="example2_info">
                        <thead>
                        <tr role="row">
                            <td>选择</td>
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
                                <td>
                                    <label>
                                        <input type="checkbox" class="minimal-red" name="loan_product[]"
                                               value="{{ $loanProduct['loan_product'] }}" @if(in_array($loanProduct['loan_product'],$org_head['loan_product'])) checked @endif>
                                    </label>
                                </td>
                                <td>{{ $loanProduct['loan_product'] }}</td>
                                <td>{{ $loanProduct['name'] }}</td>
                                <td>{{ $loanProduct['resource_company'] }}</td>
                                <td>{{ $loanProduct['rate_time'] }}</td>
                                <td>
                                    @if($loanProduct['rate_time_x'] >0)
                                        {{ $loanProduct['rate_time_x'] }}期 x {{ $loanProduct['rate_x']*100 }}%
                                    @endif

                                    @if($loanProduct['rate_time_y'] >0)
                                        {{ $loanProduct['rate_time_y'] }}期 x {{ $loanProduct['rate_y']*100 }}%
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="box-footer">
            <a href="/admin/org/head" class="btn btn-sm btn-default">取消</a>
            <button type="submit" class="btn btn-sm btn-danger pull-right">提交</button>
        </div>
    </form>
</div>
