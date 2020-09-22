@extends('_base')

@section('css')

@endsection

@section('content')

<h1 style="margin-bottom: 25px;">
    Articles
</h1>

<div class="container">

    <div class="row">

        @foreach ($posts as $post)

            <div class="col-md-4">
                <h2>{{$post['title']}}</h2>
                <p>{{$post['excerpt']}}</p>
                <p><a class="btn btn-secondary" href="/articles/{{$post['slug']}}" role="button">View article »</a></p>
            </div>

        @endforeach

        @if (empty($posts))
            <p>None yet. Please check back again soon.</p>
        @endif

        <hr>
    </div>
</div> <!-- /container -->

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

        });
    </script>
@endsection
