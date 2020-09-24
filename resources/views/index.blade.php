@extends('_base')

@section('css')
    <link href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
@endsection

@section('jumbotron')
    <div class="jumbotron">
        <div class="container">
            <h1 class="display-4">RotoRanker</h1>
            <p style="font-size: 1rem;">Automated MLB pitcher and hitter rankings using unique blends of statistics and Statcast data. Updated daily.</p>
            <p>
                <a class="btn btn-primary btn-lg" href="/pitchers" role="button">View Pitcher Rankings »</a>
            </p>
            <p>
                <a class="btn btn-primary btn-lg" href="/hitters" role="button">View Hitter Rankings »</a>
            </p>
        </div>
    </div>
@endsection

@section('content')
    <div class="container">
        <div class="row">
            <div class="col-md-4">
                <h2><a href="/about">About RotoRanker</a></h2>
                <p>A background on the pitcher and hitter rankings found on this site.</p>
                <p><a class="btn btn-secondary" href="/about" role="button">View more »</a></p>
            </div>
            @foreach ($posts as $post)
                <div class="col-md-4">
                    <h2><a href="/articles/{{$post['slug']}}">{{$post['title']}}</a></h2>
                    <p>{{$post['excerpt']}}</p>
                    <p><a class="btn btn-secondary" href="/articles/{{$post['slug']}}" role="button">View more »</a></p>
                </div>
            @endforeach
            <hr>
        </div>
    </div>
@endsection
