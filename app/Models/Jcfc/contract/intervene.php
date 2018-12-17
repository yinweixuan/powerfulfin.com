<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport"
          content="width=device-width, initial-scale=1.0, user-scalable=0, minimum-scale=1.0, maximum-scale=1.0"/>
    <meta name="apple-mobile-web-app-capable" content="yes"/>
    <meta name="format-detection" content="telephone=no"/>
    <title>课栈网居间服务协议</title>
    <style type="text/css">

        .wrapper {
            padding: 0 10px;
        }

        .ti {
            text-indent: 2em;
        }
    </style>
</head>
<body>
<p class="wrapper">
<h2 align="center">课栈网居间服务协议</h2>
<p align="right">协议编号：<?php echo $loan['id'].'-'.$loan['applseq'];?></p>

<p>本协议由以下双方于<?php echo $loan['date']; ?>线上签署并履行。</p>
<p>甲方（借款人）：</p>
<div class="ti">
    <table>
        <tbody>
        <tr>
            <th style="width: 25%" align="left">姓&emsp;&emsp;名</th>
            <th style="width: 75%" align="left"><?php echo $loan['idcard_name']; ?></th>
        </tr>
        <tr>
            <th style="width: 25%" align="left">身份证号</th>
            <th style="width: 75%" align="left"><?php echo $loan['idcard']; ?></th>
        </tr>
        <tr>
            <th style="width: 25%" align="left">手&ensp;机&ensp;号</th>
            <th style="width: 75%" align="left"><?php echo $loan['phone']; ?></th>
        </tr>
        <tr>
            <th style="width: 25%" align="left">联系地址</th>
            <th style="width: 75%" align="left"><?php echo $loan['home']; ?></th>
        </tr>
        </tbody>
    </table>
</div>
<p>乙方（居间服务商）：</p>
<div class="ti">
    <table>
        <tbody>
        <tr>
            <th style="width: 25%" align="left">公司名称</th>
            <th style="width: 75%" align="left">北京弟傲思时代信息技术有限公司（课栈网www.kezhanwang.cn）</th>
        </tr>
        <tr>
            <th style="width: 25%" align="left">联系地址</th>
            <th style="width: 75%" align="left">北京市海淀区西土城路1号院7号楼10层1018号</th>
        </tr>
        <tr>
            <th style="width: 25%" align="left">联&ensp;系&ensp;人</th>
            <th style="width: 75%" align="left"></th>
        </tr>
        <tr>
            <th style="width: 25%" align="left">联系电话</th>
            <th style="width: 75%" align="left"></th>
        </tr>
        </tbody>
    </table>
</div>
<p>鉴于：</p>
<p>
<div class="ti">
    1） 甲方作为乙方合作教育培训机构的学员，与该等教育培训机构签订培训协议并约定分期支付培训费用，就此有一定的资金需求；
</div>
<div class="ti">
    2）乙方作为教育培训互联网服务者，拥有并运营“课栈网（网址www.kezhanwang.cn，包括APP端、网页端、微信端等）”，与众多优质的教育培训机构达成合作，为其提供品牌推广、招生引流及线上课程售卖服务；
</div>
<div class="ti">
    3）为使甲方在乙方合作的教育培训机构顺利购买课程并实现分期支付培训费用，乙方向甲方推荐提供分期借贷服务的第三方（以下统一简称“资金方”），撮合甲方与资金方就分期借款事项达成相关协议。
</div>
现双方在平等自愿、诚实信用的基础上，就以上服务达成一致，特订立本协议。
</p>

<p>
<h3>第一条 甲方权利与义务</h3>
<div class="ti">1） 甲方有权通过乙方了解其在资金方的信用评审进度及结果；</div>
<div class="ti">2） 甲方在申请及实现借款的全过程中，必须如实向乙方和资金方提供所要求提供的个人信息；</div>
<div class="ti">3） 甲方委托乙方为其申请、调用电子签章（如有）；</div>
<div class="ti">4） 甲方在资金方建立个人信用账户，授权资金方基于甲方提供的信息及资金方独立获取的信息来管理甲方的信用信息；</div>
<div class="ti">5） 甲方应按照本协议的规定向乙方支付居间服务费，向资金方支付每月应还本金、利息及相关费用（如有）；</div>
<div class="ti">6） 甲方同意，甲方成功借款后，甲方按照约定期限及金额进行还款，甲方有义务无条件及时配合工作；</div>
</p>
<p>
<h3>第二条 乙方权利与义务</h3>
<div class="ti">1） 乙方为甲方在线购买课程及申请分期支付培训费用提供课栈网平台，并提供相关协助工作；</div>
<div class="ti">2）
    乙方为甲方推荐可提供分期借贷服务的资金方，提供相关的信息咨询，并在甲方申请借款过程中协助其办理各项手续，包括但不限于为甲方与资金方之间的在线文件传输提供技术支持，并依据甲方的委托为其申请、调用电子签章（如有）；
</div>
<div class="ti">3） 乙方就为甲方提供线上平台及相关服务收取居间服务费；</div>
<div class="ti">4） 对于甲方提供给乙方的个人证件及其他各类信息，乙方有义务在本协议约定下为甲方保密。</div>
</p>
<p>
<h3>第三条 居间服务费</h3>
<div class="ti">1） 甲方选择的产品及应支付居间服务费如下：</div>
<div class="ti" style="text-indent: 2em">① 分期借贷本金：【<?php echo $loan['money_apply']; ?>】元；</div>
<div class="ti" style="text-indent: 2em">② 产品类型：有补贴【<?php if ($loan['is_free']) {echo "∨";}?> 】 无补贴【 <?php if (!$loan['is_free']) {echo "∨";}?>】；</div>
<div class="ti" style="text-indent: 2em">③ 分期期数：【<?php echo $loan['repay_need']; ?>】期；</div>
<div class="ti" style="text-indent: 2em">④ 居间服务费费率：总费率【 <?php if ($loan['is_free']) {echo $loan['interview_fee']*100 . "%";}?>】</div>
<div class="ti" style="text-indent: 2em">⑤ 居间服务费总额：【<?php echo $loan['intervene_fee'];?>】元</div>
<div class="ti" style="text-indent: 2em">注：居间服务费总额=本金×总费率；</div>
<br/>
<div class="ti">2） 在本协议中，“居间服务费”是指乙方为甲方推荐提供分期借贷服务的资金方，并在甲方申请借款过程中提供平台服务技术支持而由甲方支付给乙方的报酬。</div>
<div class="ti">3） 乙方收取的居间服务费与资金方收取的借款利息一并收取，并由资金方代为划扣。</div>
<div class="ti">4）
    如甲方选择有补贴的产品，即教育培训机构承诺为甲方提供补贴向资金方一次性全额支付全部息费，则甲方不可撤销地授权资金方在按照分期贷款合同（或称“个人消费贷款合同”，具体名称以甲方与学员实际签署的贷款合同名称为准）约定放款时，将包括贷款利息、相关费用（如有）、乙方居间服务费在内的全部息费一次性扣除，剩余款项代为支付给指定的教育培训机构。
</div>
<div class="ti">5） 如甲方选择无补贴的产品，即由甲方向资金方按期等额支付全部息费，则乙方收取的居间服务费由资金方每期扣款时一并划扣。</div>
<div class="ti">6）
    甲方不可撤销地同意并授权乙方委托资金方代收/代扣本合同项下的居间服务费。甲方须在还款日当日（北京时间15：00）之前将足额款项存入还款账户供资金方按时划扣；即使还款日非工作日，还款时间并不顺延，甲方应提前存入足额款项。如因余额不足、卡片状态不正常等任何原因导致未能成功扣款，由此产生的逾期及其他损失和不利后果由甲方自行承担。扣款通知发出后甲方对扣收有异议的，应当在扣款通知送达之日起7个工作日内以书面形式向资金方提出。
</div>
<div class="ti">7）
    甲方如发生提前还款、中途退课情形的，乙方已经收取的居间服务费（如有）不予退还；居间服务费的支付方式、逾期罚息、违约处理等事宜，均参照甲方与资金方签署的《个人消费贷款合同》中约定的与贷款利息相关的规则执行。
</div>
</p>
<p>
<h3>第四条 甲方指定账户及款项扣划</h3>
<div class="ti">1） 甲方须向乙方提供指定银行账户用于款项划扣。</div>
<div class="ti">2） 甲方须确保提供的账户为甲方名下合法有效的银行账户，如甲方需要变更指定账户，须在还款日前至少5个工作日向乙方提出申请。否则因此导致甲方未能实现及时还款，并因此导致的逾期违约金和罚息由甲方承担。</div>
</p>
<p>
<h3>第五条 违约规定</h3>
<div class="ti">1） 任何一方违反本协议的约定，使得本协议的全部或部分不能履行，均应承担违约责任，并赔偿对方因此遭受的损失。</div>
<div class="ti">2） 如甲方未按与资金方签署的《个人消费贷款合同》及时足额还款而对乙方造成任何损失的，乙方有权就前述损失对甲方进行追偿，并要求甲方承担因追偿而发生的律师费、诉讼费、鉴定费、差旅费等相关费用。</div>
<div class="ti">3） 乙方及乙方推荐的资金方保留将甲方违约失信的相关信息在媒体披露的权利。</div>
</p>
<p>
<h3>第六条 变更通知</h3>
<div class="ti">1） 本协议签订之日起至借款全部清偿之日止，甲方有义务在下列信息变更后3日内提供更新后的信息给资金方：甲方本人、甲方的家庭联系人及紧急联系人的工作单位、居住地址、住所电话、手机号码、电子邮箱。</div>
<div class="ti">2） 若因甲方不及时提供上述变更信息而使资金方产生调查及诉讼等其他相关费用，由甲方承担。</div>
</p>
<p>
<h3>第七条 其他</h3>
<div class="ti">1） 本协议由甲乙双方签字/盖章后于文首所载日期成立；自《个人消费贷款合同》生效之日起生效，至甲方清偿全部借款及相关息费之日终止。</div>
<div class="ti">2） 本协议的任何修改、补充均须以书面的形式作出。</div>
<div class="ti">3） 甲乙双方均确认，本协议的签署、生效和履行以不违反中国的法律法规为前提。如果本协议中的任何一条或多条违反适用的法律法规，则该条将被视为无效，但该无效条款并不影响本协议其他条款的效力。</div>
<div class="ti">4）
    甲方承诺，本次借款用以支付教育培训学费。甲方与教育培训机构之间的培训事宜、退课所涉实际费用结算及纠纷解决，以双方另行签订的相关培训协议中的约定为准。互联网第三方征信查询授权、提前还款服务费、延迟还款服务费、甲方详细还款计划（方式）等相关条款，以甲方与资金方另行签署的《个人消费贷款合同》中的约定为准。
</div>
<div class="ti">5） 如果甲乙双方在本协议履行过程中发生任何争议，应友好协商解决；如协商不成，则任一方有权将争议提交北京市朝阳区人民法院诉讼解决。</div>
</p>
<p>【以下无正文】</p>
<div class="ti">
    <table>
        <tbody>
        <tr>
            <th style="width: 50%" align="left">甲方：<?php echo $loan['idcard_name']; ?></th>
            <th style="width: 50%" align="left">乙方：北京弟傲思时代信息技术有限公司</th>
        </tr>
        <tr>
            <th align="left">签字：</th>
            <th align="left">签章：</th>
        </tr>
        </tbody>
    </table>
</div>
</div>
</body>
</html>
