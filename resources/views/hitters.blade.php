@extends('_base')

@section('css')
    <link href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
    <link rel="canonical" href="{{route('hitter_ranks', [$year])}}" />
@endsection

@section('content')
    <h1>
        Hitter Rankings
    </h1>

    <div class="row">
        <div class="col-xl-2 col-md-3">
            <label for="yearSelect">Year</label>
            <select class="form-control" id="yearSelect" name="yearSelect">
                @foreach ($years as $oneYear)
                    <option value="{{$oneYear}}" @if ($year == $oneYear) selected @endif>{{$oneYear}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-xl-10 col-md-9" style="text-align: right">Last updated: @if (date('G') > 7) {{ date('F j, Y') }}@else {{ date('F j, Y', strtotime('yesterday')) }}@endif</div>
    </div>

    <div class="table-responsive-md">
        <table id="hitters" class="table table-bordered table-hover table-sm" style="font-size: 12px">
            <thead>
            <tr>
                <td>Rank</td>
                <td style="width: 125px">Name</td>
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
                    <td style="font-size: 1.25em;">{{$key+1}}</td>
                    <td style="text-align: left; font-size: 1.25em;"><a href="{{route('hitter', $stat->player['slug'])}}">{{$stat->player['name']}}</a></td>
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
                    <td style="font-weight: bold; font-size: 1.25em;">{{number_format($stat['hardhit_percentage'] - $stat['k_percentage'], 1)}}</td>
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
            var t = $('#hitters').DataTable({
                paging: false,
                order: [[ 14, "desc" ]]
            });

            // manage index column
            t.on( 'order.dt ', function () {
                t.column(0, {search:'applied', order:'applied'}).nodes().each( function (cell, i) {
                    cell.innerHTML = i+1;
                } );
            } ).draw();

            $('#positionSelect, #yearSelect').change(function() {
                var year = $('#yearSelect').val();
                var position = $('#positionSelect').val();
                window.location.href = '/hitters/'+year;
            });
        });
    </script>
@endsection
