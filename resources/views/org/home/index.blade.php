@extends('org.common.base')
@section('title',  '首页')
@section('content')
    <?php
    /**
     * 首页展示
     * User: haoxiang
     * Date: 2018/12/24
     * Time: 3:42 PM
     */
    use App\Models\Server\BU\BULoanStatus;
    ?>
    <div class="container">
        <div class="page-header text-center">
            <small>欢迎使用大圣分期机构管理后台</small>
        </div>
        <div class="row">
            <div class="col-md-4 col-lg-6 col-sm-4">
                <div class="row"><h4>今日统计({{$today}})</h4></div>
                <div class="row">
                    <div class="info-box">
                        <span class="info-box-icon bg-green-active"><i class="fa fa-diamond"></i></span>
                        <div class="info-box-content">
                            <h5>放款金额</h5>
                            <span class="info-box-number"><a href="/stat/list?query=1&status=10000&begin_time={{$today}}&end_time={{$today}}">￥<?php echo \App\Components\OutputUtil::echoMoney($tongji['m_repay']);?></a></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="info-box">
                        <span class="info-box-icon bg-green"><i class="fa fa-user"></i></span>
                        <div class="info-box-content">
                            <h5>放款单数</h5>
                            <span class="info-box-number"><a href="/stat/list?query=1&status=10000&begin_time={{$today}}&end_time={{$today}}">{{$tongji['c_repay']}}</a></span>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="info-box">
                        <span class="info-box-icon bg-gray-light"><i class="fa fa-users"></i></span>
                        <div class="info-box-content">
                            <h5>申请单数</h5>
                            <span class="info-box-number"><a href="/stat/list?query=1&begin_time={{$today}}&end_time={{$today}}">{{$tongji['c_total']}}</a></span>
                        </div>
                    </div>
                </div>
            </div>
            <?php if (isset($todo_list) && $todo_list) {?>
            <div class="col-md-4 col-lg-6 col-sm-4">
                <p><h4>当前任务</h4></p>
                <table class="table table-striped table-hover">
                    <?php
                    foreach ($todo_list as $todo) {
                        if ($todo['status'] == LOAN_1100_CREATE_ACCOUNT) {
                            echo "<tr class=''><td>{$todo['full_name']}正在" . BULoanStatus::getStatusDescriptionForB($todo['status']) . "</td><td><a href='/order/bookinglist?query=1&lid={$todo['lid']}' target='_blank'>去处理>></a></td></tr>";
                        } else {
                            echo "<tr class='warning'><td>{$todo['full_name']}正在" . BULoanStatus::getStatusDescriptionForB($todo['status']) . "</td><td><a href='/order/confirmlist?query=1&lid={$todo['lid']}' target='_blank'>去处理>></a></td></tr>";
                        }
                    }
                    ?>
                </table>
            </div>
            <?php } else {?>
            <div class="col-md-4 col-lg-4 col-sm-4">
                <p><h4>待办任务都被您做完啦!</h4></p>
            </div>
            <?php }?>
        </div>
    </div>
@endsection
