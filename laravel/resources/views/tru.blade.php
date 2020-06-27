@extends('_base')

@section('css')
    <link href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
@endsection

@section('content')
    <h1>
        True Pitcher Rankings
    </h1>
    <table id="tru" class="table table-bordered table-hover table-sm">
        <thead>
            <tr>
                <td>Rank</td>
                <td>Name</td>
                <td>Age</td>
                <td>GS</td>
                <td>IP</td>
                <td>K%</td>
                <td>BB%</td>
                <td>K-BB%</td>
                <td>ERA</td>
                <td>WHIP</td>
                <td>Velo</td>
            </tr>
        </thead>
        <tbody>
            @foreach($stats as $key => $stat)
                <tr>
                    <td>{{$key+1}}</td>
                    <td>{{$stat->player['name']}}</td>
                    <td>{{$stat['age']}}</td>
                    <td>{{$stat['gs']}}</td>
                    <td>{{$stat['ip']}}</td>
                    <td>{{$stat['k_percentage']}}</td>
                    <td>{{$stat['bb_percentage']}}</td>
                    <td>{{$stat['kbb_percentage']}}</td>
                    <td>{{$stat['era']}}</td>
                    <td>{{$stat['whip']}}</td>
                    <td>{{$stat['velo']}}</td>
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
            $('#tru').DataTable({
                paging: false,
            });
        });
    </script>
@endsection