<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ $page_title ?? getenv('APP_NAME') }}</title>

    <link href="/css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    @yield('css')
</head>
<body>

<div class="container">
    <div class="row">
        <div class="col">
            @yield('content')
        </div>
    </div>
</div>

<script type="text/javascript" src="/js/app.js"></script>
@yield('javascript')

</body>
</html>
