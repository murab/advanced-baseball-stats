@extends('_base')

@section('css')
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.css"/>
@endsection

@section('content')
    <h1 style="margin-bottom: 25px; text-align: center">
        {{$player['name']}} Stats
    </h1>

    <div style="text-align: center">
        <button id="expand" class="d-block d-sm-none btn btn-primary" style="margin: 0 auto; margin-bottom: 20px;">Expand</button>
    </div>

    <div class="form-group form-check" style="text-align: center">
        <input type="checkbox" name="toggle-xstats" id="toggle-xstats" class="form-check-input">
        <label class="form-check-label" for="toggle-xstats">Toggle xBA/xHR</label>
    </div>

    <div class="row">
        <div class="col-sm-12 table-responsive" style="padding-right: 0; padding-left: 0">
        <table id="hitters" class="table-bordered table-striped table-sm" style="font-size: 12px; line-height: 18px; margin: 0 auto;">
            <thead style="text-align: center">
            <tr>
                <th class="all">Year</th>
                <th class="all" style="border-right: 1px solid black;">Age</th>
                <th class="all">PA</th>
                <th class="all" style="border-right: 1px solid black;">PA/G</th>
                <th class="all">R</th>
                <th id="th-avg" class="all">AVG</th>
                <th id="th-hr" class="all">HR</th>
                <th class="all">RBI</th>
                <th class="all" style="border-right: 1px solid black;">SB</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">SB%</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">OPS</th>
                <th class="d-none d-md-table-cell">BB%</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">K%</th>
                <th class="d-none d-lg-table-cell" style="border-right: 1px solid black;">SwStr%</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">Sprint Spd</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">Brls/PA</th>
                <th class="d-none d-md-table-cell">AVG<br>Rank</th>
                <th class="d-none d-md-table-cell">HR/G<br>Rank</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">xwOBA<br>Rank</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">Def</th>
                <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">wRC+ vs. L</th>
                <th class="all" style="border-right: 1px solid black;">wRC+</th>
                <th class="all">Rank</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stats as $key => $stat)
                <tr>
                    <td><a href="{{route('hitter_ranks', [$stat['year'], strtolower($stat['position'])])}}">{{$stat['year']}}</a></td>
                    <td style="border-right: 1px solid black;">{{$stat['age']}}</td>
                    <td class="all">{{$stat['pa']}}</td>
                    <td class="all" style="border-right: 1px solid black;">{{ltrim(number_format($stat['pa_per_g'], 1))}}</td>
                    <td>{{$stat['r']}}</td>
                    <td class="avg" <?php if ($stat['xba']) { ?> style="text-decoration-line: underline; text-decoration-color: lightgray; text-decoration-style: dotted;" data-toggle="tooltip" data-avg="<?php echo ltrim(number_format($stat['avg'], 3),"0")?>" data-xba="<?php echo ltrim(number_format($stat['xba'], 3),'0')?>" title="Expected: {{ltrim(number_format($stat['xba'], 3),'0')}}" <?php } ?>>{{ltrim(number_format($stat['avg'], 3),"0")}}</td>
                    <td class="hr" <?php if ($stat['xba']) { ?>style="text-decoration-line: underline; text-decoration-color: lightgray; text-decoration-style: dotted;" data-toggle="tooltip" data-hr="<?php echo $stat['hr']?>" data-xhr="<?php echo $stat['xhr']?>" title="Expected: {{$stat['xhr']}}" <?php } ?>>{{$stat['hr']}}</td>
                    <td>{{$stat['rbi']}}</td>
                    <td style="border-right: 1px solid black;">{{$stat['sb']}}</td>
                    <td class="d-none d-md-table-cell" style="border-right: 1px solid black;"><?php if ($stat['cs'] === null || $stat['sb'] === 0) echo '0'; else echo number_format($stat['sb']/($stat['sb']+$stat['cs']),2)*100; ?>%</td>
                    <td class="d-none d-md-table-cell" style="border-right: 1px solid black; @if ($stat['ops'] > .8) color:green; @endif @if ($stat['ops'] >= .9) font-weight:bold; font-size: 1.2em; @endif">{{number_format($stat['ops'], 3)}}</td>
                    <td class="d-none d-md-table-cell">{{number_format($stat['bb_percentage'], 1)}}</td>
                    <td class="d-none d-md-table-cell" style="border-right: 1px solid black;">{{number_format($stat['k_percentage'], 1)}}</td>
                    <td class="d-none d-lg-table-cell" style="border-right: 1px solid black;">{{number_format($stat['swstr_percentage'], 1)}}</td>
                    <td class="d-none d-md-table-cell" style="border-right: 1px solid black;">{{number_format($stat['sprint_speed'], 1)}}</td>
                    <td class="d-none d-md-table-cell" style="border-right: 1px solid black; @if ($stat['brls_per_pa'] >= 8) color: green; @endif @if ($stat['brls_per_pa'] >= 10) font-size: 1.2em; font-weight: bold; @endif">{{ number_format(round($stat['brls_per_pa'])) }}%</td>
                    <td class="d-none d-md-table-cell">{{$stat['avg_rank'] ?? '' }}</td>
                    <td class="d-none d-md-table-cell">{{$stat['hr_per_g_rank'] ?? ''}}</td>
                    <td class="d-none d-md-table-cell" style="border-right: 1px solid black;">{{$stat['xwoba_rank'] ?? ''}}</td>
                    <td class="d-none d-md-table-cell" style="border-right: 1px solid black; @if ($stat['def'] > 0) color: green; font-weight: bold @endif ">{{number_format($stat['def'], 1)}}</td>
                    <td class="d-none d-md-table-cell" style="border-right: 1px solid black; @if (($bats == 'L' || $bats == 'B') && $stat['vsleft_wrc_plus'] >= 100) color: green; @endif @if (($bats == 'L' || $bats == 'B') && $stat['vsleft_wrc_plus'] >= 110) font-weight: bold; font-size: 1.2em; @endif @if (($bats == 'L' || $bats == 'B') && $stat['vsleft_wrc_plus'] <= 80) color: red; @endif">{{ $stat['vsleft_wrc_plus'] }}</td>
                    <td style="border-right: 1px solid black; @if ($stat['wrc_plus'] > 110) font-weight: bold; font-size: 1.2em; color: green @endif">{{$stat['wrc_plus']}}</td>
                    <td style="font-weight: bold">{{ $stat['rank_avg_rank'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
    </div>
@endsection

@section('javascript')
    <script>$('[data-toggle="tooltip"]').tooltip();</script>
    <script type="text/javascript" src="//cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#expand').on('click', function() {
                $('.d-none').removeClass('d-none');
            });

            var data = localStorage.getItem('data');

            if (!data) {
                data = {
                    "hitters": {},
                    "pitchers": {}
                };
            } else {
                data = JSON.parse(data);
            }

            if (data.xstats === 'true') data.xstats = true;
            if (data.xstats === 'false') data.xstats = false;
            if (data.xstats === true && !$('#toggle-xstats').is(':checked') || data.xstats === false && $('#toggle-xstats').is(':checked')) {
                $('#toggle-xstats').click();
            }

            function doXstats() {
                if ($('#toggle-xstats').is(':checked')) {
                    data.xstats = true;
                    $('#th-avg').html('xAVG');
                    $('.avg').each(function() {
                        $(this).html($(this).data('xba') ?? 0);
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

            doXstats();
            $('#toggle-xstats').on('change', function() {
                doXstats();
            });

            if (window.screen.height <= 932) { // scroll to stats automatically on mobile
                $('html').animate({ scrollTop: $('table').offset().top }, 800);
            }
            $(window).on("orientationchange", function(event) {
                if (window.screen.height <= 932) { // scroll to stats automatically on mobile
                    $('html').animate({ scrollTop: $('table').offset().top }, 800);
                }
            });
        });
    </script>
@endsection
