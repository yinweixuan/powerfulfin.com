<div class="header-section">

    <!--toggle button start-->
    <a class="toggle-btn"><i class="fa fa-bars"></i></a>
    <!--toggle button end-->
    <label><?php if (isset($errmsg)) echo $errmsg;?></label>
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
                    <li>您的专属商务:<?php \App\Components\OutputUtil::echoEscape($org_business['name']);?></li>
                    <li>您的专属运营:<?php \App\Components\OutputUtil::echoEscape($org_op['name']);?></li>
                    <li><a href="/home/logout"><i class="fa fa-sign-out"></i>退出</a></li>
                </ul>
            </li>
        </ul>
    </div>
    <!-- page rolling start -->
</div>
<!-- header section end-->
