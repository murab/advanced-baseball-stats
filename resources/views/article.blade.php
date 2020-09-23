@extends('_base')

@section('css')

@endsection

@section('content')

    <div class="article">
        <h1>
            {{$post['title']}}
        </h1>

        {!! $post['content'] !!}
    </div>

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

        });
    </script>
@endsection
