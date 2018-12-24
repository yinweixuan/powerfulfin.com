<?php
/**
 * 左侧菜单
 * User: haoxiang
 * Date: 2018/12/24
 * Time: 5:07 PM
 */
?>
<div class="left-side sticky-left-side">
    <!--logo and iconic logo start-->
    <div class="logo">
        <a href="/" style="position: fixed;left: 34px;top: 9px;">
            <img style="height: 25px;" src="{{ admin_asset('web/img/logo.png') }}" alt="">
        </a>
    </div>

    <div class="logo-icon text-center">
        <a href="/" style="position: fixed;left: 10px;top: 9px;">
            <img src="{{ admin_asset('web/img/logo.png') }}" alt="" style="width: 27px;">
        </a>
    </div>
    <!--logo and iconic logo end-->

    <div class="left-side-inner">
        <ul class="nav nav-pills nav-stacked custom-nav">
            <li class="menu-list ">
                <a href="javascript:void(0)">
                    <i class="fa  fa-file-text-o"></i> <span>信息统计</span>
                </a>
                <ul class="sub-menu-list">
                    <li class=""><a href="/organize/sstat/schoolloan">分期汇总</a></li>
                    <li class=""><a href="/organize/sstat/schooldetails">分期明细</a></li>
                </ul>
            </li>
        </ul>
    </div>
</div>
