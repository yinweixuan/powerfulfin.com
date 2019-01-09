@extends('org.common.base')
@section('title',  '首页')
@section('content')
<?php
/**
 * 首页展示
 * User: haoxiang
 * Date: 2018/12/24
 * Time: 3:42 PM
 */
?>
<div class="container">
    <div class="page-header">
        <h1>扫码申请</h1>
    </div>
    <p class="lead">使用<b>大圣分期APP扫描该二维码</b>,可快速进入申请流程,方便快捷</p>
    <p class="lead">可保存文件并打印,置于机构前台或教室,用于学员申请</p>
    <p><img src="<?php echo "/home/qr?oid={$org_id}";?>" alt="扫描二维码进入分期申请" style="margin-left: 200px;width:200px;"/></p>
</div>
@endsection
