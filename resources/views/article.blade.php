@extends('_base')

@section('css')

@endsection

@section('content')

    <h1>
        {{$post['title']}}
    </h1>

    {!! $post['content'] !!}

@endsection

@section('javascript')
    <script type="text/javascript">
        $(document).ready(function() {

        });
    </script>
@endsection
