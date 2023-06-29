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
            <select class="form-control" id="positionSelect" name="positionSelect">
                <option value="sp">SP</option>
                <option value="rp" @if ($position == 'rp') selected @endif>RP</option>
            </select>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4">
            <label for="yearSelect">Year</label>
            <select class="form-control" id="yearSelect" name="yearSelect">
                @foreach ($years as $oneYear)
                    <option value="{{$oneYear}}" @if ($year == $oneYear) selected @endif>{{$oneYear}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-xl-9 col-lg-7 col-md-5" style="text-align: right">
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
                <th class="desktop" style="border-right: 1px solid black;"><a href="https://www.pitcherlist.com/csw-rate-an-intro-to-an-important-new-metric/">CSW%</a></th>
                <th class="all" style="border-right: 1px solid black;">Velo</th>
{{--                <th class="desktop">IPpG Rank</th>--}}

                @if ($position == 'sp')
                    <th class="desktop">KpG Rank</th>
                @else
                    <th class="desktop">K% Rank</th>
                @endif

                <th class="desktop">xERA Rank</th>
                <th class="all" style="font-weight: bold">Rank</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stats as $key => $stat)
                <tr>
                    <td style="font-size: 1.2em;" class="align-middle">{{$key+1}}</td>
                    <td class="align-middle" style="text-align: left;font-size: 1.2em; letter-spacing: 0"><a href={{route('pitcher', $stat->player['slug'])}}>{{$stat->player['name']}}</a></td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{$stat['age']}}</td>
{{--                    <td>{{$stat['g']}}</td>--}}
                    <td class="align-middle">{{$stat['ip']}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['ip'] / $stat['g'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['k_per_game'], 1)}}</td>
                    <td class="align-middle">{{number_format($stat['era'], 2)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['whip'], 2)}}</td>
                    <td class="align-middle">{{number_format($stat['k_percentage'],1)}}</td>
                    <td class="align-middle">{{number_format($stat['bb_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['k_percentage'] - $stat['bb_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['swstr_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['gb_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['csw'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['velo'], 1)}}</td>
{{--                    <td class="align-middle">{{ $position != 'rp' ? $stat['ip_per_g_rank'] : ''}}</td>--}}
                    <td class="align-middle">{{ $stat['k_rank'] ?? ''}}</td>
                    <td class="align-middle">{{ $stat['xwoba_rank'] ?? ''}}</td>
                    <td class="align-middle" style="font-weight: bold;font-size: 1.2em;">{{$key+1}}</td>
                </tr>
            @endforeach
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

            $('.playerSetBtn').eq(0).click();

            $('#positionSelect, #yearSelect').change(function() {
                var year = $('#yearSelect').val();
                var position = $('#positionSelect').val();
                window.location.href = '/pitchers/'+year+'/'+position;
            });

            $('#search').on('change keyup', function() {
                filterCurrentSearch();
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

            filterCurrentSearch();
        });
    </script>
@endsection
