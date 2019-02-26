<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0" />
    <meta name="apple-mobile-web-app-capable" content="yes" />
    <meta name="format-detection" content="telephone=no" />
    <title>大圣分期</title>
    <style>
        *{ margin:0; padding:0;}
        body{padding:0; margin:0; font-size:16px; color:#484f55; background: #fff; font-family: "微软雅黑"; width: 100%; height: 100%;}
        img{ padding: 0; margin: 0;}
        .free{}
        .content{ background: url(/img/wx_download/bg.jpg) no-repeat scroll center top; background-size: cover; width: 100%; height: 100%; position: absolute;}
        .content .button{ position: absolute; left: 50%; bottom: 30%;  margin-left:-44px; width: 88px; height: 27px; background: #fff; border: 1px #007dc8 solid; border-radius: 5px; font-size: 16px; color: #007dc8; text-align: center; line-height: 27px;}
        .tc{
            left: 0px;
            top: 0px;
            background: url(/img/wx_download/tc.png) no-repeat scroll center top;
            background-size: cover; ;
            overflow: hidden;
            position: fixed;
            height: 100%;
            width: 100%;
            z-index: 10000;

        }
    </style>
</head>
<body>
<div id="page" data-role = "page" class="free">
    <div data-role = "none" class="content">
        <?php
        if ($isWX) {
        ?>
        <a href="javascript:void(0)" onclick="document.getElementById('download').style.display='block'">
            <div class="button">下载</div>
            <div class='tc' id="download" style="display:none"></div>
        </a>
            <?php } else if($isIOS) {?>
                <a class="button" href="itms-services://?action=download-manifest&url=https://app.powerfulfin.com/web/app/manifest.plist">下载</a>
            <?php } else {?>
                <a class="button" href="http://www.powerfulfin.com/downloadpackage?f=download">下载</a>
            <?php } ?>



    </div>
</div>
</body></html>
