@extends('_base')

@section('css')
    <link href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
@endsection

@section('content')
    <!-- Main jumbotron for a primary marketing message or call to action -->
    <div class="jumbotron">
        <div class="container">
            <h1 class="display-4">RotoRanker</h1>
            <p>Automated MLB pitcher and hitter rankings using unique blends of statistics and Statcast data. Updated daily.</p>
            <p>
                <a class="btn btn-primary btn-lg" href="/pitchers" role="button">View Pitcher Rankings »</a>
            </p>
            <p>
                <a class="btn btn-primary btn-lg" href="/hitters" role="button">View Hitter Rankings »</a>
            </p>
        </div>
    </div>

    <div class="container">

        <div class="row">

            @foreach ($posts as $post)

                <div class="col-md-4">
                    <h2>{{$post['title']}}</h2>
                    <p>Donec id elit non mi porta gravida at eget metus. Fusce dapibus, tellus ac cursus commodo, tortor mauris condimentum nibh, ut fermentum massa justo sit amet risus. Etiam porta sem malesuada magna mollis euismod. Donec sed odio dui. </p>
                    <p><a class="btn btn-secondary" href="/articles/{{$post['slug']}}" role="button">View details »</a></p>
                </div>

            @endforeach

            <hr>
        </div>
    </div> <!-- /container -->

@endsection
