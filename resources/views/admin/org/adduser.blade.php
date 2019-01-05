<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/1/5
 * Time: 8:42 PM
 */
?>
<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">新增</h3>
    </div>
    <form class="form-horizontal" role="form" name="form" action="" method="post">
        <div class="box-body">
            <div class="form-group">
                <label class="col-sm-2 control-label">登录名</label>
                <div class="col-sm-9">
                    <input type="hidden" value="adduser" name="type">
                    <input type="text" class="form-control" placeholder="请输登录名" value="" name="org_username">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">密码</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" placeholder="请填写密码" value=""
                           name="org_password">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">分校ID</label>
                <div class="col-sm-9">
                    <input type="text" class="form-control" placeholder="请填写分校ID" value="" name="org_id">
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">账户状态</label>
                <div class="col-sm-9">
                    <select name="status" style="width: 100%;">
                        <option value="">请选择</option>
                        <option value="{{ STATUS_SUCCESS }}">可用</option>
                        <option value="{{ STATUS_FAIL }}">不可用</option>
                    </select>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-2 control-label">权限</label>
                <div class="col-sm-9">
                    <select name="right" style="width: 100%;">
                        <option value="">请选择</option>
                        <option value="0">审核</option>
                        <option value="1">汇总</option>
                    </select>
                </div>
            </div>
        </div>
        <div class=" box-footer">
            <a href="/admin/org/users" class="btn btn-sm btn-default">取消</a>
            <button name="submit" type="submit" class="btn btn-sm btn-danger pull-right">提交</button>
        </div>
    </form>
</div>
