@extends('org.common.base')
@section('title',  '常见问题')
@section('content')
<?php
/**
 * 常见问题
 * User: haoxiang
 * Date: 2018/12/24
 * Time: 3:42 PM
 */
?>
<!--body wrapper start-->
<div class="wrapper">
    <div class="row">
        <div class="col-md-12">
            <!--collapse start-->
            <div class="panel-group">
                <div class="panel">
                    <div class="panel-heading dark">
                        <h4 class="panel-title">
                            <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion2"
                               href="#question1">
                                办理分期以后如何还款？
                            </a>
                        </h4>
                    </div>
                    <div id="question1" class="panel-collapse collapse" style="height: 0px;">
                        <div class="panel-body">
                            <p style="color: #FF0000"><strong>1、下载最新大圣分期APP，添加自主还款卡，进行主动还款。（推荐方式）</strong></p>
                            <p style="text-indent: 2em">首页=》立即还款,或 订单=》订单详情=》还款计划表=》立即还款</p>
                            <p style="text-indent: 2em">目前可使用的银行有：<?php
                                $banks = \App\Models\Server\BU\BUBanks::getBanksInfo();
                                foreach ($banks as $b) {
                                    echo $b['bankname'] . "、";
                                }
                                ?></p>
                            <!--<div id="gallery" class="media-gal isotope"
                                 style="position: relative; overflow: hidden; height: 250px;">
                                <div class="images item  isotope-item"
                                     style="position: absolute; left: 0px; top: 0px; transform: translate3d(0px, 0px, 0px);"
                                     onclick="openImg('/others/3c367a60189a50d83f8fd72e34756021.jpeg',1)">
                                    <a href="#myModal" data-toggle="modal">
                                        <img src="/others/3c367a60189a50d83f8fd72e34756021.jpeg">
                                    </a>
                                    <p>支付流程</p>
                                </div>
                                <div class="audio item  isotope-item"
                                     style="position: absolute; left: 0px; top: 0px; transform: translate3d(243px, 0px, 0px);"
                                     onclick="openImg('/others/7b3f389b34d5279cd947e47f1483565e.jpeg',2)">
                                    <a href="#myModal" data-toggle="modal">
                                        <img src="/others/7b3f389b34d5279cd947e47f1483565e.jpeg">
                                    </a>
                                    <p>绑卡流程</p>
                                </div>
                            </div>-->
                            <p>2、通过申请分期时绑定的主动划扣卡，等银行系统划扣。</p>
                        </div>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-heading dark">
                        <h4 class="panel-title">
                            <a class="accordion-toggle collapsed" data-toggle="collapse" data-parent="#accordion2"
                               href="#question2">
                                手机号变更，且旧手机号不在使用，怎么办？
                            </a>
                        </h4>
                    </div>
                    <div id="question2" class="panel-collapse collapse" style="height: 0px;">
                        <div class="panel-body">
                            <p> 1、学员提供具体信息，由机构老师发送至对接运营修改。</p>
                            <p style="text-indent: 2em"> 主题【XX修改手机号】</p>
                            <p style="text-indent: 2em"> 正文【姓名、身份证号、新旧手机号、修改原因】</p>
                            <p> 2、学员自行发邮件至jinrongfuwu@kezhanwang.cn</p>
                            <p style="text-indent: 2em"> 主题【XX修改手机号】</p>
                            <p style="text-indent: 2em">正文【姓名、身份证号、新旧手机号、修改原因】</p>
                        </div>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-heading dark">
                        <h4 class="panel-title">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                               href="#question3">
                                银行卡如何修改？
                            </a>
                        </h4>
                    </div>
                    <div id="question3" class="panel-collapse collapse" style="height: auto;">
                        <div class="panel-body">
                            <p>1、学员提供具体信息，由机构老师发送至对接运营修改。</p>
                            <p style="text-indent: 2em">主题，【XX修改银行卡】</p>
                            <p style="text-indent: 2em">正文，【姓名、身份证号、手机号、新旧银行卡号、新银行卡照片】</p>
                            <p>2、学员自行发邮件至jinrongfuwu@kezhanwang.cn</p>
                            <p style="text-indent: 2em">主题，【XX修改银行卡】</p>
                            <p style="text-indent: 2em">正文，【姓名、身份证号、手机号、新旧银行卡号、新银行卡照片】</p>
                        </div>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-heading dark">
                        <h4 class="panel-title">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                               href="#question4">
                                客服的联系方式有哪些？
                            </a>
                        </h4>
                    </div>
                    <div id="question4" class="panel-collapse collapse" style="height: auto;">
                        <div class="panel-body">
                            <p>1、电话：400-002-9691</p>
                            <p>2、邮箱：jinrongfuwu@kezhanwang.cn</p>
                            <p>3、微信公众号：大圣分期网金融服务平台</p>
                        </div>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-heading dark">
                        <h4 class="panel-title">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                               href="#question5">
                                银行卡出现不一致、不匹配、身份信息数据查询不到，怎么办？
                            </a>
                        </h4>
                    </div>
                    <div id="question5" class="panel-collapse collapse" style="height: auto;">
                        <div class="panel-body">
                            <p>身份证号，银行卡，姓名，手机号，有一个对不上都是不匹配，但是哪个不匹配需要问银行，我们无法进入到银行系统去查询的</p>
                            <p>辛苦老师了解，不是学生说都一致就肯定一致。如果信息都一致，是肯定可以验证过去的，建议联系银行查询！</p>
                        </div>
                    </div>
                </div>
                <div class="panel">
                    <div class="panel-heading dark">
                        <h4 class="panel-title">
                            <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion2"
                               href="#question6">
                                学员退课、提前还款费用怎么计算？
                            </a>
                        </h4>
                    </div>
                    <div id="question6" class="panel-collapse collapse" style="height: auto;">
                        <div class="panel-body">
                            <p>1、因现在现在有多个资金方，烦请机构老师，将学员姓名、身份证号，发送至对接运营，对接运营会计算出当日金额。</p>
                            <p>
                                2、若学员想自己了解，可告知学员拨打客服热线400-002-9691，或者发邮件，至金融服务邮箱jinrongfuwu@kezhanwang.cn，或通过微信公众号，自行咨询费用事宜。</p>
                        </div>
                    </div>
                </div>
            </div>
            <!--collapse end-->
        </div>
    </div>
</div>
<!--body wrapper end-->

<!-- Modal -->
<div class="modal fade" id="myModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true"
     style="margin-top:8%;">
    <div class="modal-dialog" id="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                <h4 class="modal-title">主动还款详解</h4>
            </div>

            <div class="modal-body row">
                <div class="col-md-12 img-modal">
                    <img id="image" src="" alt="" class="img-rounded" style="height: 550px;width: auto;display: block;">
                </div>
            </div>
        </div>
    </div>
</div>
<!-- modal -->

<script type="text/javascript">
    function openImg(src, width) {
        $("#image").attr('src', src);
        var screenImage = $("#image");
        // Create new offscreen image to test
        var theImage = new Image();
        theImage.src = screenImage.attr("src");

        // Get accurate measurements from that.
        var imageWidth = theImage.width;
        var imageHeight = theImage.height;
        var bili = imageHeight / imageWidth;
        var newImageWidth = 550 / bili + 20;
        $('#modal-dialog').css({"width": newImageWidth});
    }
</script>
@endsection
