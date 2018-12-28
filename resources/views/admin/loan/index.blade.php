<section class="content">
    <!-- SELECT2 EXAMPLE -->
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">搜索</h3>
            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
                </button>
            </div>
        </div>
        <!-- /.box-header -->
        <form role="form" name='form1' id="loginform" action=""
              method="get">
            <input type="hidden" name="page" value="{{ $page }}">
            <div class="box-body">
                <div class="row">
                    <div class="col-md-2">
                        <!-- line 1 -->
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
                        <!-- line 2-->
                        <div class="form-group">
                            <label>晋商借据:</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-sticky-note-o"></i>
                                </div>
                                <input type="text" name="borrow" class="input-sm form-control" placeholder="晋商借据号"
                                       value="<?php kz_e($borrow); ?>">
                            </div>
                        </div>

                    </div>
                    <!-- /.col -->
                    <div class="col-md-2">
                        <!-- line 1 -->
                        <div class="form-group">
                            <label>身&ensp;份&ensp;证:</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-credit-card"></i>
                                </div>
                                <input type="text" name="idcard" class="input-sm form-control" placeholder="身份证号"
                                       value="<?php kz_e($idcard); ?>">
                            </div>
                        </div>
                        <!-- line 2 -->
                        <div class="form-group">
                            <label>放款时间:</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right datepicker" placeholder="开始时间"
                                       name="beginDate" value="<?php echo $_GET['beginDate'] ?>">
                            </div>
                        </div>

                    </div>
                    <!-- /.col -->
                    <div class="col-md-2">
                        <!-- line 1 -->
                        <div class="form-group">
                            <label>资&ensp;金&ensp;方:</label>
                            <select class="form-control select2 select2-hidden-accessible" style="width: 100%;"
                                    tabindex="-1" aria-hidden="true" name="resource">
                                <option value="-1" <?php if ($_GET['resource'] == -1 || !isset($_GET['resource'])) {
                                    echo "selected";
                                } ?>>请选择...
                                </option>
                                <?php if (ARPayLoanType::$resourceCompany) foreach (ARPayLoanType::$resourceCompany as $k => $v) { ?>
                                <option
                                    value="<?php echo $k; ?>" <?php if (isset($_GET['resource']) && $_GET['resource'] == $k) {
                                    echo 'selected';
                                } ?>><?php echo $v; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <!-- line 2 -->
                        <div class="form-group">
                            <label>放款时间:</label>
                            <div class="input-group date">
                                <div class="input-group-addon">
                                    <i class="fa fa-calendar"></i>
                                </div>
                                <input type="text" class="form-control pull-right datepicker" name="endDate"
                                       placeholder="结束时间" value="<?php echo $_GET['endDate'] ?>">
                            </div>
                        </div>

                    </div>
                    <!-- /.col -->
                    <div class="col-md-2">
                        <!-- line 1 -->
                        <div class="form-group">
                            <label>放款类型:</label>
                            <select class="form-control select2 select2-hidden-accessible" style="width: 100%;"
                                    tabindex="-1" aria-hidden="true" name="pay_type">
                                <option value="-1" <?php if ($_GET['pay_type'] == -1 || !isset($_GET['pay_type'])) {
                                    echo "selected";
                                } ?>>请选择...
                                </option>
                                <?php if (ARPayLoanType::$payType) foreach (ARPayLoanType::$payType as $k => $v) { ?>
                                <option
                                    value="<?php echo $k; ?>" <?php if (isset($_GET['pay_type']) && $_GET['pay_type'] == $k) {
                                    echo 'selected';
                                } ?>><?php echo $v; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <!-- line 2 -->
                        <div class="form-group ">
                            <label>学&emsp;&emsp;校:</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-university"></i>
                                </div>
                                <input type="text" name="schname" class="input-sm form-control" placeholder="学校名字"
                                       value="<?php kz_e($schname); ?>">
                            </div>
                        </div>

                        <!-- line 3 -->
                        <div class="form-group ">
                            <label>总&emsp;&emsp;校:</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-university"></i>
                                </div>
                                <input type="number" name="sbid" class="input-sm form-control" placeholder="学校名字"
                                       value="<?php kz_e($sbid); ?>">
                            </div>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-md-2">
                        <!-- line 1 -->
                        <div class="form-group">
                            <label>手&ensp;机&ensp;号:</label>
                            <div class="input-group">
                                <div class="input-group-addon">
                                    <i class="fa fa-phone"></i>
                                </div>
                                <input type="text" class="input-sm form-control" name="phone" placeholder="手机号"
                                       value="<?php kz_e($phone); ?>">
                            </div>
                        </div>
                        <!-- line 2 -->
                        <div class="form-group">
                            <label>分期状态:</label>
                            <select class="form-control select2 select2-hidden-accessible" style="width: 100%;"
                                    tabindex="-1" aria-hidden="true" id="select_status" name="status">
                                <option value="">请选择...</option>
                                <option
                                    value="<?php echo LOAN_STAT_NO_1001 ?>" <?php if ($status == LOAN_STAT_NO_1001) echo 'selected' ?>><?php echo '开始上课,等待放款' ?></option>
                                <?php
                                global $gLoanStatus;
                                //有些状态要一起统计的
                                $combineStatusArr = array(LOAN_0_NO, LOAN_10_CREATE, LOAN_50_SCHOOL_BEGIN, LOAN_DATA_60_NOTICE_MONEY, LOAN_DATA_65_NOTICE_FINISH, LOAN_DATA_68_GET_MONEY, LOAN_DATA_70_GET_MONEY_RESULT,);
                                foreach ($gLoanStatus as $k => $v) {
                                if (in_array($k, $combineStatusArr)) {
                                    continue;
                                }
                                ?>
                                <option
                                    value="<?php echo $k ?>" <?php if ($status == $k) echo 'selected' ?>><?php echo $v['name'] ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <!-- line 3 -->
                    </div>
                    <!-- /.col -->
                    <div class="col-md-2">
                        <!-- line 1 -->
                        <div class="form-group">
                            <label>银行列表:</label>
                            <select class="form-control select2 select2-hidden-accessible" style="width: 100%;"
                                    tabindex="-1" aria-hidden="true" name="bank_id">
                                <option value="0">请选择...</option>
                                <?php foreach (BUBanks::getBanksInfo() as $k => $v) { ?>
                                <option
                                    value="<?php echo $k; ?>" <?php if (isset($_GET['bank_id']) && $_GET['bank_id'] == $k) {
                                    echo "selected";
                                } ?>><?php echo $v['bankname']; ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <!-- line 2 -->
                        <div class="form-group">
                            <label>分期类型:</label>
                            <select class="form-control select2 select2-hidden-accessible" style="width: 100%;"
                                    tabindex="-1" aria-hidden="true" name="rate_type">
                                <option value="">请选择...</option>
                                <option value="1" <?php if (isset($_GET['rate_type']) && $_GET['rate_type'] == 1) {
                                    echo "selected";
                                } ?>>
                                    弹性分期
                                </option>
                                <option value="2" <?php if (isset($_GET['rate_type']) && $_GET['rate_type'] == 2) {
                                    echo "selected";
                                } ?>>
                                    贴息分期
                                </option>
                                <option value="3" <?php if (isset($_GET['rate_type']) && $_GET['rate_type'] == 3) {
                                    echo "selected";
                                } ?>>
                                    等额分期
                                </option>

                            </select>
                        </div>
                        <!-- line 3 -->
                    </div>

                </div>
                <!-- /.row -->
            </div>
            <!-- /.box-body -->
            <div class="box-footer">
                <input type="button" class="btn btn-primary" id="log_btn"
                       onclick="document.getElementsByName('excel')[0].value=0;document.form1.submit();" value='查询'/>
                <input type='button' class="btn btn-primary"
                       onclick="document.getElementsByName('excel')[0].value=1;document.form1.submit();" value='导出'>
                <button class="btn btn-primary"><a style="color:white;" href="/admin/astat/calculator" target="_blank">计算器</a>
                </button>
            </div>
        </form>
    </div>
    <div class="row">
        <div class="col-xs-12">
            <div class="box">
                <div class="box-header">
                    <div class="col-sm-4 text-right">
                        <label class="col-md-4">总数:</label>
                        <div class="col-md-3">
                            <span class="label label-info"><?php echo $result['total']['count']; ?></span>
                        </div>
                    </div>
                    <div class="col-sm-4 text-right">
                        <label class="col-md-4">学员申请:</label>
                        <div class="col-md-4">
                            <span
                                class="label label-info"><?php echo KZOutput::echoMoney($result['total']['money_apply']); ?></span>
                        </div>
                    </div>
                    <div class="col-sm-4 text-right">
                        <label class="col-md-4">机构应收:</label>
                        <div class="col-md-4">
                            <span
                                class="label label-info"><?php echo KZOutput::echoMoney($result['total']['money_school']); ?></span>
                        </div>
                    </div>
                </div>
                <!-- /.box-header -->
                <div class="box-body">
                    <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                        <div class="row">
                            <div class="col-sm-6"></div>
                            <div class="col-sm-6"></div>
                        </div>
                        <div class="row">
                            <div class="col-sm-12" style="overflow-x: auto;">
                                <table id="example2" class="table table-bordered table-hover" role="grid"
                                       aria-describedby="example2_info">
                                    <thead>
                                    <tr role="row">
                                        <th>单号</th>
                                        <th>资金渠道</th>
                                        <th>学校</th>
                                        <th>课程</th>
                                        <th>学员</th>
                                        <th>申请<br>时间</th>
                                        <th>放款<br>时间</th>
                                        <th>还款<br>方式</th>
                                        <th>学员<br>申请</th>
                                        <th>机构<br>收款</th>
                                        <th>状态</th>
                                        <th>还款</th>
                                        <th>操作</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    foreach ($result['result'] as $item) { ?>
                                    <tr>
                                        <td <?php if ($item['resource'] == 5 || $item['resource'] == 8) {
                                            echo 'style="width: 90px;"';
                                        } ?>>
                                            <?php echo $item['lid']; ?><?php if ($item['resource'] == 3) {
                                                echo "<br>" . $item['applseq'] . "<br>" . $item['loan_order'];
                                            } elseif ($item['resource'] == 5 || $item['resource'] == 8) {
                                                echo "<br>" . $item['fcs_loanid'];
                                            } ?></td>
                                        <td><?php echo ARPayLoanType::$resourceCompany[$item['resource']]; ?>
                                            <br><?php echo ARPayLoanType::$payType[$item['pay_type']]; ?></td>
                                        <td>
                                            <a href="<?php echo UrlUtil::get('school', $item['sid'], 'http://bj.kezhanwang.cn'); ?>"
                                               target="_blank"
                                               title="<?php kz_e($item['school_name']); ?>"><?php kz_e($item['school_name'], 2); ?></a>
                                        </td>
                                        <td>
                                            <a href="<?php echo UrlUtil::get('course', $item['cid'], 'http://bj.kezhanwang.cn'); ?>"
                                               target="_blank"
                                               title="<?php kz_e($item['course_name']); ?>"><?php kz_e($item['course_name'], 2); ?></a>
                                        </td>
                                        <td><?php kz_e($item['idcard_name']); ?><br>
                                            <?php kz_e($item['idcard']); ?><br>
                                            <?php if ($item['is_hide'] == 2) { ?>
                                                    <?php echo "<br />({$item['uid']}&nbsp;&nbsp;)"; ?>
                                                <?php } else { ?>
                                            <a href="<?php echo UrlUtil::get('astat_statloandetails', $item['uid']) . '&query=2' ?>"
                                               target="_blank"><?php echo $item['uid']; ?></a><br>
                                            <?php } ?>
                                            <img src="<?php echo BUBanks::getBankLogo($item['bank_id'])?>"
                                                 style="width:16px;"><?php echo BUBanks::getBankName($item['bank_id']); ?>
                                        </td>
                                        <td><?php if (strpos($item['ctime'], '0000') !== 0) echo $item['ctime']; ?></td>
                                        <td><?php if (strpos($item['pay_time'], '0000') !== 0) {
                                                echo $item['pay_time'];
                                            } ?></td>
                                        <td><?php echo $item['loan_type_desp']; ?></td>
                                        <td>￥<?php echo KZOutput::echoMoney($item['money_apply']) ?></td>
                                        <td>￥<?php echo KZOutput::echoMoney($item['money_school']) ?></td>
                                        <td><?php kz_e("({$item['status']}){$item['status_desp']}"); ?></td>
                                        <td><?php if ($item['last_update_repay'] > 0) { ?>
                                            <span
                                                class="label label-info"><?php kz_e($item['last_update_repay']); ?></span>
                                            <?php } ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo UrlUtil::get('astat_loandetail', $item['lid']) . '&view=1&detail=1' ?>"
                                               target="_blank" class="btn btn-xs btn-success btn-noborder">详情</a>
                                            <?php if ($item['status'] == LOAN_100_REPAY || $item['status'] == LOAN_110_FINISH || $item['status'] == LOAN_111_OVERDUE_KZ || $item['status'] == LOAN_120_DROP || $item['status'] == LOAN_130_EARLY_FINISH) { ?>
                                            <a href=" <?php echo UrlUtil::get('astat_loanbill', $item['lid']) . '&query=1' ?>"
                                               target="_blank"
                                               class="btn btn-xs btn-success btn-noborder">还款计划</a>
                                            <?php } ?>
                                            <?php if ($item['status'] == LOAN_100_REPAY || $item['status'] == LOAN_110_FINISH || $item['status'] == LOAN_111_OVERDUE_KZ || $item['status'] == LOAN_120_DROP || $item['status'] == LOAN_130_EARLY_FINISH || $item['status'] == LOAN_DATA_60_NOTICE_MONEY) { ?>
                                            <a href="/admin/astat/contract?lid=<?php echo $item['id']; ?>"
                                               target="_blank"
                                               class="btn btn-xs btn-success btn-noborder">合同下载</a>
                                            <?php } ?>
                                            <?php if (in_array($item['status'], [LOAN_10_CREATE, LOAN_DATA_11_CREATE_ACCOUNT, LOAN_31_KZ_REFUSE, LOAN_20_SCHOOL_CONFIRM, LOAN_21_SCHOOL_REFUSE, LOAN_41_P2P_REFUSE, LOAN_34_KZ_CREDIT_SEND_INFO, LOAN_35_KZ_CREDIT_LOAN_APPLY, LOAN_51_SCHOOL_REFUSE, LOAN_52_SCHOOL_STOP, LOAN_53_SCHOOL_PAUSE, LOAN_101_REFUSE, LOAN_140_FOREVER_REFUSE])) { ?>
                                            <a href="/admin/astat/calcresource?id=<?php echo $item['lid']; ?>"
                                               class="btn btn-xs btn-danger btn-noborder">转资金方</a>
                                            <?php } ?>
                                            <a href="/admin/astat/customernotice?phone=<?php echo $item['phone']; ?>&lid=<?php echo $item['lid']; ?>"
                                               class="btn btn-xs btn-success btn-noborder">发送短信</a>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                    </tbody>
                                    <tfoot>
                                    <tr role="row">
                                        <th rowspan="1" colspan="1">单号</th>
                                        <th rowspan="1" colspan="1">资金渠道</th>
                                        <th rowspan="1" colspan="1">学校</th>
                                        <th rowspan="1" colspan="1">顾问</th>
                                        <th rowspan="1" colspan="1">学员</th>
                                        <th rowspan="1" colspan="1">申请时间</th>
                                        <th rowspan="1" colspan="1">放款时间</th>
                                        <th rowspan="1" colspan="1">还款方式</th>
                                        <th rowspan="1" colspan="1">学员申请</th>
                                        <th rowspan="1" colspan="1">机构收款</th>
                                        <th rowspan="1" colspan="1">状态</th>
                                        <th rowspan="1" colspan="1">还款</th>
                                        <th rowspan="1" colspan="1">操作</th>
                                    </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-sm-7">
                                <div class="dataTables_paginate paging_simple_numbers" id="example2_paginate">
                                    <ul class="pagination">
                                        <?php
                                        if (isset($result['total']) && $result['total']) {
                                            $allPage = ceil(($result['total']['count'] + 1) / $ps);
                                        }
                                        ?>
                                        <li class="paginate_button previous"><a data-action="first" onclick="goUrl(1)">首页</a>
                                        </li>
                                        <li class="previous"><a data-action="previous"
                                                                <?php if ($p > 1){ ?>onclick="goUrl(<?php echo $p - 1; ?>)" <?php } ?>>上一页</a>
                                        </li>
                                        <li class="paginate_button next"><a data-action="next"
                                                                            <?php if (!empty($result)){ ?>onclick="goUrl(<?php echo($p + 1); ?>)" <?php } ?>>下一页</a>
                                        </li>
                                        <li class="last"><a data-action="last"
                                                            onclick="goUrl(<?php if (isset($allPage)) {
                                                                echo $allPage;
                                                            } ?>)">尾页</a></li>
                                        <li class=""><a>(共<?php echo $result['total']['count'] ?>条<?php echo $allPage ?>
                                                页)</a></li>
                                        <li class="">
                                            <input type="text" value="<?php echo $p ?>" id="go_page"
                                                   class="form-control"
                                                   style="width: 12%;display: inline;">/<?php echo $allPage; ?>
                                            <input type="button" class="btn btn-success"
                                                   onclick="goUrl($('#go_page').val())" value="确定">
                                        </li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.box-body -->
            </div>
            <!-- /.box -->
        </div>
        <!-- /.col -->
    </div>
</section>

