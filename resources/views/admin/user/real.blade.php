{!! Admin::css(admin_asset('plugins/viewerjs/dist/viewer.css')) !!}

<div class="box box-danger">
    <div class="box-header with-border">
        <h3 class="box-title">搜索</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i>
            </button>
        </div>
    </div>
    <form class="form_inline" role="form" name='form1' action="" method="get">
        <input type="hidden" name="page" value="{{$page}}">
        <div class="box-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label>用户UID：</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-university"></i>
                            </div>
                            <input type="text" name="uid" class="form-control input-sm" placeholder="用户UID"
                                   value="{{$uid}}">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>手机号：</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-university"></i>
                            </div>
                            <input type="number" name="phone" class="form-control input-sm" placeholder="手机号"
                                   value="{{$phone}}">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>姓名：</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-university"></i>
                            </div>
                            <input type="text" name="full_name" class="form-control input-sm" placeholder="姓名"
                                   value="{{$full_name}}">
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>身份证：</label>
                        <div class="input-group">
                            <div class="input-group-addon">
                                <i class="fa fa-university"></i>
                            </div>
                            <input type="text" name="identity_number" class="form-control input-sm" placeholder="姓名"
                                   value="{{$identity_number}}">
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
<div class="row">
    <div class="col-xs-12">
        <div class="box box-danger">
            <!-- /.box-header -->
            <div class="box-body">
                <div id="example2_wrapper" class="dataTables_wrapper form-inline dt-bootstrap">
                    <div class="row">
                        <div class="col-sm-6"></div>
                        <div class="col-sm-6"></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-12" style="overflow-x: auto;">
                            <table id="example2" class="table table-bordered table-hover dataTable"
                                   role="grid" aria-describedby="example2_info">
                                <thead>
                                <tr>
                                    <th>用户UID</th>
                                    <th>手机号</th>
                                    <th>姓名</th>
                                    <th>身份证</th>
                                    <th>性别</th>
                                    <th>有效期</th>
                                    <th>签发</th>
                                    <th>正面照</th>
                                    <th>背面照</th>
                                    <th>实名时间</th>
                                </tr>
                                </thead>
                                <tbody id="galley">
                                @foreach($info as $item)
                                    <?php
                                    if (!empty($item['idcard_information_pic'])) {
                                        $idcard_information_pic = \App\Components\AliyunOSSUtil::getAccessUrl(\App\Components\AliyunOSSUtil::getLoanBucket(), $item['idcard_information_pic']);
                                    } else {
                                        $idcard_information_pic = '';
                                    }

                                    if (!empty($item['idcard_national_pic'])) {
                                        $idcard_national_pic = \App\Components\AliyunOSSUtil::getAccessUrl(\App\Components\AliyunOSSUtil::getLoanBucket(), $item['idcard_national_pic']);
                                    } else {
                                        $idcard_national_pic = '';
                                    }

                                    ?>
                                    <tr>
                                        <td>{{$item['uid']}}</td>
                                        <td>{{$item['phone']}}</td>
                                        <td>{{$item['full_name']}}</td>
                                        <td>{{$item['identity_number']}}</td>
                                        <td>{{$item['gender']}}</td>
                                        <td>{{$item['start_date']}}~{{$item['end_date']}}</td>
                                        <td>{{$item['issuing_authority']}}</td>
                                        <td>
                                            @if(empty($idcard_information_pic))
                                                <span class="glyphicon glyphicon-picture"
                                                      style="cursor: pointer;"></span>
                                            @else
                                                <img style="cursor: pointer;width: 10px;"
                                                     data-original="{{ $idcard_information_pic }}"
                                                     src="{{ $idcard_information_pic }}">
                                            @endif

                                        </td>
                                        <td>
                                            @if(empty($idcard_national_pic))
                                                <span class="glyphicon glyphicon-picture"
                                                      style="cursor: pointer;"></span>
                                            @else
                                                <img style="cursor: pointer;width: 10px;"
                                                     data-original="{{ $idcard_national_pic }}"
                                                     src="{{ $idcard_national_pic }}">
                                            @endif
                                        </td>
                                        <td>{{substr($item['create_time'],0,10)}}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                                <tfoot>
                                <tr>
                                    <th>用户UID</th>
                                    <th>手机号</th>
                                    <th>姓名</th>
                                    <th>身份证</th>
                                    <th>性别</th>
                                    <th>有效期</th>
                                    <th>签发</th>
                                    <th>正面照</th>
                                    <th>背面照</th>
                                    <th>实名时间</th>
                                </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
            <div class="box-footer">
                {{$info->links()}}
            </div>
        </div>
    </div>
</div>
{!! Admin::js(admin_asset('plugins/viewerjs/dist/viewer.js')) !!}


<script type="text/javascript">
    window.addEventListener('DOMContentLoaded', function () {
        var galley = document.getElementById('galley');
        var viewer = new Viewer(galley, {
            url: 'data-original',
            toolbar: {
                oneToOne: true,

                prev: function () {
                    viewer.prev(true);
                },

                play: true,

                next: function () {
                    viewer.next(true);
                },

                download: function () {
                    const a = document.createElement('a');

                    a.href = viewer.image.src;
                    a.download = viewer.image.alt;
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                },
            },
        });
    });
</script>
