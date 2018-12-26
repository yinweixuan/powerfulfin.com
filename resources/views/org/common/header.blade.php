<div class="header-section">

    <!--toggle button start-->
    <a class="toggle-btn"><i class="fa fa-bars"></i></a>
    <!--toggle button end-->
    <h1><?php if (isset($errmsg)) echo $errmsg;?></h1>
    <!--notification menu start -->
    <div class="menu-right">
        <ul class="notification-menu">
            <li>
                <a href="#" class="btn btn-default dropdown-toggle" data-toggle="dropdown">
                    <img src="{{ admin_asset('web/img/logo.png') }}" alt="" style="width: 25px;height: 25px"/>
                    <?= $org['short_name']; ?>
                    <span class="caret"></span>
                </a>
                <ul class="dropdown-menu dropdown-menu-usermenu pull-right">
                    <li><a href="/home/logout"><i class="fa fa-sign-out"></i>退出</a></li>
                </ul>
            </li>
        </ul>
    </div>
    <!-- page rolling start -->

</div>
<!-- header section end-->
