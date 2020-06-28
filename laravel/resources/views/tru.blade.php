@extends('_base')

@section('css')
    <link href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
@endsection

@section('content')
    <h1>
        True Pitcher Rankings
    </h1>

    <div class="form-group">
        <label for="positionSelect">Position</label>
        <select class="form-control" id="positionSelect" name="positionSelect">
            <option value="sp">SP</option>
            <option value="rp" @if ($position == 'rp') selected @endif>RP</option>
        </select>
    </div>
    <div class="form-group">
        <label for="yearSelect">Year</label>
        <select class="form-control" id="yearSelect" name="yearSelect">
            @foreach ($years as $oneYear)
                <option value="{{$oneYear}}" @if ($year == $oneYear) selected @endif>{{$oneYear}}</option>
            @endforeach
        </select>
    </div>

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
                <td>SwStr%</td>
                <td>Velo</td>
                <td>True Score</td>
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
                    <td>{{number_format($stat['k_percentage'],1)}}</td>
                    <td>{{number_format($stat['bb_percentage'], 1)}}</td>
                    <td>{{number_format($stat['k_percentage'] - $stat['bb_percentage'], 1)}}</td>
                    <td>{{number_format($stat['swstr_percentage'], 1)}}</td>
                    <td>{{number_format($stat['velo'], 1)}}</td>
                    <td>{{number_format($stat['tru'], 2)}}</td>
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
            $('#positionSelect, #yearSelect').change(function() {
                var year = $('#yearSelect').val();
                var position = $('#positionSelect').val();
                window.location.href = '/tru/'+year+'/'+position;
            });
        });
    </script>
@endsection