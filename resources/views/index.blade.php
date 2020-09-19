@extends('_base')

@section('css')
    <link href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
@endsection

@section('content')
    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron">
        <div class="container">
            <h1 class="display-3">RotoRanker</h1>
            <p>Automated MLB pitcher and hitter rankings using unique blends of statistics and Statcast data. Updated daily.</p>
            <p>
                <a class="btn btn-primary btn-lg" href="/pitchers" role="button">View Pitcher Rankings »</a>
                <a class="btn btn-primary btn-lg" href="/hitters" role="button" style="margin-left: 20px">View Hitter Rankings »</a>
            </p>
        </div>
    </div>

{{--    <div class="container">--}}
{{--        <!-- Example row of columns -->--}}
{{--        <div class="row">--}}
{{--            <div class="col-md-4">--}}
{{--                <h2>Heading</h2>--}}
{{--                <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>--}}
{{--                <p><a class="btn btn-secondary" href="#" role="button">View details »</a></p>--}}
{{--            </div>--}}
{{--            <div class="col-md-4">--}}
{{--                <h2>Heading</h2>--}}
{{--                <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>--}}
{{--                <p><a class="btn btn-secondary" href="#" role="button">View details »</a></p>--}}
{{--            </div>--}}
{{--            <div class="col-md-4">--}}
{{--                <h2>Heading</h2>--}}
{{--                <p>Donec sed odio dui. Cras justo odio, dapibus ac facilisis in, egestas eget quam. Vestibulum id ligula porta felis euismod semper. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus.</p>--}}
{{--                <p><a class="btn btn-secondary" href="#" role="button">View details »</a></p>--}}
{{--            </div>--}}
{{--        </div>--}}
{{--        <hr>--}}
{{--    </div> <!-- /container -->--}}

@endsection