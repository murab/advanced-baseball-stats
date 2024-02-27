<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" style="font-size: 1.25rem" href="/">{{ env('APP_NAME') }}</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item @if (!empty($page) && $page == 'pitchers' && $position == 'sp') active @endif">
                <a class="nav-link navbar-brand" href="/pitchers">Starting Pitchers</a>
            </li>
            <li class="nav-item @if (!empty($page) && $page == 'pitchers' && $position == 'rp') active @endif">
                <a class="nav-link navbar-brand" href="/pitchers/@php echo date('Y'); @endphp/rp">Relief Pitchers</a>
            </li>
            <li class="nav-item @if (!empty($page) && $page == 'hitters') active @endif">
                <a class="nav-link navbar-brand" href="/hitters">Hitters</a>
            </li>
            <li class="nav-item @if (!empty($page) && $page == 'about') active @endif">
                <a class="nav-link navbar-brand" href="/about">About</a>
            </li>
        </ul>

        <div class="form-inline my-2 my-lg-0">
            <p class="my-sm-0 my-2">
                <form method="post" action="/gotoplayer" name="gotoplayerForm">
                    @csrf
                    <input type="text" id="gotoplayer" name="gotoplayer" class="form-control" placeholder="Go to Player Page">
                </form>
            </p>
        </div>
    </div>
</nav>
