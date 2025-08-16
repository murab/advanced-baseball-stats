@extends('_base')

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/jq-3.6.0/dt-1.13.4/fc-4.2.2/fh-3.3.2/r-2.4.1/datatables.min.css"/>
    <link rel="canonical" href="{{route('pitcher_ranks', [$year, $position])}}" />
    <style>
        @media (max-width: 540px) {
            .ip, .name { border-right: 1px solid black !important; }
            .no-border-right-mobile { border-right: none; !important}
        }
        @media (min-width: 541px) {
            .whip { border-right: 1px solid black !important; }
            .border-right-desktop { border-right: 1px solid black !important; }
        }
    </style>
@endsection

@section('content')
    <h1>
        Pitcher Rankings <button id="expand" class="d-sm-none btn btn-primary">Expand</button>
    </h1>

    @if ($position == 'sp')
        <p>Average IP for SP in {{ $year }}: {{ $min_ip }}</p>
    @else
        <p>Average IP for RP in {{$year}}: {{ $min_ip }}</p>
    @endif

    <div class="row">
        <div class="col-xl-1 col-lg-2 col-md-3 col-4 mb-3">
            <label class="mb-0" for="positionSelect">Position</label>
            <select class="form-control form-control-sm" id="positionSelect" name="positionSelect">
                <option value="sp">SP</option>
                <option value="rp" @if ($position == 'rp') selected @endif>RP</option>
            </select>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4 col-4">
            <label class="mb-0"  for="yearSelect">Year</label>
            <select class="form-control form-control-sm" id="yearSelect" name="yearSelect">
                @foreach ($years as $oneYear)
                    <option value="{{$oneYear}}" @if ($year == $oneYear) selected @endif>{{$oneYear}}</option>
                @endforeach
            </select>
        </div>

        @if ($position == 'sp')
        <div class="col-lg-1 col-sm-2 col-4">
            <label class="mb-0"  for="ip_minimum">IP Min</label>
            <input type="text" id="ip_minimum" class="form-control form-control-sm">
        </div>
        @else
            <input type="hidden" id="ip_minimum">
        @endif

        <div class="col-sm-2 offset-sm-4 col-6">
            <label class="mb-0"  for="saveSetName">Save search as</label>
            <input type="text" class="form-control form-control-sm" id="saveSetName">
            <button class="btn btn-outline-secondary btn-sm mt-1 mr-1" id="saveSetBtn">Save</button><button class="btn btn-outline-secondary mt-1 btn-sm" id="deleteSetBtn">Delete</button>
        </div>
        <div class="col-sm-2 col-6">
            <label class="mb-0"  for="search">Search</label>
            <input class="form-control-sm form-control" type="text" id="search">
            <span class="float-right" id="playerSets"></span>
        </div>
    </div>

    <div class="row">
        <div class="col-sm-12" style="padding-right: 0; padding-left: 0">
        <div style="text-align: center">Out of <span class="numPitchers"></span> eligible pitchers</div>
        <table id="pitchers" class="table-bordered table-hover table-sm" style="font-size: 12px; line-height: 16px; margin: 0 auto;">
            <thead>
            <tr>
                <th class="">Rank</th>
                <th class="name" style="width: 125px">Name</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">Age</th>
                <th class="ip">IP</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">IPpG</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">KpG</th>
                <th class="">ERA</th>
                <th class="" style="border-right: 1px solid black;">WHIP</th>
                <th class="d-none d-md-table-cell">K%</th>
                <th class="d-none d-md-table-cell">BB%</th>
                <th class="" style="border-right: 1px solid black;">K-BB%</th>
                <th class="no-border-right-mobile border-right-desktop" style="">SwStr%</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">GB%</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">CSW%</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">Stuff+</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">Velo</th>
{{--                <th class="desktop">IPpG Rank</th>--}}

                @if ($position == 'sp')
                    <th class="d-none d-md-table-cell">KpG<br>Rank</th>
                @else
                    <th class="d-none d-md-table-cell">K%<br>Rank</th>
                @endif

                <th class="xera d-none d-md-table-cell">xERA<br>Rank</th>
                @if ($position == 'sp')
                    <th class="d-none d-md-table-cell whip">WHIP<br>Rank</th>
                @endif
                <th class="d-none d-md-table-cell avg_rank" style="font-weight: bold">Rank</th>
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

            $.ajax({
                url: '/api/lists/get',
                data: data,
                type: 'POST',
                success: function(resp) {
                    var parsed = JSON.parse(resp);
                    if (parsed.hitters || parsed.pitchers) {
                        data = parsed;
                        localStorage.setItem('data', resp);
                    }
                },
                async: false
            });

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

                    $.post('/api/lists/save', data);
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
                        "<button id='"+pitcher.name+"' class='playerSetBtn btn btn-outline-secondary mt-1 ml-1 btn-sm'>"+pitcher.name+"</button>"
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

            $('#ip_minimum').val({{ $min_ip }});

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
                // $.cookie('ip_minimum', parseFloat($('#ip_minimum').val()), { expires: 20*365 });
                updateData();
            });

            $('#ip_minimum').on('blur', function(e) {
                if ($(this).val() == '') { $(this).val(0); }
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
                var kbb_style = '';
                if ('{{$position}}' == 'sp' && stat['kbb_percentage'] >= 17.5 || '{{$position}}' == 'rp' && stat['kbb_percentage'] >= 20) { kbb_style += 'color: green; '; }
                if ('{{$position}}' == 'sp' && stat['kbb_percentage'] >= 20 || '{{$position}}' == 'rp' && stat['kbb_percentage'] >= 25) { kbb_style += 'font-weight: bold; font-size: 1.2em'; }
                var whip_rank_html = '';
                if ('{{$position}}' == 'sp') { whip_rank_html = '<td class="align-middle whip d-none d-md-table-cell">'+stat['whip_rank']+'</td>' };
                $('#pitchers tbody').append(
                    "<tr>" +
                    '<td class="align-middle" style="font-size: 1.2em;">'+rank+"</td>"+
                    '<td class="align-middle name" style="text-align: left; font-size: 1.2em; width: 150px; letter-spacing: 0;"><a target="_blank" href="/pitcher/'+stat['player']['slug']+'" class="pitcherNameLink">'+stat['player']['name']+'</a></td>'+
                    '<td class="align-middle d-none d-md-table-cell" style="border-right: 1px solid black;">'+stat['age']+"</td>"+
                    '<td class="align-middle ip">'+stat['ip']+"</td>"+
                    '<td class="align-middle ip-per-g d-none d-md-table-cell" style="border-right: 1px solid black;">'+stat['ip_per_g']+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell" style="border-right: 1px solid black;">'+stat['k_per_game']+"</td>"+
                    '<td class="align-middle">'+stat['era']+"</td>"+
                    '<td class="align-middle" style="border-right: 1px solid black;">'+stat['whip']+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell">'+stat['k_percentage']+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell">'+stat['bb_percentage']+"</td>"+
                    '<td class="align-middle" style="border-right: 1px solid black; '+kbb_style+'">'+stat['kbb_percentage']+"</td>"+
                    '<td class="align-middle border-right-desktop no-border-right-mobile" style="">'+stat['swstr_percentage']+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell" style="border-right: 1px solid black;">'+stat['gb_percentage']+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell" style="border-right: 1px solid black;">'+stat['csw']+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell" style="border-right: 1px solid black;">'+stat['stuff_plus']+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell" style="border-right: 1px solid black;">'+stat['velo']+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell">'+stat['k_rank']+"</td>"+
                    '<td class="align-middle xera d-none d-md-table-cell">'+stat['xwoba_rank']+"</td>"+
                    whip_rank_html+
                    '<td class="align-middle d-none d-md-table-cell" style="font-size: 1.2em;">'+rank+"</td>"+
                    '</tr>');
            }

            updateData();
            if (window.screen.height <= 932) { // scroll to stats automatically on mobile
                $('html').animate({ scrollTop: $('table').offset().top }, 800);
            }
            $(window).on("orientationchange", function(event) {
                if (window.screen.height <= 932) { // scroll to stats automatically on mobile
                    $('html').animate({ scrollTop: $('table').offset().top }, 800);
                }
            });

            $('#expand').on('click', function() {
                $('.d-none').removeClass('d-none');
            });
        });
    </script>
@endsection
