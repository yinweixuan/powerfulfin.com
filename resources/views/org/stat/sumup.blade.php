<?php
/**
 * 校区汇总
 * User: haoxiang
 * Date: 2018/12/25
 * Time: 10:33 AM
 */
use App\Components\OutputUtil;
//总校和分校展示信息相同,套用同样的表格
function displayStat($result)
{
    $content = '<table class="table table-hover table-bordered">';      //表头
    $content .= '<thead>
        <tr>
            <td><b>机构名称</b></td>
            <td><b>总数</b></td>
            <td><b>审核中</b></td>
            <td><b>待放款</b></td>
            <td><b>已放款</b></td>
            <td><b>逾期</b></td>
            <td><b>M1</b></td>
            <td><b>M2</b></td>
            <td><b>M3</b></td>
            <td><b>M4</b></td>
            <td><b>M5</b></td>
            <td><b>M6+</b></td>
        </tr>
        </thead><tbody>';
    foreach ($result as $r) {
        $content .= "<tr>";
        $content .= "<td>{$r['baseInfo']['short_name']}</td>";
        $content .= "<td>{$r['total']}单<br />￥" . OutputUtil::echoMoney($r['total_money']) . "</td>";
        $content .= "<td>{$r['audit']}单<br />￥" . OutputUtil::echoMoney($r['audit_money']) . "</td>";
        $content .= "<td>{$r['repaying']}单<br />￥" . OutputUtil::echoMoney($r['repaying_money']) . "</td>";
        $content .= "<td>{$r['repayed']}单<br />￥" . OutputUtil::echoMoney($r['repayed_money']) . "</td>";
        $content .= "<td>{$r['delay']}单<br />￥" . OutputUtil::echoMoney($r['delay_money']) . "</td>";
        for ($i = 1; $i<=6; $i++) {
            $content .= "<td>" . (array_key_exists($i, $r['mInfo']) ? $r['mInfo'][$i]:0) . '</td>';
        }
        $content .= '</tr>';
    }
    $content .= '</tbody></table>';
    return $content;
}
?>

@extends('org.common.base')
@section('title',  '校区汇总')
@section('content')

<div id="pjax-content">
    <?php if (isset($headStat) && $headStat) {?>
    <div class="row" >
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">机构总计 <span style="font-size:xx-small;"></span></div>
                <div class="panel-body" style="overflow-x: auto;">
                    <?php echo displayStat($headStat);?>
                </div>
            </div>
        </div>
    </div>
        <?php }?>
    <div class="row">
        <div class="col-sm-12">
            <div class="panel panel-default">
                <div class="panel-heading">分校统计 <span style="font-size:xx-small;"></span><button style="display:none;" href="#" class='btn btn-warning' onclick="overdueY()" >导出</button></div>
                <div class="panel-body" style="overflow-x: auto;">
                    <?php echo displayStat($stat);?>
                </div>
            </div>
        </div>
    </div>
    <div class="row" >

    </div>
</div>
<script type="text/javascript">
    jQuery(document).ready(function () {
        //$(".select2").select2();
        //Date picker
        $('.datepicker').datepicker({
            format: 'yyyy-mm-dd',
            autoclose: true
        });
    });
    function goUrl(page) {
        var page = "page_" + page;
        var url = $("#" + page + "").data();
        console.log(url);
        $.pjax({
            url: url['url'],
            container: '#pjax-content',
            timeout: 10000,
        });
    }
    $("#loginform").keydown(function(e){
        var e = e || event,
            keycode = e.which || e.keyCode;
        if (keycode==13) {
            $("#log_btn").trigger("click");
        }
    });
    function overdueY()
    {
        var name = $('#name').val();
        url = "schoolloan?excel=1";
        window.location.href = url;
    }

</script>
@endsection
