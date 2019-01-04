@extends('org.common.base')
@section('title',  '分期详情')
@section('content')
<?php
/**
 * 分期详情
 * User: haoxiang
 * Date: 2018/12/25
 * Time: 10:30 AM
 */
use App\Components\OutputUtil;
?>
<div class="panel panel-default">
    <div class="panel-heading"><h3 class="panel-title">
            订单详情：<?php OutputUtil::echoEscape("{$real['full_name']}({$base['id']})");?>
        </h3>
    </div>
    <div class="panel-body">
        <div class="row mt_10">
            <div class="col-md-12">
                <table class="table table-hover table-bordered table-striped ">
                    <tr>
                        <td>申请来源</td>
                        <td><?php echo OutputUtil::valuePhoneType($base[''])?></td>
                        <td>备注</td>
                        <td><?php OutputUtil::echoEscape($base['audit_opinion'] ? $base['audit_opinion'] : ''); ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" align="center">分期信息</td>
                    </tr>
                    <tr>
                        <td>期望分期金额</td>
                        <td><?php OutputUtil::echoMoney($base['borrow_money']); ?></td>
                        <td>分期类型</td>
                        <td><?php OutputUtil::echoEscape($base['loan_product_desc']); ?></td>
                    </tr>
                    <tr>
                        <td>学校</td>
                        <td><?php OutputUtil::echoEscape($org['name']);?></td>
                        <td>分期课程</td>
                        <td><?php OutputUtil::echoEscape($class['class_name']); ?></td>
                    </tr>
                    <tr>
                        <td colspan="4" align="center">分期账单信息</td>
                    </tr>
                    <tr>
                        <td>订单状态</td>
                        <td><?php  ?></td>
                        <td>放款时间</td>
                        <td><?php if (!empty($base['loan_time'])) {
                                echo $base['loan_time'];
                            } else {
                                echo "暂未放款";
                            } ?></td>
                    </tr>
                    <tr>
                        <td>分期期数</td>
                        <td><?php OutputUtil::echoEscape( $base['loan_product_config']['rate_time']); ?></td>
                        <td>资金方</td>
                        <td><?php OutputUtil::echoEscape($base['resource_desc_simple']); ?></td>
                        </td>
                    </tr>
                    <tr>
                        <td colspan="4" align="center">分期信息资料</td>
                    </tr>
                    <tr>
                        <td>订单号</td>
                        <td><?php OutputUtil::echoEscape( $base['id']); ?></td>
                        <td>姓名</td>
                        <td><?php OutputUtil::echoEscape($real['full_name']); ?></td>
                    </tr>
                    <tr>
                        <td>身份证</td>
                        <td><?php OutputUtil::echoEscape($real['identity_number']);?></td>
                        <td>手机号</td>
                        <td><?php OutputUtil::echoEscape($user['phone']); ?></td>
                    </tr>
                    <tr>
                        <td>开课时间</td>
                        <td><?php OutputUtil::echoEscape($base['class_start_date']); ?></td>
                        <td>课程顾问</td>
                        <td><?php OutputUtil::echoEscape($base['class_adviser'] ? $info['class_adviser'] : '无'); ?></td>
                    </tr>
                    <tr>
                        <td>银行</td>
                        <td><?php
                            echo \App\Models\Server\BU\BUBanks::getBankName($bank[0]['bank_code']);
                            ?></td>
                        <td>银行卡号</td>
                        <td><?php OutputUtil::echoEscape($bank[0]['bank_account']); ?></td>
                    </tr>
                    <tr>
                        <td>学历</td>
                        <td><?php OutputUtil::echoEscape($work['highest_education']); ?></td>
                        <td>工作</td>
                        <td><?php echo OutputUtil::valueWorkStatus($work['working_status']);?>(<?php OutputUtil::echoEscape($work['profession']); ?>)</td>
                    </tr>
                    <tr>
                        <td>第一联系人</td>
                        <td><?php OutputUtil::echoEscape($contact['contact_person'] . "({$contact['contact_person_relation']})"); ?></td>
                        <td>联系方式</td>
                        <td><?php OutputUtil::echoEscape($contact['contact_person_phone']);?></td>
                    </tr>
                    <tr>
                        <td colspan="4" align="center">图像资料</td>
                    </tr>
                    <tr>
                        <td>场景照</td>
                        <td colspan="3" align="center">
                            <?php if (!empty($base['scene_pic'])) {
                                $picArr = OutputUtil::json_decode($base['scene_pic']);
                                if (!is_array($picArr)) {
                                    $picArr = [$picArr];
                                }
                                foreach ($picArr as $pic) {
                                    $url = OutputUtil::valueImg($pic);
                                ?>
                            <a href="<?php echo $url; ?>" target="_blank">
                                <img src="<?php echo $url; ?>" style="width:200px">
                            </a>
                            <?php }} else { ?>
                            <p style="color: red" align="center">暂无</p>
                            <?php } ?>
                        </td>
                    </tr>
                    <tr>
                        <td>学籍证明</td>
                        <td colspan="3" align="center">
                            <?php if (!empty($work['edu_pic'])) {
                            $picArr = OutputUtil::json_decode($work['edu_pic']);
                            if (!is_array($picArr)) {
                                $picArr = [$picArr];
                            }
                            foreach ($picArr as $pic) {
                                $url = OutputUtil::valueImg($pic);
                            ?>
                            <a href="<?php echo $url; ?>" target="_blank">
                                <img src="<?php echo $url; ?>" style="width:200px">
                            </a>
                            <?php }} else { ?>
                            <p style="color: red" align="center">未上传学籍证明，有拒绝风险，请联系学员上传学生证或学信网截图</p>
                            <?php } ?>
                        </td>
                    </tr>
                    <?php if (!empty($info['train_contract_pic'])) {
                        echo "<tr><td>培训协议</td><td colspan='3'>";
                        $picArr = OutputUtil::json_decode($base['train_contract_pic']);
                        if (!is_array($picArr)) {
                            $picArr = [$picArr];
                        }
                        foreach ($picArr as $pic) {
                            $url = OutputUtil::valueImg($pic);
                            echo "<a href='{$url}' target='_blank'><img src='{$url}' style='width:200px'></a>";
                        }
                        echo "</td></tr>";
                    }?>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
