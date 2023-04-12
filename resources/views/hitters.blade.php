@extends('_base')

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/jq-3.6.0/dt-1.13.4/fc-4.2.2/fh-3.3.2/r-2.4.1/datatables.min.css"/>
    <link rel="canonical" href="{{route('hitter_ranks', [$year])}}" />
@endsection

@section('content')
    <h1>
        Hitter Rankings
    </h1>

    <p>Minimum PA: {{ $min_pa }}</p>

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
            <label for="pa_per_g_minimum">PA/G Min</label>
            <input type="text" id="pa_per_g_minimum" class="form-control form-control-sm">
        </div>

        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2">
            <label for="sb_minimum">SB Min</label>
            <input type="text" id="sb_minimum" class="form-control form-control-sm">
        </div>

        <div class="col-xl-6 col-lg-6 col-md-6 col-sm-6" style="text-align: right">
            <div>Last updated: @if (date('G') > 7) {{ date('F j, Y') }}@else {{ date('F j, Y', strtotime('yesterday')) }}@endif</div>
            <div id="playerSets" style="margin-bottom: 5px"></div>
            <div id="saveSet" style="margin-bottom: 5px">Save current search as <input type="text" id="saveSetName"><button id="saveSetBtn">Save</button><button id="deleteSetBtn">Delete</button></div>
            <div style="margin-bottom: 5px">Search: <input type="text" id="search"></div>
            <br />
        </div>

    </div>

    <div class="table-responsive-sm">
        <div style="text-align: center">Out of <span class="numHitters"></span> eligible hitters</div>
        <table id="hitters" class="table-bordered table-hover table-sm" style="font-size: 12px; line-height: 16px; margin: 0 auto;">
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

{{--                <th>K% Rank</th>--}}
                <th style="border-right: 1px solid black;">Sprint Rank</th>
{{--                <th>BA Rank</th>--}}
{{--                <th>SB/PA Rank</th>--}}
                <th>Brls Rank</th>
                <th style="border-right: 1px solid black;">xwOBA Rank</th>

                <th style="font-weight: bold; border-right: 1px solid black;">Def</th>
                <th class="all">wRC+</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stats as $key => $stat)
                <tr>
                    <td class="align-middle" style="font-size: 1.2em;">{{$key+1}}</td>
                    <td class="align-middle" style="text-align: left; font-size: 1.2em; width: 150px; letter-spacing: 0;"><a href="{{route('hitter', $stat->player['slug'])}}" class="hitterNameLink">{{$stat->player['name']}}</a></td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{$stat['age']}}</td>
                    <td class="align-middle">{{$stat['pa']}}</td>
                    <td class="align-middle pa-per-g" style="border-right: 1px solid black;">{{ltrim(number_format($stat['pa_per_g'], 1))}}</td>
                    <td class="align-middle">{{$stat['r']}}</td>
                    <td class="align-middle">{{ltrim(number_format($stat['avg'], 3),"0")}}</td>
                    <td class="align-middle">{{$stat['hr']}}</td>
                    <td class="align-middle">{{$stat['rbi']}}</td>
                    <td class="align-middle sb" style="border-right: 1px solid black;">{{$stat['sb']}}</td>
                    <td class="align-middle">{{number_format($stat['bb_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['k_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['swstr_percentage'], 1)}}</td>

{{--                    <td class="align-middle">{{number_format($stat['k_percentage_rank'])}}</td>--}}
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['sprint_speed_rank'])}}</td>
{{--                    <td class="align-middle">{{number_format($stat['avg_rank'])}}</td>--}}
{{--                    <td class="align-middle">{{number_format($stat['sb_per_pa_rank'])}}</td>--}}
                    <td class="align-middle">{{number_format($stat['brls_rank'])}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['xwoba_rank'])}}</td>

                    <td class="align-middle" style="border-right: 1px solid black;">{{$stat['def']}}</td>

                    <td class="align-middle" style="@if ($stat['wrc_plus'] > 110) font-weight: bold; font-size: 1.2em; color: green @endif ">{{$stat['wrc_plus']}}</td>
                </tr>
            @endforeach
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
                        // t.search(hitter.players, true, false).draw();
                    });
                });
            }

            drawPlayerSetButtons(data.hitters);

            // var t = $('#hitters').DataTable({
            //     fixedHeader: true,
            //     fixedColumns: {
            //         left: 2
            //     },
            //     scrollX: true,
            //     paging: false,
            //     // order: [[ 17, "asc" ]]
            //     // columnDefs: [
            //     //     { width: "6%", targets: [0,2,3,4,5,6,7,8,9,10,11,12,13,14,15,16,17] },
            //     // ]
            // });

            // $('.table-responsive-md').on("scroll", function() {
            //     $('#hitters').DataTable().fixedHeader.adjust();
            // });

            function doMins() {
                var minPaPerGame = parseFloat($('#pa_per_g_minimum').val());
                $('.pa-per-g').parent().removeClass('exclude').show();
                $('.pa-per-g').each(function() {
                    if ($(this).html() < minPaPerGame) {
                        $(this).parent().addClass('exclude');
                        $(this).parent().hide();
                    }
                });
                var minSb = parseFloat($('#sb_minimum').val());
                $('.sb').each(function() {
                    if ($(this).html() < minSb) {
                        $(this).parent().addClass('exclude');
                        $(this).parent().hide();
                    }
                });
                filterCurrentSearch();
            }

            // t.on('order.dt search.dt', function () {
            //     let i = 1;
            //
            //     t.cells(null, 0, { search: 'applied', order: 'applied' }).every(function (cell) {
            //         this.data(i++);
            //     });
            // }).draw();

            // $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            //     var min = parseFloat($('#pa_per_g_minimum').val());
            //     if (parseFloat(data[4]) < min) {
            //         return false;
            //     }
            //     return true;
            // });
            //
            // $.fn.dataTable.ext.search.push(function(settings, data, dataIndex) {
            //     var min = parseFloat($('#sb_minimum').val());
            //     if (parseFloat(data[9]) < min) {
            //         return false;
            //     }
            //     return true;
            // });

            if ($.cookie('pa_per_g_minimum') !== 'NaN') {
                $('#pa_per_g_minimum').val($.cookie('pa_per_g_minimum'));
            }
            if ($.cookie('sb_minimum') !== 'NaN') {
                $('#sb_minimum').val($.cookie('sb_minimum'));
            }
            // t.draw();

            $('.playerSetBtn').eq(0).click();

            $('#positionSelect, #yearSelect').change(function() {
                var year = $('#yearSelect').val();
                var position = $('#positionSelect').val();
                window.location.href = '/hitters/'+year;
            });

            // $('.dataTables_filter input', t.table().container())
            //     .off('.DT')
            //     .on('keyup.DT cut.DT paste.DT input.DT search.DT', function (e) {
            //         // If the length is 3 or more characters, or the user pressed ENTER, search
            //         if(this.value.length >= 3 || e.keyCode == 13) {
            //             // Call the API search function
            //             t.search(this.value, true, false).draw();
            //         }
            //
            //         // Ensure we clear the search if they backspace far enough
            //         if(this.value === "") {
            //             t.search("").draw();
            //         }
            //     });

            $('#search').on('change keyup', function() {
                filterCurrentSearch();
            });

            $('#pa_per_g_minimum, #sb_minimum').on('keyup change', function(e) {
                $.cookie('pa_per_g_minimum', parseFloat($('#pa_per_g_minimum').val()), { expires: 20*365 });
                $.cookie('sb_minimum', parseFloat($('#sb_minimum').val()), { expires: 20*365 });
                doMins();
                // t.draw();
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

            doMins();
            filterCurrentSearch();
        });
    </script>
@endsection
