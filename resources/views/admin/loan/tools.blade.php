<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/14
 * Time: 11:03 AM
 */
?>
<style>
    .nav-stacked > li.active > a, .nav-stacked > li.active > a:hover {
        border-left-color: #dd4b39;
    }

</style>
<div class="row">
    <div class="col-md-3">
        <div class="box box-solid">
            <div class="box-header with-border">
                <h3 class="box-title">订单状态</h3>
            </div>
            <div class="box-body no-padding">
                <ul class="nav nav-pills nav-stacked">
                    <li class="active"><a href="#loan_status" data-toggle="tab">订单状态</a></li>
                    <li><a href="#update_status" data-toggle="tab">退课&提前还款</a></li>
                    <li><a href="#offline_repay" data-toggle="tab">线下还款</a></li>
                    <li><a href="#baofu_repay" data-toggle="tab">划扣触发</a></li>
                    <li><a href="#stop_loan" data-toggle="tab">终止&暂停分期</a></li>
                    <li><a href="#change_phone" data-toggle="tab">修改预留电话</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="col-md-9">
        <div class="tab-content">
            <div class="active tab-pane" id="loan_status">
                <div class="box box-danger">
                    <div class="box-header with-border">
                        <h3 class="box-title">订单状态</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <form class="form-horizontal" method="post" action="/admin/tools/loanstatus">
                        <!-- /.box-header -->
                        <div class="box-body">
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">订单号</label>
                                <div class="col-sm-10">
                                    <input type="number" class="form-control" name="lid" placeholder="订单号">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="inputEmail3" class="col-sm-2 control-label">修正状态</label>
                                <div class="col-sm-10">
                                    <select class="form-control" name="status">
                                        <option value="">请选择状态码</option>
                                        @foreach(\App\Models\Server\BU\BULoanStatus::getStatusDescriptionForAdmin() as $item=>$value)
                                            @if(in_array($item,[LOAN_1200_SURE_FILE,LOAN_2100_SCHOOL_REFUSE,LOAN_3100_PF_REFUSE,LOAN_5100_SCHOOL_REFUSE,LOAN_5200_SCHOOL_STOP,LOAN_5300_SCHOOL_PAUSE]))
                                                <option value="{{ $item }}">{{ $value }}--{{ $item }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="box-footer">
                            <button type="submit" class="btn btn-danger btn-sm">修改</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="tab-pane" id="update_status">
                <div class="box box-primary">
                    <div class="box-header with-border">
                        <h3 class="box-title">退课&提前还款</h3>
                        <div class="box-tools pull-right">
                            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i
                                    class="fa fa-minus"></i>
                            </button>
                        </div>
                    </div>
                    <!-- /.box-header -->
                    <div class="box-body">
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>分期单号:</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-list-ul"></i>
                                        </div>
                                        <input type="text" name="change_list_id" id="change_list_id"
                                               class="input-sm form-control"
                                               placeholder="订单号"
                                               value="<?php echo $_GET['id']; ?>"
                                               onkeyup="this.value=this.value.replace(/\D/g,'')"
                                               onafterpaste="this.value=this.value.replace(/\D/g,'')">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>更新时间:</label>
                                    <div class="easyui-datetimebox" id="changeTime" style="width:100%">
                                        <div class="input-group-addon">
                                            <i class="fa fa-calendar"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>金额:</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-cny (alias)"></i>
                                        </div>
                                        <input type="text" name="change_amount" id="change_amount"
                                               class="input-sm form-control" placeholder="金额" value=""
                                               onkeyup="if(isNaN(value))execCommand('undo')"
                                               onafterpaste="if(isNaN(value))execCommand('undo')">
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>资金来源:</label>
                                    <div class="input-group">
                                        <?php //如果是催收，不展示前两个资金源
                                        if (!$isChase) {
                                        ?>
                                        <input type="radio" id="org" name="money_from" value="1"
                                               checked><span>机构</span>&emsp;&emsp;
                                        <input type="radio" id="stu" name="money_from" value="2"><span>学员</span>&emsp;&emsp;
                                        <?php }?>
                                        <input type="radio" id="chase" name="money_from"
                                               value="3" <?php if ($isChase) {
                                            echo 'checked';
                                        }?>><span>催收</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>备注:</label>
                                    <div class="input-group">
                                        <div class="input-group-addon">
                                            <i class="fa fa-sticky-note-o"></i>
                                        </div>
                                        <input type="text" name="billremark" id="billremark"
                                               class="input-sm form-control" placeholder="备注" value="">
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.row -->
                    </div>
                    <!-- /.box-body -->
                    <div class="box-footer">
                        <input type="button" class="btn btn-primary" value='更改状态为退课'
                               onclick='changeStatus("1")'/>
                        <input type="button" class="btn btn-primary" value='更改状态为提前还款'
                               onclick='changeStatus("2")'/>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
