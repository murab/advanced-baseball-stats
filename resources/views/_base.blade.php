<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>{{ isset($page_title) ? $page_title . ' | ' .  getenv('APP_NAME') : getenv('APP_NAME') }}</title>

    <link href="{{ asset('/css/app.css') }}" rel="stylesheet" type="text/css">
    <link href="{{ asset('/css/styles.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet">

    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=UA-178664100-1"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'UA-178664100-1');
    </script>
    
    <script data-ad-client="ca-pub-9421705552575420" async src="https://pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>

    @yield('css')
</head>
<body>

@include('_nav')

<main role="main">
    @yield('jumbotron')
    <div class="container" style="margin-top: 25px">
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
