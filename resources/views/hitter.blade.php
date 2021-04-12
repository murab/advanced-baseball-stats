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
            <thead>
            <tr>
                <th>Year</th>
                <th>Age</th>
                <th>PA</th>
                <th>R</th>
                <th>AVG</th>
                <th>HR</th>
                <th>RBI</th>
                <th>SB</th>
                <th>BB%</th>
                <th>K%</th>
                <th>SwStr%</th>
                <th>Hard%</th>
                <th>Hard% Rank</th>
                <th>Sprint Rank</th>
                <th>K% Rank</th>
                <th>Brls Rank</th>
                <th>wRC+</th>
                <th style="font-weight: bold">Rank</th>
            </tr>
            </thead>
            <tbody>
            @foreach($stats as $key => $stat)
                <tr>
                    <td><a href="{{route('hitter_ranks', [$stat['year'], strtolower($stat['position'])])}}">{{$stat['year']}}</a></td>
                    <td>{{$stat['age']}}</td>
                    <td>{{$stat['pa']}}</td>
                    <td>{{$stat['r']}}</td>
                    <td>{{ltrim(number_format($stat['avg'], 3),"0")}}</td>
                    <td>{{$stat['hr']}}</td>
                    <td>{{$stat['rbi']}}</td>
                    <td>{{$stat['sb']}}</td>
                    <td>{{number_format($stat['bb_percentage'], 1)}}</td>
                    <td>{{number_format($stat['k_percentage'], 1)}}</td>
                    <td>{{number_format($stat['swstr_percentage'], 1)}}</td>
                    <td>{{number_format($stat['hardhit_percentage'], 1)}}</td>

                    <td>{{number_format($stat['hardhit_rank'])}}</td>
                    <td>{{number_format($stat['sprint_speed_rank'])}}</td>
                    <td>{{number_format($stat['k_percentage_rank'])}}</td>
                    <td>{{number_format($stat['brls_bbe_rank'])}}</td>

                    <td>{{$stat['wrc_plus']}}</td>
                    <td style="font-weight: bold">{{ $stat['rank_avg_rank'] }}</td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript" src="//cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var t = $('#hitters').DataTable({
                fixedHeader: true,
                responsive: {
                    details: false
                },
                searching: false,
                paging: false,
                columnDefs: [
                    { width: "6%", targets: "_all" }
                ]
            });
        });
    </script>
@endsection
