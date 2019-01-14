@extends('web.common.base')
@section('title',  '首页-大圣-教育培训助手')
@section('content')
    <!-- end header -->
    <section id="banner">
        <!-- Slider -->
        <div id="main-slider">
            <ul class="slides">
                <li>
                    <img src="{{ URL::asset('web/img/slides/1.png') }}" alt="" style="width: 100%"/>
                </li>
                <li>
                    <img src="{{ URL::asset('web/img/slides/2.png') }}" alt="" style="width: 100%"/>
                </li>
            </ul>
        </div>
        <!-- end slider -->
    </section>
    <section id="content">
        <div class="container">
            <div class="row">
                <div class="col-md-12">
                    <div class="aligncenter">
                        <h2 class="aligncenter">有大圣,无所不能</h2>
                    </div>
                    <br/>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-4 info-blocks">
                    <i class="icon-info-blocks material-icons">book_open_variant</i>
                    <div class="info-blocks-in">
                        <h3>学员安心培训</h3>
                        <p>安心在培训机构参加学习，毕业后未成功就业，享受最高100%的学费帮扶金。</p>
                    </div>
                </div>
                <div class="col-sm-4 info-blocks">
                    <i class="icon-info-blocks material-icons">input</i>
                    <div class="info-blocks-in">
                        <h3>机构专注教学</h3>
                        <p>教育培训机构时刻关注教学表现和就业进展，大圣分期帮协助机构处理就业事务，提升培训效果，破解行业人才难题。</p>
                    </div>
                </div>
                <div class="col-sm-4 info-blocks">
                    <i class="icon-info-blocks material-icons">repeat</i>
                    <div class="info-blocks-in">
                        <h3>行业平稳发展</h3>
                        <p>学员、机构、大圣分期三者共同努力，各司其职，提升培训和就业质量，促进整个教育培训行业平稳发展。</p>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
