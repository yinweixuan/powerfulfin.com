<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{config('admin.name')}}机构后台</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/AdminLTE/bootstrap/css/bootstrap.min.css") }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/font-awesome/css/font-awesome.min.css") }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/AdminLTE/dist/css/AdminLTE.min.css") }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ admin_asset("/vendor/laravel-admin/AdminLTE/plugins/iCheck/square/blue.css") }}">
    <link href="{{ admin_asset("/org/css/style.css") }}" rel="stylesheet">
    <link href="{{ admin_asset("/org/css/style-responsive.css") }}" rel="stylesheet">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="{{ admin_asset("/js/html5shiv.min.js") }}"></script>
    <script src="{{ admin_asset("/js/respond.min.js") }}"></script>
    <![endif]-->
</head>
<body class="sticky-header">
{{-- 左侧菜单 --}}
@include('org.common.left')
<div class="main-content">
{{-- 包含页头 --}}
@include('org.common.header')

{{-- 继承后插入的内容 --}}
@yield('content')

{{-- 包含页脚 --}}
@include('org.common.footer')
</div>
<!-- jQuery 2.1.4 -->
<script src="{{ admin_asset("/vendor/laravel-admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js")}} "></script>
<!-- Bootstrap 3.3.5 -->
<script src="{{ admin_asset("/vendor/laravel-admin/AdminLTE/bootstrap/js/bootstrap.min.js")}}"></script>
<!-- iCheck -->
<script src="{{ admin_asset("/vendor/laravel-admin/AdminLTE/plugins/iCheck/icheck.min.js")}}"></script>
<script src="{{ admin_asset("/js/jquery-migrate-1.2.1.min.js")}}"></script>
<script src="{{ admin_asset("/js/jquery-ui-1.9.2.custom.min.js")}}"></script>
<script src="{{ admin_asset("/js/jquery.nicescroll.js")}}"></script>
<script src="{{ admin_asset("/js/modernizr.min.js")}}"></script>
<script src="{{ admin_asset("/js/respond.min.js")}}"></script>
<script src="{{ admin_asset("/org/js/common.js")}}"></script>
</body>
</html>
