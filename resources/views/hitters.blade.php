@extends('_base')

@section('css')
    <link href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
@endsection

@section('content')
    <h1>
        Automated Hitter Rankings
    </h1>

    <div class="row">
{{--        <div class="col-sm-1">--}}
{{--            <label for="positionSelect">Position</label>--}}
{{--            <select class="form-control" id="positionSelect" name="positionSelect">--}}
{{--                <option value="sp">SP</option>--}}
{{--                <option value="rp" @if ($position == 'rp') selected @endif>RP</option>--}}
{{--            </select>--}}
{{--        </div>--}}
        {{--    </div>--}}
        {{--    <div class="form-group">--}}
        <div class="col-sm-2">
            <label for="yearSelect">Year</label>
            <select class="form-control" id="yearSelect" name="yearSelect">
                @foreach ($years as $oneYear)
                    <option value="{{$oneYear}}" @if ($year == $oneYear) selected @endif>{{$oneYear}}</option>
                @endforeach
            </select>
        </div>
    </div>

    <table id="hitters" class="table table-bordered table-hover table-sm" style="font-size: 12px">
        <thead>
        <tr>
            <td class="text-center">Rank</td>
            <td class="text-center">Name</td>
            <td class="text-center">Age</td>
            <td class="text-center">PA</td>
            <td class="text-center">R</td>
            <td class="text-center">AVG</td>
            <td>HR</td>
            <td>RBI</td>
            <td class="text-center">SB</td>
            <td class="text-center">BB%</td>
            <td class="text-center">K%</td>
            <td class="text-center">SwStr%</td>
            <td class="text-center">Hard Hit%</td>
            <td class="text-center">wRC+</td>
            <td class="text-center" style="font-weight: bold">Hard Hit% - K%</td>
        </tr>
        </thead>
        <tbody>
        @foreach($stats as $key => $stat)
            <tr>
                <td class="text-center">{{$key+1}}</td>
                <td>{{$stat->player['name']}}</td>
                <td class="text-center">{{$stat['age']}}</td>
                <td class="text-center">{{$stat['pa']}}</td>
                <td class="text-center">{{$stat['r']}}</td>
                <td class="text-center">{{number_format($stat['avg'], 3)}}</td>
                <td>{{$stat['hr']}}</td>
                <td>{{$stat['rbi']}}</td>
                <td class="text-center">{{$stat['sb']}}</td>
                <td class="text-center">{{number_format($stat['bb_percentage'], 1)}}</td>
                <td class="text-center">{{number_format($stat['k_percentage'], 1)}}</td>
                <td class="text-center">{{number_format($stat['swstr_percentage'], 1)}}</td>
                <td class="text-center">{{number_format($stat['hardhit_percentage'], 1)}}</td>
                <td class="text-center">{{$stat['wrc_plus']}}</td>
                <td class="text-center" style="font-weight: bold">{{number_format($stat['hardhit_percentage'] - $stat['k_percentage'], 1)}}</td>
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
                window.location.href = '/ranks/'+year+'/'+position;
            });
        });
    </script>
@endsection
