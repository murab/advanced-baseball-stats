@extends('_base')

@section('css')
    <link rel="canonical" href="{{route('hitter_ranks', [$year])}}" />
    <style>
        @media (max-width: 540px) {
            .ops { border-right: none !important; }
            .border-right-mobile { border-right: 1px solid black !important }
        }
        @media (min-width: 541px) {

        }
    </style>
@endsection

@section('content')
    <h1>
        Hitter Rankings <button id="expand" class="d-sm-none btn btn-primary">Expand</button>
    </h1>

    <p>Average PA in {{ $year }}: {{ $min_pa }}</p>

    <div class="row">
        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 col-3 mb-3">
            <label class="mb-0"  for="yearSelect">Year</label>
            <select class="form-control form-control-sm" id="yearSelect" name="yearSelect">
                @foreach ($years as $oneYear)
                    <option value="{{$oneYear}}" @if ($year == $oneYear) selected @endif>{{$oneYear}}</option>
                @endforeach
            </select>
        </div>

        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 col-3">
            <label class="mb-0"  for="pa_minimum">PA Min</label>
            <input type="text" id="pa_minimum" class="form-control form-control-sm">
        </div>

        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 col-3">
            <label class="mb-0"  for="pa_per_g_minimum">PA/G Min</label>
            <input type="text" id="pa_per_g_minimum" class="form-control form-control-sm">
        </div>

        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 col-3">
            <label class="mb-0"  for="sb_minimum">SB Min</label>
            <input type="text" id="sb_minimum" class="form-control form-control-sm">
        </div>

        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 col-6">
            <label class="mb-0"  for="saveSetName">Save search as</label>
            <input type="text" class="form-control form-control-sm" id="saveSetName">
            <button class="btn btn-outline-secondary btn-sm mt-1 mr-1" id="saveSetBtn">Save</button><button class="btn btn-outline-secondary mt-1 btn-sm" id="deleteSetBtn">Delete</button>
        </div>
        <div class="col-xl-2 col-lg-2 col-md-2 col-sm-2 col-6">
            <label class="mb-0"  for="search">Search</label>
            <input class="form-control-sm form-control" type="text" id="search">
            <span class="float-right" id="playerSets"></span>
        </div>

    </div>

    <div class="row">
        <div class="col-sm-12" style="padding-right: 0; padding-left: 0">
            <div class="form-group form-check" style="text-align: center">
                <input type="checkbox" name="toggle-xstats" id="toggle-xstats" class="form-check-input">
                <label class="form-check-label" for="toggle-xstats">Toggle xBA/xHR</label>
            </div>
            <div style="text-align: center">Out of <span class="numHitters"></span> eligible hitters</div>
            <table id="hitters" class="table-bordered table-hover table-sm" style="font-size: 12px; line-height: 18px; margin: 0 auto;">
                <thead>
                <tr style="text-align: center">
                    <th>Rank</th>
                    <th style="width: 125px">Name</th>
                    <th style="border-right: 1px solid black;">Age</th>
                    <th class="border-right-mobile">PA</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">PA/G</th>
                    <th>R</th>
                    <th id="th-avg">AVG</th>
                    <th id="th-hr">HR</th>
                    <th>RBI</th>
                    <th style="border-right: 1px solid black;">SB</th>
                    <th class="d-none d-lg-table-cell" style="border-right: 1px solid black;">SB%</th>
                    <th class="ops" style="border-right: 1px solid black;">OPS</th>
                    <th class="d-none d-md-table-cell">BB%</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">K%</th>
                    <th class="d-none d-lg-table-cell" style="border-right: 1px solid black;">SwStr%</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">Sprint<br>Speed</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">Brls/PA</th>
                    <th class="d-none d-lg-table-cell" style="border-right: 1px solid black;">PullFB/G</th>
                    <th class="d-none d-md-table-cell">PullFB/G<br>Rank</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">xwOBA<br>Rank</th>
                    <th class="d-none d-md-table-cell" style="font-weight: bold; border-right: 1px solid black;">Def</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">wRC+ vs. L</th>
                    <th class="d-none d-md-table-cell">wRC+</th>
                </tr>
                </thead>
                <tbody>

                </tbody>
            </table>
            <div style="text-align: center">Out of <span class="numHitters"></span> eligible hitters</div>
        </div>
    </div>

@endsection

@section('javascript')
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
                if (data.xstats === 'true') data.xstats = true;
                if (data.xstats === 'false') data.xstats = false;
            }

            $.ajax({
                url: '/api/lists/get',
                data: data,
                type: 'POST',
                success: function(resp) {
                    var parsed = JSON.parse(resp);
                    if (parsed.hitters || parsed.pitchers) {
                        data = parsed;
                        if (data.xstats === 'true') data.xstats = true;
                        if (data.xstats === 'false') data.xstats = false;
                        localStorage.setItem('data', resp);
                    }
                },
                async: false
            });

            if (data.min_pa != {{ $min_pa }}) {
                data.pa_min = {{ $min_pa }};
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

                    $.post('/api/lists/save', data);
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
                        "<button id='"+hitter.name+"' class='btn btn-outline-secondary ml-1 btn-sm mt-1 playerSetBtn'>"+hitter.name+"</button>"
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

            $('#pa_minimum').val(data.pa_min);

            $('#pa_per_g_minimum').val(data.pa_per_g_minimum ?? 3.7);

            $('#sb_minimum').val(0);

            if (data.xstats === true && !$('#toggle-xstats').is(':checked') || data.xstats === false && $('#toggle-xstats').is(':checked')) {
                $('#toggle-xstats').click();
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
                data.pa_min = $('#pa_minimum').val();
                data.pa_per_g_minimum = $('#pa_per_g_minimum').val();
                data.min_pa = {{ $min_pa }};
                data.xstats = $('#toggle-xstats').is(':checked');
                localStorage.setItem('data', JSON.stringify(data));
                $.post('/api/lists/save', data);
                // $.cookie('pa_minimum', parseFloat($('#pa_minimum').val()), { expires: 20*365 });
                // $.cookie('pa_per_g_minimum', parseFloat($('#pa_per_g_minimum').val()), { expires: 20*365 });
                // $.cookie('sb_minimum', parseFloat($('#sb_minimum').val()), { expires: 20*365 });
                updateData();
            });

            $('#pa_minimum, #pa_per_g_minimum, #sb_minimum').on('blur', function(e) {
                if ($(this).val() == '') { $(this).val(0); }
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
                    $('[data-toggle="tooltip"]').tooltip();
                    doXstats();
                });
            }

            function insertRow(stat, rank) {
                var sb_percentage = stat['sb'] === 0 ? 0 :  Number(stat['sb']/(stat['sb']+stat['cs'])*100).toFixed(0);
                var ops_style = '';
                if (stat['ops'] >= .8) { ops_style += 'color: green; '; }
                if (stat['ops'] >= .9) { ops_style += 'font-weight: bold; font-size: 1.2em;'; }
                var brls_style = '';
                if (stat['brls_per_pa'] >= 8) { brls_style += 'color: green; '; }
                if (stat['brls_per_pa'] >= 10) { brls_style += 'font-weight: bold; font-size: 1.2em'; }
                var vsleft_wrc_plus_style = '';
                if ((stat['bats'] == 'L' || stat['bats'] == 'B') && stat['vsleft_wrc_plus'] >= 100) { vsleft_wrc_plus_style += 'color: green; '; }
                if ((stat['bats'] == 'L' || stat['bats'] == 'B') && stat['vsleft_wrc_plus'] >= 110) { vsleft_wrc_plus_style += 'font-weight: bold; font-size: 1.2em; '; }
                if ((stat['bats'] == 'L' || stat['bats'] == 'B') && stat['vsleft_wrc_plus'] <= 80) { vsleft_wrc_plus_style += 'color: red; '; }
                $('#hitters tbody').append(
                    "<tr>" +
                    '<td class="align-middle" style="font-size: 1.2em;">'+rank+"</td>"+
                    '<td class="align-middle" style="text-align: left; font-size: 1.2em; width: 150px; letter-spacing: 0;"><a target="_blank" href="/hitter/'+stat['player']['slug']+'" class="hitterNameLink">'+stat['player']['name']+'</a></td>'+
                    '<td class="align-middle" style="border-right: 1px solid black;">'+stat['age']+"</td>"+
                    '<td class="align-middle pa border-right-mobile">'+stat['pa']+"</td>"+
                    '<td class="align-middle pa-per-g d-none d-md-table-cell" style="border-right: 1px solid black;">'+stat['pa_per_g']+"</td>"+
                    '<td class="align-middle">'+stat['r']+"</td>"+
                    '<td class="avg align-middle" style="text-decoration-line: underline; text-decoration-color: lightgray; text-decoration-style: dotted;" data-avg="'+stat['avg']+'" data-xavg="'+stat['xba']+'" data-toggle="tooltip" title="Expected: '+stat['xba']+'">'+stat['avg']+"</td>"+
                    '<td class="hr align-middle" style="text-decoration-line: underline; text-decoration-color: lightgray; text-decoration-style: dotted;" data-hr="'+stat['hr']+'" data-xhr="'+stat['xhr']+'" data-toggle="tooltip" title="Expected: '+stat['xhr']+'">'+stat['hr']+"</td>"+
                    '<td class="align-middle">'+stat['rbi']+"</td>"+
                    '<td class="align-middle sb" style="border-right: 1px solid black;">'+stat['sb']+"</td>"+
                    '<td class="align-middle d-none d-lg-table-cell" style="border-right: 1px solid black;">' + sb_percentage + '%</td>' +
                    '<td class="align-middle ops" style="border-right: 1px solid black;'+ops_style+'">'+Number(stat['ops']).toFixed(3)+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell">'+stat['bb_percentage']+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell" style="border-right: 1px solid black;">'+stat['k_percentage']+"</td>"+
                    '<td class="align-middle d-none d-lg-table-cell" style="border-right: 1px solid black;">'+stat['swstr_percentage']+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell" style="border-right: 1px solid black;">'+Number(stat['sprint_speed']).toFixed(1)+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell" style="border-right: 1px solid black;'+brls_style+'">'+stat['brls_per_pa']+"%</td>"+
                    '<td class="align-middle d-none d-lg-table-cell" style="border-right: 1px solid black;">'+Number(stat['pulled_flyballs_per_g']).toFixed(2)+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell">'+stat['pulled_fb_g_rank']+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell" style="border-right: 1px solid black;">'+stat['xwoba_rank']+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell" style="border-right: 1px solid black; '+(stat['def'] > 0 ? 'color: green; font-weight: bold' : '')+' ">'+stat['def']+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell" style="border-right: 1px solid black;'+vsleft_wrc_plus_style+'">'+stat['vsleft_wrc_plus']+"</td>"+
                    '<td class="align-middle d-none d-md-table-cell" style=" font-size: 1.2em;  '+(stat['wrc_plus'] > 110 ? 'font-weight: bold; color: green' : '')+'">'+stat['wrc_plus']+"</td>"+
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

            function doXstats() {
                if ($('#toggle-xstats').is(':checked')) {
                    data.xstats = true;
                    $('#th-avg').html('xAVG');
                    $('.avg').each(function() {
                        $(this).html($(this).data('xavg') ?? 0);
                    });
                    $('#th-hr').html('xHR');
                    $('.hr').each(function() {
                        $(this).html(Math.round($(this).data('xhr')) ?? 0);
                    });
                } else {
                    data.xstats = false;
                    $('#th-avg').html('AVG');
                    $('.avg').each(function() {
                        $(this).html($(this).data('avg') ?? 0);
                    });
                    $('#th-hr').html('HR');
                    $('.hr').each(function() {
                        $(this).html(Math.round($(this).data('hr')) ?? 0);
                    });
                }
            }

            if (data.xstats === true && !$('#toggle-xstats').is(':checked') || data.xstats === false && $('#toggle-xstats').is(':checked')) {
                $('#toggle-xstats').click();
            }

            $('#toggle-xstats').on('change', function() {
                doXstats();
                data.xstats = $('#toggle-xstats').is(':checked');
                localStorage.setItem('data', JSON.stringify(data));
                $.post('/api/lists/save', data);
            });
        });
    </script>
@endsection
