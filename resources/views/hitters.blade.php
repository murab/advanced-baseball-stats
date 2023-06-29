@extends('_base')

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/jq-3.6.0/dt-1.13.4/fc-4.2.2/fh-3.3.2/r-2.4.1/datatables.min.css"/>
    <link rel="canonical" href="{{route('hitter_ranks', [$year])}}" />
@endsection

@section('content')
    <h1>
        Hitter Rankings
    </h1>

    <p>Average PA in {{ $year }}: {{ $min_pa }}</p>

    <div class="row">
        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2">
            <label for="yearSelect">Year</label>
            <select class="form-control form-control-sm" id="yearSelect" name="yearSelect">
                @foreach ($years as $oneYear)
                    <option value="{{$oneYear}}" @if ($year == $oneYear) selected @endif>{{$oneYear}}</option>
                @endforeach
            </select>
        </div>

        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2">
            <label for="pa_minimum">PA Min</label>
            <input type="text" id="pa_minimum" class="form-control form-control-sm">
        </div>

        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2">
            <label for="pa_per_g_minimum">PA/G Min</label>
            <input type="text" id="pa_per_g_minimum" class="form-control form-control-sm">
        </div>

        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2">
            <label for="sb_minimum">SB Min</label>
            <input type="text" id="sb_minimum" class="form-control form-control-sm">
        </div>

        <div class="col-xl-4 col-lg-4 col-md-4 col-sm-4" style="text-align: right">
            <div>Last updated: @if (date('G') > 7) {{ date('F j, Y') }}@else {{ date('F j, Y', strtotime('yesterday')) }}@endif</div>
            <div id="playerSets" style="margin-bottom: 5px"></div>
            <div id="saveSet" style="margin-bottom: 5px">Save current search as <input type="text" id="saveSetName"><button id="saveSetBtn">Save</button><button id="deleteSetBtn">Delete</button></div>
            <div style="margin-bottom: 5px">Search: <input type="text" id="search"></div>
            <br />
        </div>

    </div>

    <div class="table-responsive-sm">
        <div style="text-align: center">Out of <span class="numHitters"></span> eligible hitters</div>
        <table id="hitters" class="table-bordered table-hover table-sm" style="font-size: 12px; line-height: 18px; margin: 0 auto;">
            <thead>
            <tr>
                <th>Rank</th>
                <th style="width: 125px">Name</th>
                <th style="border-right: 1px solid black;">Age</th>
                <th class="desktop">PA</th>
                <th class="desktop" style="border-right: 1px solid black;">PA/G</th>
                <th>R</th>
                <th class="all" >AVG</th>
                <th class="all" >HR</th>
                <th class="all" >RBI</th>
                <th class="all"  style="border-right: 1px solid black;">SB</th>
                <th>BB%</th>
                <th style="border-right: 1px solid black;">K%</th>
                <th style="border-right: 1px solid black;">SwStr%</th>
                <th style="border-right: 1px solid black;">Sprint Rank</th>
                <th style="border-right: 1px solid black;">Brls/PA Rank</th>
                <th style="border-right: 1px solid black;">HardPullFB/G</th>
                <th>HardPullFB/G Rank</th>
                <th style="border-right: 1px solid black;">xwOBA Rank</th>
                <th style="font-weight: bold; border-right: 1px solid black;">Def</th>
                <th class="all" style="border-right: 1px solid black;">wRC+ vs. L</th>
                <th class="all">wRC+</th>
            </tr>
            </thead>
            <tbody>
{{--            @foreach($stats as $key => $stat)--}}
{{--                <tr>--}}
{{--                    <td class="align-middle" style="font-size: 1.2em;">{{$key+1}}</td>--}}
{{--                    <td class="align-middle" style="text-align: left; font-size: 1.2em; width: 150px; letter-spacing: 0;"><a href="{{route('hitter', $stat->player['slug'])}}" class="hitterNameLink">{{$stat->player['name']}}</a></td>--}}
{{--                    <td class="align-middle" style="border-right: 1px solid black;">{{$stat['age']}}</td>--}}
{{--                    <td class="align-middle pa">{{$stat['pa']}}</td>--}}
{{--                    <td class="align-middle pa-per-g" style="border-right: 1px solid black;">{{ltrim(number_format($stat['pa_per_g'], 1))}}</td>--}}
{{--                    <td class="align-middle">{{$stat['r']}}</td>--}}
{{--                    <td class="align-middle">{{ltrim(number_format($stat['avg'], 3),"0")}}</td>--}}
{{--                    <td class="align-middle">{{$stat['hr']}}</td>--}}
{{--                    <td class="align-middle">{{$stat['rbi']}}</td>--}}
{{--                    <td class="align-middle sb" style="border-right: 1px solid black;">{{$stat['sb']}}</td>--}}
{{--                    <td class="align-middle">{{number_format($stat['bb_percentage'], 1)}}</td>--}}
{{--                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['k_percentage'], 1)}}</td>--}}
{{--                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['swstr_percentage'], 1)}}</td>--}}
{{--                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['sprint_speed_rank'])}}</td>--}}
{{--                    <td class="align-middle">{{number_format($stat['brls_rank'])}}</td>--}}
{{--                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['xwoba_rank'])}}</td>--}}
{{--                    <td class="align-middle" style="border-right: 1px solid black; @if ($stat['def'] > 0) color: green; font-weight: bold @endif">{{number_format($stat['def'],1)}}</td>--}}
{{--                    <td class="align-middle" style="border-right: 1px solid black;">{{$stat['vsleft_wrc_plus']}}</td>--}}
{{--                    <td class="align-middle" style=" font-size: 1.2em;  @if ($stat['wrc_plus'] > 110) font-weight: bold; color: green @endif">{{$stat['wrc_plus']}}</td>--}}
{{--                </tr>--}}
{{--            @endforeach--}}
            </tbody>
        </table>
        <div style="text-align: center">Out of <span class="numHitters"></span> eligible hitters</div>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/jq-3.6.0/dt-1.13.4/fc-4.2.2/fh-3.3.2/r-2.4.1/datatables.min.js"></script>
    <script type="text/javascript" src="https://cdnjs.cloudflare.com/ajax/libs/jquery-cookie/1.4.1/jquery.cookie.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            var data = localStorage.getItem('data');

            if (!data) {
                data = {
                    "hitters": {},
                    "pitchers": {}
                };
            } else {
                data = JSON.parse(data);
            }

            $('#saveSetBtn').on('click', function(e) {
                var name = $('#saveSetName').val();
                var string = $('#search').val();
                if (string !== '') {
                    data.hitters[name] = {
                        "name": name,
                        "players": string
                    };
                    drawPlayerSetButtons(data.hitters);
                    localStorage.setItem('data', JSON.stringify(data));
                }
            });

            $('#deleteSetBtn').on('click', function() {
                var name = $('#saveSetName').val();
                delete data.hitters[name];
                localStorage.setItem('data', JSON.stringify(data));
                drawPlayerSetButtons(data.hitters);
            });

            function drawPlayerSetButtons(players)
            {
                $("#playerSets").empty();
                $.each(players, function(key, hitter) {
                    $("#playerSets").append(
                        "<button id='"+hitter.name+"' class='playerSetBtn'>"+hitter.name+"</button>"
                    );
                    $("#"+hitter.name).on('click', function() {
                        $("#hitters_filter input").val(hitter.players);
                        $('#saveSetName').val(hitter.name);
                        $('#search').val(hitter.players);
                        filterCurrentSearch();
                    });
                });
            }

            drawPlayerSetButtons(data.hitters);

            if ($.cookie('pa_minimum') !== 'NaN' && typeof $.cookie('pa_minimum') === 'string') {
                $('#pa_minimum').val($.cookie('pa_minimum'));
            } else {
                $('#pa_minimum').val({{ $min_pa }});
            }
            if ($.cookie('pa_per_g_minimum') !== 'NaN' && typeof $.cookie('pa_per_g_minimum') === 'string') {
                $('#pa_per_g_minimum').val($.cookie('pa_per_g_minimum'));
            } else {
                $('#pa_per_g_minimum').val(0);
            }
            if ($.cookie('sb_minimum') !== 'NaN' && typeof $.cookie('sb_minimum') === 'string') {
                $('#sb_minimum').val($.cookie('sb_minimum'));
            } else {
                $('#sb_minimum').val(0);
            }

            $('.playerSetBtn').eq(0).click();

            $('#positionSelect, #yearSelect').change(function() {
                var year = $('#yearSelect').val();
                var position = $('#positionSelect').val();
                window.location.href = '/hitters/'+year;
            });

            $('#search').on('change keyup', function() {
                filterCurrentSearch();
            });

            $('#pa_minimum, #pa_per_g_minimum, #sb_minimum').on('change keyup', function(e) {
                $.cookie('pa_minimum', parseFloat($('#pa_minimum').val()), { expires: 20*365 });
                $.cookie('pa_per_g_minimum', parseFloat($('#pa_per_g_minimum').val()), { expires: 20*365 });
                $.cookie('sb_minimum', parseFloat($('#sb_minimum').val()), { expires: 20*365 });
                updateData();
            });

            function filterCurrentSearch() {
                if ($('#search').val() == '') {
                    $('#hitters tbody tr').show();
                    return true;
                }
                $('#hitters tbody tr').show()
                var names = $('#search').val().split('|').map(function(item) {
                    return item.trim();
                });
                var rank = 1;
                $('#hitters tbody tr').each(function() {
                    if ($(this).hasClass('exclude')) {
                        $(this).hide();
                        return true;
                    }
                    var name = $(this).find('td a').eq(0).html();
                    var wasFound = false;
                    names.forEach(function(one) {
                        if (!$(this).hasClass('exclude') && name.toLowerCase().includes(one.toLowerCase())) {
                            wasFound = true;
                            return false;
                        }
                    });
                    if (wasFound) {
                        $(this).find('td').eq(0).html(rank);
                    } else {
                        $(this).hide();
                    }
                    rank++;

                    $('.numHitters').html(rank-1);
                });
            }

            function updateData() {
                $.get("/api/hitters/{{ $year }}/"+$('#pa_minimum').val()+"/"+$('#pa_per_g_minimum').val()+"/"+$('#sb_minimum').val(), function(data) {
                    $('#hitters tbody tr').remove();
                    data = JSON.parse(data);
                    var i = 1;
                    data.forEach(function(stat) {
                        insertRow(stat, i);
                        i++;
                    });
                    $('.numHitters').html(data.length);
                }).done(function() {
                    filterCurrentSearch();
                });
            }

            function insertRow(stat, rank) {
                $('#hitters tbody').append(
                    "<tr>" +
                        '<td class="align-middle" style="font-size: 1.2em;">'+rank+"</td>"+
                    '<td class="align-middle" style="text-align: left; font-size: 1.2em; width: 150px; letter-spacing: 0;"><a href="/hitter/'+stat['player']['slug']+'" class="hitterNameLink">'+stat['player']['name']+'</a></td>'+
                '<td class="align-middle" style="border-right: 1px solid black;">'+stat['age']+"</td>"+
                '<td class="align-middle pa">'+stat['pa']+"</td>"+
                '<td class="align-middle pa-per-g" style="border-right: 1px solid black;">'+stat['pa_per_g']+"</td>"+
                '<td class="align-middle">'+stat['r']+"</td>"+
                '<td class="align-middle">'+stat['avg']+"</td>"+
                '<td class="align-middle">'+stat['hr']+"</td>"+
                '<td class="align-middle">'+stat['rbi']+"</td>"+
                '<td class="align-middle sb" style="border-right: 1px solid black;">'+stat['sb']+"</td>"+
                '<td class="align-middle">'+stat['bb_percentage']+"</td>"+
                '<td class="align-middle" style="border-right: 1px solid black;">'+stat['k_percentage']+"</td>"+
                '<td class="align-middle" style="border-right: 1px solid black;">'+stat['swstr_percentage']+"</td>"+
                '<td class="align-middle" style="border-right: 1px solid black;">'+stat['sprint_speed_rank']+"</td>"+
                '<td class="align-middle" style="border-right: 1px solid black;">'+stat['brls_rank']+"</td>"+
                '<td class="align-middle" style="border-right: 1px solid black;">'+Number(stat['pulled_flyballs_per_g']).toPrecision(2)+"</td>"+
                '<td class="align-middle">'+stat['pulled_fb_g_rank']+"</td>"+
                '<td class="align-middle" style="border-right: 1px solid black;">'+stat['xwoba_rank']+"</td>"+
                '<td class="align-middle" style="border-right: 1px solid black; '+(stat['def'] > 0 ? 'color: green; font-weight: bold' : '')+' ">'+stat['def']+"</td>"+
                '<td class="align-middle" style="border-right: 1px solid black;">'+stat['vsleft_wrc_plus']+"</td>"+
                '<td class="align-middle" style=" font-size: 1.2em;  '+(stat['wrc_plus'] > 110 ? 'font-weight: bold; color: green' : '')+'">'+stat['wrc_plus']+"</td>"+
                '</tr>');
            }

            updateData();
        });
    </script>
@endsection
