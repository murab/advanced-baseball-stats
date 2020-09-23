@extends('_base')

@section('css')
    <link href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
@endsection

@section('content')
    <h1>
        {{$player['name']}} Stats
    </h1>

    <div class="table-responsive-md">
        <table id="tru" class="table table-bordered table-hover table-sm" style="font-size: 12px">
            <thead>
            <tr>
                <td>Year</td>
                <td>Age</td>
                <td>Position</td>
                <td>G</td>
                <td>IP</td>
                <td>IP per G</td>
                <td>K%</td>
                <td>BB%</td>
                <td>K-BB%</td>
                <td>SwStr%</td>
                <td>Velo</td>
                <td>GB%</td>
                <td>IP per G Rank</td>
                <td>K% Rank</td>
                <td>xERA Rank</td>
                <td style="font-weight: bold">Positional Rank</td>
            </tr>
            </thead>
            <tbody>
            @foreach($stats as $key => $stat)
                <tr>
                    <td>{{$stat['year']}}</a></td>
                    <td>{{$stat['age']}}</td>
                    <td>{{$stat['position']}}</td>
                    <td>{{$stat['g']}}</td>
                    <td>{{$stat['ip']}}</td>
                    <td>{{number_format($stat['ip'] / $stat['g'], 1)}}</td>
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
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {

        });
    </script>
@endsection
