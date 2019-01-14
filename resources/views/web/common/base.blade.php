<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <meta name="description" content="{{ config('app.company_name') }}"/>
    <meta name="keywords" content="教育培训,招生辅助,培训周边">
    <title>@yield('title')</title>
    <link rel="icon" href="{{ URL::asset('web/img/favicon.ico') }}" />
    <!-- css -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="{{ URL::asset('web/materialize/css/materialize.min.css') }}"
          media="screen,projection"/>
    <link href="{{ URL::asset('web/css/bootstrap.min.css') }}" rel="stylesheet"/>
    <link href="{{ URL::asset('web/css/fancybox/jquery.fancybox.css') }}" rel="stylesheet">
    <link href="{{ URL::asset('web/css/flexslider.css') }}" rel="stylesheet"/>
    <link href="{{ URL::asset('web/css/style.css') }}" rel="stylesheet"/>

    <!-- HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
    <script src="http://html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

</head>
<body>
{{-- 包含页头 --}}
@include('web.common.header')

{{-- 继承后插入的内容 --}}
@yield('content')

{{-- 包含页脚 --}}
@include('web.common.footer')


<!-- Placed at the end of the document so the pages load faster -->
<script src="{{ URL::asset('web/js/jquery.js') }}"></script>
<script src="{{ URL::asset('web/js/jquery.easing.1.3.js') }}"></script>
<script src="{{ URL::asset('web/materialize/js/materialize.min.js') }}"></script>
<script src="{{ URL::asset('web/js/bootstrap.min.js') }}"></script>
<script src="{{ URL::asset('web/js/jquery.fancybox.pack.js') }}"></script>
<script src="{{ URL::asset('web/js/jquery.fancybox-media.js') }}"></script>
<script src="{{ URL::asset('web/js/jquery.flexslider.js') }}"></script>
<script src="{{ URL::asset('web/js/animate.js') }}"></script>
<!-- Vendor Scripts -->
<script src="{{ URL::asset('web/js/modernizr.custom.js') }}"></script>
<script src="{{ URL::asset('web/js/jquery.isotope.min.js') }}"></script>
<script src="{{ URL::asset('web/js/jquery.magnific-popup.min.js') }}"></script>
<script src="{{ URL::asset('web/js/animate.js') }}"></script>
<script src="{{ URL::asset('web/js/custom.js') }}"></script>
</body>
</html>
