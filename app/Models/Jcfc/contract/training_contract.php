<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/html">
<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="format-detection" content="telephone=no"/>
    <title>教育培训协议</title>
    <style type="text/css">
        body {
            max-width: 1000px;
            margin: auto;
            font-size: 16px;
            color: #484f55;
            background: #f9f9f9;
            font-family: "微软雅黑";
        }

        h1, h2 {
            text-align: center;
            color: black;
        }

        p {
            text-indent: 2em;
            text-shadow: 1px 1px white;
            margin: 1em 0;
        }

        .wrapper {
            padding: 0 10px;
        }

        .ti {
            text-indent: 2em;
        }
    </style>
</head>
<body>
<div class="wrapper">
    <h1>教育培训协议</h1>
</div>
<div class="ti">
    <p>甲方：<?php echo $loan['school_name']; ?></p>
    <p>联系电话：<?php echo $loan['contact_mobile']; ?></p>
    <p>乙方：<?php echo $loan['idcard_name']; ?></p>
    <p>联系电话：<?php echo $loan['phone']; ?></p>
</div>
<div class="ti">
    <p>
        <strong>
            依据《中华人民共和国合同法》的规定，甲乙双方就甲方为乙方提供培训服务事宜，经双方协商一致，本着平等互利的原则，特订立以下合同条款，以兹共同信守。
        </strong>
    </p>
</div>
<h3>一、 培训内容</h3>
<div class="ti">
    <p>课程名称：<?php echo $loan['course_name']; ?></p>
    <p>课程价格：<?php echo $loan['money_apply']; ?>元</p>
</div>
<h3>二、 甲方的权利义务</h3>
<div class="ti">
    <p> 1、 甲方有权对乙方提供的个人资料信息进行审核；</p>
    <p> 2、 甲方确保教学质量，使乙方能够顺利结业，并灵活运用所学知识；</p>
    <p> 3、 甲方负责制定学员管理制度；</p>
    <p> 4、 甲方负责安排学员的课程计划和实施计划；</p>
    <p> 5、 甲方提供培训教学场地及其教学资料；</p>
    <p> 6、 培训时间根据实际情况进行安排。</p>
</div>
<h3>三、 乙方的权利义务</h3>
<div class="ti">
    <p>1、 乙方应遵守学校的相关管理制度；</p>
    <p>2、 乙方不得有任何有损于甲方声誉及形象的言行；</p>
    <p>3、 乙方保证所提供信息的真实性，如乙方提供的信息不真实或者侵犯了第三方的权利，一切责任由乙方承担。</p>
    <p>4、 乙方承认已详细了解甲方的培训课程及其教学计划、学员管理制度。</p>
</div>
<h3>四、 协议终止</h3>
<div class="ti">
    <p>1、 履行本协议过程中发生不可抗力事件包括地震、洪灾、战争等致使协议失去继续履行的条件或确无履行的必要，本协议自动终止；</p>
    <p>2、 由于非自然的原因包括企业破产、清盘、国家政策变更等，或者甲方的法人资格消灭，本协议自动终止。</p>
</div>
<h3>五、 其他</h3>
<div class="ti">
    <p>1、 协议双方因本协议出现任何争议，应由双方友好协商解决，协商不成的，任何一方均有权将争议提交至被告住所地的人民法院诉讼解决；</p>
    <p>2、 本协议自双方签字、盖章之日起生效；本协议一式两份，甲乙双方各执一份，具有同等法律效力。</p>
</div>
<div class="ti">
    <p>【以下无正文】</p>
</div>
<div class="ti">
    <p>甲方：<?php echo $loan['school_name']; ?></p>
    <p>公司盖章：</p>
    <p>签署日期：<?php echo date('Y年m月d日', strtotime($loan['ctime'])); ?></p>
</div>
<div class="ti">
    <p>乙方名字（签章）：<?php echo $loan['idcard_name']; ?></p>
    <p>身份证号码：<?php echo $loan['idcard']; ?></p>
    <p>签署日期：<?php echo date('Y年m月d日', strtotime($loan['ctime'])); ?></p>
</div>
</body>
</html>
