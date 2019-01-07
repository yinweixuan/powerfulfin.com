@extends('org.common.base')
@section('title',  '资金方信息')
@section('content')
<?php
/**
 * 常见问题
 * User: haoxiang
 * Date: 2018/12/24
 * Time: 3:42 PM
 */
use App\Components\OutputUtil;
use App\Models\Server\BU\BULoanProduct;
?>
<!--body wrapper start-->
<div class="wrapper">
    <div class="row blog">
        <div class="col-md-12">
            <section class="panel">
                <header class="panel-heading custom-tab dark-tab">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#<?php echo RESOURCE_FCS_SC; ?>"
                               data-toggle="tab"><?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_FCS_SC, true)); ?></a>
                        </li>
                        <li class="">
                            <a href="#<?php echo RESOURCE_FCS; ?>"
                               data-toggle="tab"><?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_FCS, true)); ?></a>
                        </li>
                        <li class="">
                            <a href="#<?php echo RESOURCE_JCFC; ?>"
                               data-toggle="tab"><?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_JCFC, true)); ?></a>
                        </li>
                        <li class="">
                            <a href="#<?php echo RESOURCE_ZD; ?>"
                               data-toggle="tab"><?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_ZD, true)); ?></a>
                        </li>
                    </ul>
                </header>
                <div class="panel-body">
                    <div class="tab-content">
                        <div class="tab-pane active" id="<?php echo RESOURCE_FCS_SC ?>">
                            <div class="panel">
                                <div class="panel-body">
                                    <h1 class="text-center mtop35">
                                        <a href="#"><?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_FCS_SC, true)) ?></a>
                                    </h1>
                                    <p>
                                        富登分为两个资金方。<?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_FCS_SC, true)) ?>
                                        和<?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_FCS, true)) ?>
                                        。请机构老师在进行退课、提前还款操作时，一定核对好账户。若出现打错账户的情况，富登会要求重新补充打款的。并且，富登告知，若出现划扣问题，是不允许线下还款。所以每月划扣时，若机构老师方便，一定告知学员存好钱，不要取走。</p>
                                    <p>
                                        资金方【<?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_FCS_SC, true)) ?>
                                        】退课、提前还款等业务银行账户信息：</p>
                                    <blockquote style="color: #000000;font-style: normal">
                                        <strong>
                                            <ul>
                                                <li>户&emsp;&emsp;名：富登小额贷款(四川)有限公司</li>
                                                <li>账&emsp;&emsp;户：5100 1436 3370 5150 1664</li>
                                                <li>开&ensp;户&ensp;行：中国建设银行股份有限公司成都南虹支行</li>
                                                <br/>
                                                <li>括号为英文状态下输入。</li>
                                                <li>打款时候一定要备注：“姓名+课栈还款“</li>
                                            </ul>
                                        </strong>
                                    </blockquote>
                                    <p>友情提示：富登不允许线下打款。</p>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="<?php echo RESOURCE_FCS ?>">
                            <div class="panel">
                                <div class="panel-body">
                                    <h1 class="text-center mtop35">
                                        <a href="#"><?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_FCS, true)) ?></a>
                                    </h1>
                                    <p>
                                        富登分为两个资金方。<?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_FCS, true)) ?>
                                        和<?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_FCS_SC, true)) ?>
                                        。请机构老师在进行退课、提前还款操作时，一定核对好账户。若出现打错账户的情况，富登会要求重新补充打款的。并且，富登告知，若出现划扣问题，是不允许线下还款。所以每月划扣时，若机构老师方便，一定告知学员存好钱，不要取走。</p>
                                    <p>资金方【<?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_FCS, true)) ?>
                                        】退课、提前还款等业务银行账户信息：</p>
                                    <blockquote style="color: #000000;font-style: normal">
                                        <strong>
                                            <ul>
                                                <li>户&emsp;&emsp;名：富登小额贷款（重庆）有限公司</li>
                                                <li>账&emsp;&emsp;户：5000 1090 0410 5988 8888</li>
                                                <li>开&ensp;户&ensp;行：中国建设银行股份有限公司重庆北碚城南支行</li>
                                                <br/>
                                                <li>括号中文状态下输入。</li>
                                                <li>打款时候一定要备注：“姓名+课栈还款“</li>
                                            </ul>
                                        </strong>
                                    </blockquote>
                                    <p>友情提示：富登不允许线下打款。</p>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="<?php echo RESOURCE_JCFC ?>">
                            <div class="panel">
                                <div class="panel-body">
                                    <h1 class="text-center mtop35">
                                        <a href="#"><?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_JCFC, true)) ?></a>
                                    </h1>
                                    <p>该资金方不允许学员每期进行线下还款！若有学员退课、提前还款，有以下两种处理方式:</p>
                                    <p style="text-indent: 2em">
                                        1、机构老师联系课栈对接运营，提供学员信息，课栈对接运营会联系晋商核算当日金额，并将核算结果及打款截止时间，告知机构老师。</p>
                                    <p style="text-indent: 2em">
                                        2、学员可自行联系晋商客服热线，若遇到了划扣问题，如有钱未划扣未划扣造成逾期，也需学员自行联系晋商，晋商客为【400-168-5858】。</p>
                                    <p>资金方【<?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_JCFC, true)) ?>
                                        】退课、提前还款等业务银行账户信息：</p>
                                    <blockquote style="color: #000000;font-style: normal">
                                        <strong>
                                            <ul>
                                                <li>户&emsp;&emsp;名：晋商消费金融股份有限公司</li>
                                                <li>账&emsp;&emsp;户：35111380500000037</li>
                                                <li>开&ensp;户&ensp;行：晋商银行太原南内环街支行</li>
                                            </ul>
                                        </strong>
                                    </blockquote>
                                    <p>友情提示：晋商不允许线下打款，若有学员想自行打款，请告知学员拨打晋商客服热线 【400-168-5858】</p>
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane" id="<?php echo RESOURCE_ZD ?>">
                            <div class="panel">
                                <div class="panel-body">
                                    <h1 class="text-center mtop35">
                                        <a href="#"><?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_ZD, true)) ?></a>
                                    </h1>
                                    <p>
                                        KZ直贷模式，若有学员退课、提前还款、线下还款，均可将款项打至下方账户。打款以后。请机构老师及时将凭证反馈至对接运营，学员及时将凭证反馈至金融服务邮箱jinrongfuwu@kezhanwang.cn。</p>
                                    <p>
                                        通过支付宝打款牛蕊账户时，可能会遇到中断的情况。若出现，请告知学员，转账方式不是只有支付宝一种，网银，手机银行，微信转他人（百度有教程），atm，银行柜台都可以，具体如何操作可以让学员拨打所使用银行卡客服热线咨询</p>
                                    <p>资金方【<?php OutputUtil::echoEscape(BULoanProduct::getResourceCompany(RESOURCE_ZD, true)) ?>
                                        】退课、提前还款、线下还款等业务银行账户信息。</p>
                                    <blockquote style="color: #000000;font-style: normal">
                                        <strong>
                                            <!--<p>对公：</p>
                                            <ul>
                                                <li>户&emsp;&emsp;名：北京弟傲思时代信息技术有限公司</li>
                                                <li>账&emsp;&emsp;户：35310188000088948</li>
                                                <li>开&ensp;户&ensp;行：中国光大银行股份有限公司北京京广桥支行</li>
                                            </ul>-->
                                            <p>对私：</p>
                                            <ul>
                                                <li>户&emsp;&emsp;名：牛蕊</li>
                                                <li>账&emsp;&emsp;户：6226220130127888</li>
                                                <li>开&ensp;户&ensp;行：民生银行北京电子城支行</li>
                                            </ul>
                                        </strong>
                                    </blockquote>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </section>

        </div>
    </div>
</div>
@endsection
