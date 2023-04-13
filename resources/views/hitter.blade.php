@extends('_base')

@section('css')
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.css"/>
@endsection

@section('content')
    <h1 style="margin-bottom: 25px">
        {{$player['name']}} Stats
    </h1>

    <div class="table-responsive-sm">
        <table id="hitters" class="table-bordered table-striped table-sm" style="font-size: 12px; line-height: 18px; margin: 0 auto;">
            <thead style="text-align: center">
            <tr>
                <th class="all">Year</th>
                <th class="all" style="border-right: 1px solid black;">Age</th>
                <th class="all">PA</th>
                <th class="all" style="border-right: 1px solid black;">PA/G</th>
                <th class="all">R</th>
                <th class="all">AVG</th>
                <th class="all">HR</th>
                <th class="all">RBI</th>
                <th class="all" style="border-right: 1px solid black;">SB</th>
                <th class="all">BB%</th>
                <th class="all" style="border-right: 1px solid black;">K%</th>
                <th class="all" style="border-right: 1px solid black;">SwStr%</th>
                <th class="all" style="border-right: 1px solid black;">Sprint Spd</th>
                <th class="all">Brls Rank</th>
                <th class="all" style="border-right: 1px solid black;">xwOBA Rank</th>
                <th class="all" style="border-right: 1px solid black;">Rank</th>
                <th class="all" style="border-right: 1px solid black;">Def</th>
                <th class="all">wRC+</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stats as $key => $stat)
                <tr>
                    <td><a href="{{route('hitter_ranks', [$stat['year'], strtolower($stat['position'])])}}">{{$stat['year']}}</a></td>
                    <td style="border-right: 1px solid black;">{{$stat['age']}}</td>
                    <td>{{$stat['pa']}}</td>
                    <td style="border-right: 1px solid black;">{{ltrim(number_format($stat['pa_per_g'], 1))}}</td>
                    <td>{{$stat['r']}}</td>
                    <td>{{ltrim(number_format($stat['avg'], 3),"0")}}</td>
                    <td>{{$stat['hr']}}</td>
                    <td>{{$stat['rbi']}}</td>
                    <td style="border-right: 1px solid black;">{{$stat['sb']}}</td>
                    <td>{{number_format($stat['bb_percentage'], 1)}}</td>
                    <td style="border-right: 1px solid black;">{{number_format($stat['k_percentage'], 1)}}</td>
                    <td style="border-right: 1px solid black;">{{number_format($stat['swstr_percentage'], 1)}}</td>
                    <td style="border-right: 1px solid black;">{{number_format($stat['sprint_speed'], 1)}}</td>
                    <td>{{number_format($stat['brls_rank'])}}</td>
                    <td style="border-right: 1px solid black;">{{number_format($stat['xwoba_rank'])}}</td>
                    <td style="border-right: 1px solid black; font-weight: bold">{{ $stat['rank_avg_rank'] }}</td>
                    <td style="border-right: 1px solid black; @if ($stat['def'] > 0) color: green; font-size: 1.2em; font-weight: bold @endif ">{{$stat['def']}}</td>
                    <td style="@if ($stat['wrc_plus'] > 110) font-weight: bold; font-size: 1.2em; color: green @endif">{{$stat['wrc_plus']}}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript" src="//cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.js"></script>
@endsection
