@extends('_base')

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/jq-3.6.0/dt-1.13.4/fc-4.2.2/fh-3.3.2/r-2.4.1/datatables.min.css"/>
    <link rel="canonical" href="{{route('pitcher_ranks', [$year, $position])}}" />
@endsection

@section('content')
    <h1>
        Pitcher Rankings
    </h1>

    @if ($position == 'sp')
        <p>Minimum 3.0 IP per appearance, {{ $min_ip }} total IP</p>
    @else
        <p>Less than 3.0 IP per appearance, at least {{ $min_ip }} total IP</p>
    @endif

    <div class="row">
        <div class="col-xl-1 col-lg-2 col-md-3">
            <label for="positionSelect">Position</label>
            <select class="form-control form-control-sm" id="positionSelect" name="positionSelect">
                <option value="sp">SP</option>
                <option value="rp" @if ($position == 'rp') selected @endif>RP</option>
            </select>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4">
            <label for="yearSelect">Year</label>
            <select class="form-control form-control-sm" id="yearSelect" name="yearSelect">
                @foreach ($years as $oneYear)
                    <option value="{{$oneYear}}" @if ($year == $oneYear) selected @endif>{{$oneYear}}</option>
                @endforeach
            </select>
        </div>

        @if ($position == 'sp')
        <div class="col-xl-1 col-lg-1 col-md-2 col-sm-2">
            <label for="ip_minimum">IP Min</label>
            <input type="text" id="ip_minimum" class="form-control form-control-sm">
        </div>
        @else
            <input type="hidden" id="ip_minimum">
        @endif

        <div class="col-xl-8 col-lg-7 col-md-5" style="text-align: right">
            <div>Last updated: @if (date('G') > 7) {{ date('F j, Y') }}@else {{ date('F j, Y', strtotime('yesterday')) }}@endif</div>
            <div id="playerSets" style="margin-bottom: 5px"></div>
            <div id="saveSet" style="margin-bottom: 5px">Save current search as <input type="text" id="saveSetName"><button id="saveSetBtn">Save</button><button id="deleteSetBtn">Delete</button></div>
            <div style="margin-bottom: 5px">Search: <input type="text" id="search"></div>
            <br />
        </div>
    </div>

    <div class="table-responsive-sm">
        <div style="text-align: center">Out of <span class="numPitchers"></span> eligible pitchers</div>
        <table id="pitchers" class="table-bordered table-hover table-sm" style="font-size: 12px; line-height: 16px; margin: 0 auto;">
            <thead>
            <tr>
                <th class="all">Rank</th>
                <th class="all" style="width: 125px">Name</th>
                <th class="desktop" style="border-right: 1px solid black;">Age</th>
                <th class="desktop">IP</th>
                <th class="desktop" style="border-right: 1px solid black;">IPpG</th>
                <th class="desktop" style="border-right: 1px solid black;">KpG</th>
                <th class="desktop">ERA</th>
                <th class="desktop" style="border-right: 1px solid black;">WHIP</th>
                <th class="all">K%</th>
                <th class="desktop">BB%</th>
                <th class="desktop" style="border-right: 1px solid black;">K-BB%</th>
                <th class="desktop" style="border-right: 1px solid black;">SwStr%</th>
                <th class="all" style="border-right: 1px solid black;">GB%</th>
                <th class="desktop" style="border-right: 1px solid black;">CSW%</th>
                <th class="all" style="border-right: 1px solid black;">Velo</th>
{{--                <th class="desktop">IPpG Rank</th>--}}

                @if ($position == 'sp')
                    <th class="desktop">KpG Rank</th>
                @else
                    <th class="desktop">K% Rank</th>
                @endif

                <th class="desktop" style="border-right: 1px solid black;">xERA Rank</th>
                <th class="all" style="font-weight: bold">Rank</th>
            </tr>
            </thead>
            <tbody>

            </tbody>
        </table>
        <div style="text-align: center">Out of <span class="numPitchers"></span> eligible pitchers</div>
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
                    data.pitchers[name] = {
                        "name": name,
                        "players": string
                    };
                    drawPlayerSetButtons(data.pitchers);
                    localStorage.setItem('data', JSON.stringify(data));
                }
            });

            $('#deleteSetBtn').on('click', function() {
                var name = $('#saveSetName').val();
                delete data.pitchers[name];
                localStorage.setItem('data', JSON.stringify(data));
                drawPlayerSetButtons(data.pitchers);
            });

            function drawPlayerSetButtons(players)
            {
                $("#playerSets").empty();
                $.each(players, function(key, pitcher) {
                    $("#playerSets").append(
                        "<button id='"+pitcher.name+"' class='playerSetBtn'>"+pitcher.name+"</button>"
                    );
                    $("#"+pitcher.name).on('click', function() {
                        $("#pitchers_filter input").val(pitcher.players);
                        $('#saveSetName').val(pitcher.name);
                        $('#search').val(pitcher.players);
                        filterCurrentSearch();
                    });
                });
            }

            drawPlayerSetButtons(data.pitchers);

            if ($('#positionSelect').val() == 'sp' && $.cookie('ip_minimum') !== 'NaN' && typeof $.cookie('ip_minimum') === 'string') {
                $('#ip_minimum').val($.cookie('ip_minimum'));
            } else {
                $('#ip_minimum').val({{ $min_ip }});
            }

            $('.playerSetBtn').eq(0).click();

            $('#positionSelect, #yearSelect').change(function() {
                var year = $('#yearSelect').val();
                var position = $('#positionSelect').val();
                window.location.href = '/pitchers/'+year+'/'+position;
            });

            $('#search').on('change keyup', function() {
                filterCurrentSearch();
            });

            $('#ip_minimum').on('change keyup', function(e) {
                $.cookie('ip_minimum', parseFloat($('#ip_minimum').val()), { expires: 20*365 });
                updateData();
            });

            function filterCurrentSearch() {
                if ($('#search').val() == '') {
                    $('#pitchers tbody tr').show();
                    return true;
                }
                $('#pitchers tbody tr').show()
                var names = $('#search').val().split('|').map(function(item) {
                    return item.trim();
                });
                var rank = 1;
                $('#pitchers tbody tr').each(function() {
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

                    $('.numPitchers').html(rank-1);
                });
            }

            function updateData() {
                $.get("/api/pitchers/{{ $year }}/{{ $position }}/"+$('#ip_minimum').val(), function(data) {
                    $('#pitchers tbody tr').remove();
                    data = JSON.parse(data);
                    var i = 1;
                    data.forEach(function(stat) {
                        insertRow(stat, i);
                        i++;
                    });
                    $('.numPitchers').html(data.length);
                }).done(function() {
                    filterCurrentSearch();
                });
            }

            function insertRow(stat, rank) {
                $('#pitchers tbody').append(
                    "<tr>" +
                    '<td class="align-middle" style="font-size: 1.2em;">'+rank+"</td>"+
                    '<td class="align-middle" style="text-align: left; font-size: 1.2em; width: 150px; letter-spacing: 0;"><a href="/pitcher/'+stat['player']['slug']+'" class="pitcherNameLink">'+stat['player']['name']+'</a></td>'+
                    '<td class="align-middle" style="border-right: 1px solid black;">'+stat['age']+"</td>"+
                    '<td class="align-middle ip">'+stat['ip']+"</td>"+
                    '<td class="align-middle ip-per-g" style="border-right: 1px solid black;">'+stat['ip_per_g']+"</td>"+
                    '<td class="align-middle" style="border-right: 1px solid black;">'+stat['k_per_game']+"</td>"+
                    '<td class="align-middle">'+stat['era']+"</td>"+
                    '<td class="align-middle" style="border-right: 1px solid black;">'+stat['whip']+"</td>"+
                    '<td class="align-middle">'+stat['k_percentage']+"</td>"+
                    '<td class="align-middle">'+stat['bb_percentage']+"</td>"+
                    '<td class="align-middle" style="border-right: 1px solid black;">'+stat['kbb_percentage']+"</td>"+
                    '<td class="align-middle" style="border-right: 1px solid black;">'+stat['swstr_percentage']+"</td>"+
                    '<td class="align-middle" style="border-right: 1px solid black;">'+stat['gb_percentage']+"</td>"+
                    '<td class="align-middle" style="border-right: 1px solid black;">'+stat['csw']+"</td>"+
                    '<td class="align-middle" style="border-right: 1px solid black;">'+stat['velo']+"</td>"+
                    '<td class="align-middle">'+stat['k_rank']+"</td>"+
                    '<td class="align-middle" style="border-right: 1px solid black;">'+stat['xwoba_rank']+"</td>"+
                    '<td class="align-middle" style="font-size: 1.2em;">'+rank+"</td>"+
                    '</tr>');
            }

            updateData();
        });
    </script>
@endsection
