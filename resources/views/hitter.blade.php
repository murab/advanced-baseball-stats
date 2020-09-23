@extends('_base')

@section('css')
    <link href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
@endsection

@section('content')
    <h1>
        {{$player['name']}} Stats
    </h1>

    <table id="hitters" class="table table-bordered table-hover table-sm" style="font-size: 12px">
        <thead>
        <tr>
            <td>Year</td>
            <td>Age</td>
            <td>PA</td>
            <td>R</td>
            <td>AVG</td>
            <td>HR</td>
            <td>RBI</td>
            <td>SB</td>
            <td>BB%</td>
            <td>K%</td>
            <td>SwStr%</td>
            <td>Hard Hit%</td>
            <td>wRC+</td>
            <td style="font-weight: bold">Hard Hit% - K%</td>
        </tr>
        </thead>
        <tbody>
        @foreach($stats as $key => $stat)
            <tr>
                <td>{{$stat['year']}}</td>
                <td>{{$stat['age']}}</td>
                <td>{{$stat['pa']}}</td>
                <td>{{$stat['r']}}</td>
                <td>{{number_format($stat['avg'], 3)}}</td>
                <td>{{$stat['hr']}}</td>
                <td>{{$stat['rbi']}}</td>
                <td>{{$stat['sb']}}</td>
                <td>{{number_format($stat['bb_percentage'], 1)}}</td>
                <td>{{number_format($stat['k_percentage'], 1)}}</td>
                <td>{{number_format($stat['swstr_percentage'], 1)}}</td>
                <td>{{number_format($stat['hardhit_percentage'], 1)}}</td>
                <td>{{$stat['wrc_plus']}}</td>
                <td style="font-weight: bold">{{number_format($stat['hardhit_percentage'] - $stat['k_percentage'], 1)}}</td>
            </tr>
        @endforeach
        </tbody>
    </table>
@endsection

@section('javascript')
    <script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script type="text/javascript" src="//cdn.datatables.net/1.10.21/js/jquery.dataTables.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {

        });
    </script>
@endsection
