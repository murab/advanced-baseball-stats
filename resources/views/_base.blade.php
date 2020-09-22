<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ isset($page_title) ? $page_title . ' | ' .  getenv('APP_NAME') : getenv('APP_NAME') }}</title>

    <link href="/css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <script data-ad-client="ca-pub-9421705552575420" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>

    @yield('css')
</head>
<body>

@include('_nav')

<main role="main">
    @yield('jumbotron')
    <div class="container">
        <div class="row">
            <div class="col">
                @yield('content')
            </div>
        </div>
    </div>
</main>

<footer class="container" style="text-align: center">
    <p><small>© {{ Carbon\Carbon::today()->format('Y') }}</small></p>
</footer>

<script type="text/javascript" src="/js/app.js"></script>
@yield('javascript')

</body>
</html>
