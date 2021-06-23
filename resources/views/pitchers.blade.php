@extends('_base')

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.css"/>
    <link rel="canonical" href="{{route('pitcher_ranks', [$year, $position])}}" />
@endsection

@section('content')
    <h1>
        Pitcher Rankings
    </h1>

    @if ($position == 'sp')
        <p>Minimum 3.0 IP per appearance, 10 total IP</p>
    @else
        <p>Less than 3.0 IP per appearance, at least 5 total IP</p>
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
            <div id="saveSet" style="margin-bottom: 5px">Save current filter as <input type="text" id="saveSetName"><button type="submit" id="saveSetBtn">Save</button></div>
            <br />
        </div>
    </div>

    <div class="table-responsive-md">
        <table id="pitchers" class="table table-bordered table-hover table-sm" style="font-size: 12px">
            <thead>
            <tr>
                <th>Rank</th>
                <th style="width: 125px">Name</th>
                <th style="border-right: 1px solid black;">Age</th>
{{--                <td>G</td>--}}
                <th>IP</th>
                <th style="border-right: 1px solid black;">IPpG</th>
                <th>K%</th>
                <th>BB%</th>
                <th style="border-right: 1px solid black;">K-BB%</th>
                <th style="border-right: 1px solid black;">SwStr%</th>
                <th style="border-right: 1px solid black;">GB%</th>
                <th style="border-right: 1px solid black;"><a href="https://www.pitcherlist.com/csw-rate-an-intro-to-an-important-new-metric/">CSW%</a></th>
                <th style="border-right: 1px solid black;">Velo</th>
                <th>IPpG Rank</th>
                <th>K% Rank</th>
                <th>xERA Rank</th>
                <th style="font-weight: bold">Avg</th>
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
                    <td class="align-middle">{{number_format($stat['k_percentage'],1)}}</td>
                    <td class="align-middle">{{number_format($stat['bb_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['k_percentage'] - $stat['bb_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['swstr_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['gb_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['csw'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['velo'], 1)}}</td>
                    <td class="align-middle">{{ $position != 'rp' ? $stat['ip_per_g_rank'] : ''}}</td>
                    <td class="align-middle">{{ $stat['k_rank'] ?? ''}}</td>
                    <td class="align-middle">{{ $stat['xwoba_rank'] ?? ''}}</td>

                    @if ($position == 'sp')
                        <td class="align-middle" style="font-weight: bold;font-size: 1.2em;">{{ number_format(($stat['ip_per_g_rank'] + $stat['k_rank'] + $stat['xwoba_rank']) / 3, 1) }}</td>
                    @else
                        <td class="align-middle" style="font-weight: bold;font-size: 1.2em;">{{ number_format(($stat['k_rank'] + $stat['xwoba_rank']) / 2, 1) }}</td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {

            var data = localStorage.getItem('data');

            if (!data) {
                data = {};
            } else {
                data = JSON.parse(data);
            }

            $('#saveSetBtn').on('click', function(e) {
                var name = $('#saveSetName').val();
                var string = $('.dataTables_filter input').val();
                if (string !== '') {
                    data.pitchers[name] = {
                        "name": name,
                        "players": string
                    };
                    drawPlayerSetButtons(data.pitchers);
                    localStorage.setItem('data', JSON.stringify(data));
                }
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
                        t.search(pitcher.players, true, false).draw();
                    });
                    $("#saveSetName").val($("#playerSets button:first").html());
                });
            }

            drawPlayerSetButtons(data.pitchers);

            var t = $('#pitchers').DataTable({
                fixedHeader: true,
                responsive: {
                    details: false
                },
                paging: false,
                columnDefs: [
                    { "width": "5.5%", "targets": [0,2,3,4,5,6,7,8,9,10,11,12,13,14,15] }
                ]
            });

            $('.playerSetBtn').eq(0).click();

            $('#positionSelect, #yearSelect').change(function() {
                var year = $('#yearSelect').val();
                var position = $('#positionSelect').val();
                window.location.href = '/pitchers/'+year+'/'+position;
            });

            $('.dataTables_filter input', t.table().container())
                .off('.DT')
                .on('keyup.DT cut.DT paste.DT input.DT search.DT', function (e) {
                    // If the length is 3 or more characters, or the user pressed ENTER, search
                    if(this.value.length >= 3 || e.keyCode == 13) {
                        // Call the API search function
                        t.search(this.value, true, false).draw();
                    }

                    // Ensure we clear the search if they backspace far enough
                    if(this.value === "") {
                        t.search("").draw();
                    }
                });
        });
    </script>
@endsection
