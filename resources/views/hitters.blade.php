@extends('_base')

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.css"/>
    <link rel="canonical" href="{{route('hitter_ranks', [$year])}}" />
@endsection

@section('content')
    <h1>
        Hitter Rankings
    </h1>

    <p>Minimum PA: {{ $min_pa }}</p>

    <div class="row">
        <div class="col-xl-2 col-md-3">
            <label for="yearSelect">Year</label>
            <select class="form-control" id="yearSelect" name="yearSelect">
                @foreach ($years as $oneYear)
                    <option value="{{$oneYear}}" @if ($year == $oneYear) selected @endif>{{$oneYear}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-xl-10 col-lg-8 col-md-6" style="text-align: right">
            <div>Last updated: @if (date('G') > 7) {{ date('F j, Y') }}@else {{ date('F j, Y', strtotime('yesterday')) }}@endif</div>
            <div id="playerSets" style="margin-bottom: 5px"></div>
            <div id="saveSet" style="margin-bottom: 5px">Save current search as <input type="text" id="saveSetName"><button id="saveSetBtn">Save</button><button id="deleteSetBtn">Delete</button></div>
            <br />
        </div>
    </div>

    <div class="table-responsive-md">
        <table id="hitters" class="table table-bordered table-hover table-sm" style="font-size: 12px">
            <thead>
            <tr>
                <th>Rank</th>
                <th style="width: 125px">Name</th>
                <th style="border-right: 1px solid black;">Age</th>
                <th style="border-right: 1px solid black;">PA</th>
                <th>R</th>
                <th>AVG</th>
                <th>HR</th>
                <th>RBI</th>
                <th style="border-right: 1px solid black;">SB</th>
                <th>BB%</th>
                <th style="border-right: 1px solid black;">K%</th>
                <th style="border-right: 1px solid black;">SwStr%</th>

                <th style="border-right: 1px solid black;">wRC+</th>

                <th>K% Rank</th>
                <th>Hard% Rank</th>
                <th>Sprint Rank</th>
                <th>Brls Rank</th>

                <th style="font-weight: bold">Avg</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stats as $key => $stat)
                <tr>
                    <td class="align-middle" style="font-size: 1.2em;">{{$key+1}}</td>
                    <td class="align-middle" style="text-align: left; font-size: 1.2em; width: 150px; letter-spacing: 0;"><a href="{{route('hitter', $stat->player['slug'])}}" class="hitterNameLink">{{$stat->player['name']}}</a></td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{$stat['age']}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{$stat['pa']}}</td>
                    <td class="align-middle">{{$stat['r']}}</td>
                    <td class="align-middle">{{ltrim(number_format($stat['avg'], 3),"0")}}</td>
                    <td class="align-middle">{{$stat['hr']}}</td>
                    <td class="align-middle">{{$stat['rbi']}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{$stat['sb']}}</td>
                    <td class="align-middle">{{number_format($stat['bb_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['k_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['swstr_percentage'], 1)}}</td>

                    <td class="align-middle" style="border-right: 1px solid black;">{{$stat['wrc_plus']}}</td>

                    <td class="align-middle">{{number_format($stat['k_percentage_rank'])}}</td>
                    <td class="align-middle">{{number_format($stat['hardhit_rank'])}}</td>
                    <td class="align-middle">{{number_format($stat['sprint_speed_rank'])}}</td>
                    <td class="align-middle">{{number_format($stat['brls_bbe_rank'])}}</td>

                    <td class="align-middle" style="font-weight: bold;font-size: 1.2em;">{{number_format($stat['rank_avg'], 1)}}</td>
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
                data = {
                    "hitters": {},
                    "pitchers": {}
                };
            } else {
                data = JSON.parse(data);
            }

            $('#saveSetBtn').on('click', function(e) {
                var name = $('#saveSetName').val();
                var string = $('.dataTables_filter input').val();
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
                        t.search(hitter.players, true, false).draw();
                    });
                });
            }

            drawPlayerSetButtons(data.hitters);

            var t = $('#hitters').DataTable({
                fixedHeader: true,
                responsive: {
                    details: false
                },
                paging: false,
                order: [[ 17, "asc" ]],
                columnDefs: [
                    { width: "6%", targets: [0,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17] },
                ]
            });

            $('.playerSetBtn').eq(0).click();

            $('#positionSelect, #yearSelect').change(function() {
                var year = $('#yearSelect').val();
                var position = $('#positionSelect').val();
                window.location.href = '/hitters/'+year;
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
