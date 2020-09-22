@extends('_base')

@section('css')
    <link href="//cdn.datatables.net/1.10.21/css/jquery.dataTables.min.css" rel="stylesheet">
@endsection

@section('content')
    <h1 style="margin-top: 25px">
        Pitcher Rankings
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

    <table id="tru" class="table table-bordered table-hover table-sm" style="font-size: 12px">
        <thead>
            <tr>
                <td class="text-center">Rank</td>
                <td class="text-center">Name</td>
                <td class="text-center">Age</td>
                <td class="text-center">G</td>
                <td class="text-center">IP</td>
                <td class="text-center">IP per G</td>
                <td>K%</td>
                <td>BB%</td>
                <td class="text-center">K-BB%</td>
                <td class="text-center">SwStr%</td>
                <td class="text-center">Velo</td>
{{--                <td>xWOBA</td>--}}
                <td class="text-center">GB%</td>
                <td class="text-center">IP per G Rank</td>
                <td class="text-center">K% Rank</td>
                <td class="text-center">xERA Rank</td>
                <td class="text-center" style="font-weight: bold">Average Rank</td>
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
                    <td>{{$stat['g']}}</td>
                    <td class="text-center">{{$stat['ip']}}</td>
                    <td class="text-center">{{number_format($stat['ip'] / $stat['g'], 1)}}</td>
                    <td>{{number_format($stat['k_percentage'],1)}}</td>
                    <td>{{number_format($stat['bb_percentage'], 1)}}</td>
                    <td class="text-center">{{number_format($stat['k_percentage'] - $stat['bb_percentage'], 1)}}</td>
                    <td class="text-center">{{number_format($stat['swstr_percentage'], 1)}}</td>
                    <td class="text-center">{{number_format($stat['velo'], 1)}}</td>
{{--                    <td>{{number_format($stat['xwoba'], 3)}}</td>--}}
                    <td class="text-center">{{number_format($stat['gb_percentage'], 1)}}</td>
                    <td class="text-center">{{ $position != 'rp' ? abs($stat['ip_per_g_rank'] - $num) + 1 : ''}}</td>
                    <td class="text-center">{{abs($stat['k_rank'] - $num) + 1 ?? ''}}</td>
                    <td class="text-center">{{abs($stat['xwoba_rank'] - $num) + 1 ?? ''}}</td>

                    @if ($position == 'sp')
                        <td class="text-center" style="font-weight: bold">{{ number_format(abs((($stat['ip_per_g_rank']-1 + $stat['k_rank']-1 + $stat['xwoba_rank']-1) / 3 - $num)), 1) }}</td>
                    @else
                        <td class="text-center" style="font-weight: bold">{{ number_format(abs((($stat['k_rank']-1 + $stat['xwoba_rank']-1) / 2 - $num)), 1) }}</td>
                    @endif

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
                window.location.href = '/pitchers/'+year+'/'+position;
            });
        });
    </script>
@endsection
