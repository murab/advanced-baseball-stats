<nav class="navbar navbar-expand-lg navbar-dark bg-dark">
    <a class="navbar-brand" style="font-size: 1.25rem" href="/">{{ env('APP_NAME') }}</a>
    <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="navbarSupportedContent">
        <ul class="navbar-nav mr-auto">
            <li class="nav-item @if (!empty($page) && $page == 'pitchers') active @endif">
                <a class="nav-link navbar-brand" href="/pitchers">Pitchers</a>
            </li>
            <li class="nav-item @if (!empty($page) && $page == 'hitters') active @endif">
                <a class="nav-link navbar-brand" href="/hitters">Hitters</a>
            </li>
            <li class="nav-item @if (!empty($page) && $page == 'articles') active @endif">
                <a class="nav-link navbar-brand" href="/articles">Articles</a>
            </li>
            <li class="nav-item @if (!empty($page) && $page == 'about') active @endif">
                <a class="nav-link navbar-brand" href="/about">About</a>
            </li>
        </ul>
{{--        <form class="form-inline my-2 my-lg-0">--}}
{{--            <input class="form-control mr-sm-2" type="search" placeholder="Search" aria-label="Search">--}}
{{--            <button class="btn btn-outline-success my-2 my-sm-0" type="submit">Search</button>--}}
{{--        </form>--}}
                <div class="form-inline my-2 my-lg-0">
                    <span style="color: darkgray">Suggestions? Tweet me!</span>
                    <p class="my-sm-0 my-2"><a class="nav-link" href="https://twitter.com/therotoranker">@TheRotoRanker</a></p>
                </div>
    </div>
</nav>
