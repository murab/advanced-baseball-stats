@extends('_base')

@section('css')
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.css"/>
@endsection

@section('content')
    <h1 style="margin-bottom: 25px">
        {{$player['name']}} Stats
    </h1>

    <div class="table-responsive-md">
        <table id="pitchers" class="table table-bordered table-hover table-sm" style="font-size: 12px">
            <thead style="text-align: center">
            <tr>
                <th class="all">Year</th>
                <th class="all">Age</th>
                <th class="all">Position</th>
                <th class="all">G</th>
                <th class="all">IP</th>
                <th class="all">IP per G</th>
                <th class="all">K per G</th>
                <th class="all">K%</th>
                <th class="all">BB%</th>
                <th class="all">K-BB%</th>
                <th class="all">SwStr%</th>
                <th class="all">Velo</th>
                <th class="all">GB%</th>
                <th class="all">IP per G Rank</th>
                <th class="all">K% or KpS Rank</th>
                <th class="all">xERA Rank</th>
                <th class="all" style="font-weight: bold">Rank</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stats as $key => $stat)
                <tr>
                    <td><a href="{{route('pitcher_ranks', [$stat['year'], strtolower($stat['position'])])}}">{{$stat['year']}}</a></td>
                    <td>{{$stat['age']}}</td>
                    <td>{{$stat['position']}}</td>
                    <td>{{$stat['g']}}</td>
                    <td>{{$stat['ip']}}</td>
                    <td>{{number_format($stat['ip'] / $stat['g'], 1)}}</td>
                    <td>{{number_format($stat['k_per_game'], 1)}}</td>
                    <td>{{number_format($stat['k_percentage'],1)}}</td>
                    <td>{{number_format($stat['bb_percentage'], 1)}}</td>
                    <td>{{number_format($stat['k_percentage'] - $stat['bb_percentage'], 1)}}</td>
                    <td>{{number_format($stat['swstr_percentage'], 1)}}</td>
                    <td>{{number_format($stat['velo'], 1)}}</td>
                    <td>{{number_format($stat['gb_percentage'], 1)}}</td>
                    <td>{{ $stat['position'] != 'RP' ? abs($stat['ip_per_g_rank']) : ''}}</td>
                    <td>{{$stat['k_rank'] ?? ''}}</td>
                    <td>{{$stat['xwoba_rank'] ?? ''}}</td>
                    <td style="font-weight: bold">{{$stat['tru_rank']}}</td>

                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript" src="//cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.js"></script>
@endsection
