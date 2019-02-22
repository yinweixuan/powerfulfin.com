<?php
/**
 * Created by PhpStorm.
 * User: wangyi
 * Date: 2019/2/22
 * Time: 10:13 AM
 */
?>

<div class="row">
    <div class="col-md-12">
        <div class="box box-danger">
            <div class="box-header">
                <h3 class="box-title">搜索</h3>
            </div>
            <form method="get">
                <input type="hidden" name="page" value="{{ $page }}">
                <div class="box-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>UID:</label>
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-child"></i>
                                    </div>
                                    <input type="text" name="uid" class="input-sm form-control"
                                           placeholder="UID" value="{{$uid}}">
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>手机号:</label>
                                <div class="input-group">
                                    <div class="input-group-addon">
                                        <i class="fa fa-child"></i>
                                    </div>
                                    <input type="number" name="phone" class="input-sm form-control" maxlength="11"
                                           minlength="11"
                                           placeholder="请填写手机号" value="{{$phone}}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="box-footer">
                    <button type="submit" class="btn btn-sm btn-danger">查询</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="row">
    <div class="col-md-12">
        <div class="box box-danger">
            <div class="box-header">
                <h3 class="box-title">查询列表</h3>
            </div>
            <div class="box-body">
                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="row">
                        <div class="col-sm-12" style="overflow-x: auto;">
                            <table id="example2" class="table table-bordered table-hover" role="grid"
                                   aria-describedby="example2_info">
                                <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>UID</th>
                                    <th>手机号</th>
                                    <th>内容</th>
                                    <th>发送时间</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($info as $item)
                                    <tr>
                                        <td>{{ $item['id'] }}</td>
                                        <td>{{ $item['uid'] }}</td>
                                        <td>{{ $item['phone'] }}</td>
                                        <td>{{ $item['msg'] }}</td>
                                        <td>{{ $item['create_time'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>ID</th>
                                    <th>UID</th>
                                    <th>手机号</th>
                                    <th>内容</th>
                                    <th>发送时间</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                {{ $info->links() }}
            </div>
        </div>
    </div>
</div>
