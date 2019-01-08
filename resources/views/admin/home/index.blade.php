<link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/Ionicons/css/ionicons.min.css") }}">
<!-- jvectormap -->
<link rel="stylesheet" href="bower_components/jvectormap/jquery-jvectormap.css">
<div class="row">
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-aqua"><i class="ion ion-ios-people-outline"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">新增用户</span>
                <span class="info-box-number">{{ $users }}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-red"><i class="ion ion-ios-cart-outline"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">新增订单</span>
                <span class="info-box-number">{{ $loans }}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->

    <!-- fix for small devices only -->
    <div class="clearfix visible-sm-block"></div>

    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-green"><i class="ion ion-ios-list-outline"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">今日放款单数</span>
                <span class="info-box-number">{{ $loanTimes }}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
    <div class="col-md-3 col-sm-6 col-xs-12">
        <div class="info-box">
            <span class="info-box-icon bg-yellow"><i class="ion ion-ios-infinite-outline"></i></span>

            <div class="info-box-content">
                <span class="info-box-text">今日放款总额</span>
                <span class="info-box-number">￥{{ $borrowMoney }}</span>
            </div>
            <!-- /.info-box-content -->
        </div>
        <!-- /.info-box -->
    </div>
    <!-- /.col -->
</div>
<!-- /.row -->


<!-- /.row -->
<div class="row">
    <!-- Left col -->
    <div class="col-md-12">
        <!-- MAP & BOX PANE -->
        <div class="box box-danger">
            <div class="box-header with-border">
                <h3 class="box-title">访问统计</h3>
            </div>
            <!-- /.box-header -->
            <div class="box-body no-padding">
                <div class="row">
                    <div class="col-md-9 col-sm-8">
                        <div class="pad">
                            <!-- Map will be created here -->
                            <div id="world-map-markers" style="height: 325px;"></div>
                        </div>
                    </div>
                    <!-- /.col -->
                    <div class="col-md-3 col-sm-4">
                        <div class="pad box-pane-right bg-green" style="min-height: 280px">
                            <div class="description-block margin-bottom">
                                <div class="sparkbar pad" data-color="#fff">90,70,90,70,75,80,70</div>
                                <h5 class="description-header">8390</h5>
                                <span class="description-text">Visits</span>
                            </div>
                            <!-- /.description-block -->
                            <div class="description-block margin-bottom">
                                <div class="sparkbar pad" data-color="#fff">90,50,90,70,61,83,63</div>
                                <h5 class="description-header">30%</h5>
                                <span class="description-text">Referrals</span>
                            </div>
                            <!-- /.description-block -->
                            <div class="description-block">
                                <div class="sparkbar pad" data-color="#fff">90,50,90,70,61,83,63</div>
                                <h5 class="description-header">70%</h5>
                                <span class="description-text">Organic</span>
                            </div>
                            <!-- /.description-block -->
                        </div>
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row -->
            </div>
            <!-- /.box-body -->
        </div>
        <!-- /.box -->
    </div>
    <!-- /.col -->
</div>
