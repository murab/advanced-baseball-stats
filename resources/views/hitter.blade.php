@extends('_base')

@section('css')
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.css"/>
@endsection

@section('content')
    <h1 style="margin-bottom: 25px">
        {{$player['name']}} Stats
    </h1>

    <div class="table-responsive-md">
        <table id="hitters" class="table table-bordered table-hover table-sm" style="font-size: 12px">
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

                <th class="all" style="border-right: 1px solid black;">wRC+</th>

                {{--                <th class="all">Hard% Rank</th>--}}
{{--                <th class="all">BA Rank</th>--}}
{{--                <th class="all">SB/PA Rank</th>--}}
                <th class="all" style="border-right: 1px solid black;">Sprint Rank</th>
                <th class="all">Brls Rank</th>
                <th class="all">wRC+ Rank</th>

                <th class="all" style="font-weight: bold">Rank</th>
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

{{--                    <td>{{number_format($stat['hardhit_percentage'], 1)}}</td>--}}

                    <td style="border-right: 1px solid black;">{{$stat['wrc_plus']}}</td>
{{--                    <td>{{number_format($stat['hardhit_rank'])}}</td>--}}
                    <td style="border-right: 1px solid black;">{{number_format($stat['sprint_speed_rank'])}}</td>
{{--                    <td>{{number_format($stat['k_percentage_rank'])}}</td>--}}
{{--                    <td>{{number_format($stat['avg_rank'])}}</td>--}}
{{--                    <td>{{number_format($stat['sb_per_pa_rank'])}}</td>--}}
                    <td>{{number_format($stat['brls_rank'])}}</td>
                    <td>{{number_format($stat['wrcplus_rank'])}}</td>

                    <td style="font-weight: bold">{{ $stat['rank_avg_rank'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript" src="//cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.js"></script>
@endsection
