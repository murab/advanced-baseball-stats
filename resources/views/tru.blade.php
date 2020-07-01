@extends('_base')

@section('css')
    <link href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
@endsection

@section('content')
    <h1>
        True Pitcher Rankings
    </h1>

    <div class="row">
        <div class="col-sm-1">
            <label for="positionSelect">Position</label>
            <select class="form-control" id="positionSelect" name="positionSelect">
                <option value="sp">SP</option>
                <option value="rp" @if ($position == 'rp') selected @endif>RP</option>
            </select>
        </div>
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

    <table id="tru" class="table table-bordered table-hover table-sm">
        <thead>
            <tr>
                <td class="text-center">Rank</td>
                <td class="text-center">Name</td>
                <td class="text-center">Age</td>
{{--                <td>GS</td>--}}
                <td class="text-center">IP</td>
{{--                <td>K%</td>--}}
{{--                <td>BB%</td>--}}
                <td class="text-center">K-BB%</td>
                <td class="text-center">SwStr%</td>
                <td class="text-center">Velo</td>
{{--                <td>xWOBA</td>--}}
                <td class="text-center">Adj xWOBA</td>
{{--                <td>OppOPS</td>--}}
{{--                <td>True Score</td>--}}
            </tr>
        </thead>
        <tbody>
            @foreach($stats as $key => $stat)
                <tr>
                    <td class="text-center">{{$key+1}}</td>
                    <td>{{$stat->player['name']}}</td>
                    <td class="text-center">{{$stat['age']}}</td>
{{--                    <td>{{$stat['gs']}}</td>--}}
                    <td class="text-center">{{$stat['ip']}}</td>
{{--                    <td>{{number_format($stat['k_percentage'],1)}}</td>--}}
{{--                    <td>{{number_format($stat['bb_percentage'], 1)}}</td>--}}
                    <td class="text-center">{{number_format($stat['k_percentage'] - $stat['bb_percentage'], 1)}}</td>
                    <td class="text-center">{{number_format($stat['swstr_percentage'], 1)}}</td>
                    <td class="text-center">{{number_format($stat['velo'], 1)}}</td>
{{--                    <td>{{number_format($stat['xwoba'], 3)}}</td>--}}
                    <td class="text-center">{{ltrim(number_format($stat['adjusted_xwoba'], 3), '0')}}</td>
{{--                    <td>{{number_format($stat['oppops'], 3)}}</td>--}}
{{--                    <td>{{number_format($stat['tru'], 2)}}</td>--}}
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
            var t = $('#tru').DataTable({
                paging: false
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
                window.location.href = '/tru/'+year+'/'+position;
            });
        });
    </script>
@endsection