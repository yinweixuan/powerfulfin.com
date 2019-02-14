<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/14
 * Time: 11:38 AM
 */
?>
<div class="callout callout-success">
    <h4>操作成功</h4>
    {{ $message }}
    <p>5s后自动跳转。。。</p><a href="{{ $url }}">手动跳转</a>
</div>
<input type="hidden" id="jump_url" value="{{ $url }}">
<script type="application/javascript">
    $(function () {
        var url = $('#jump_url').val();
        setInterval(function () {
            window.location.href = url;
        }, 5000);
    })
</script>
