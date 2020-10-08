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
                <td style="border-right: 1px solid black;">Age</td>
                <td style="border-right: 1px solid black;">PA</td>
                <td>R</td>
                <td>AVG</td>
                <td>HR</td>
                <td>RBI</td>
                <td style="border-right: 1px solid black;">SB</td>
                <td>BB%</td>
                <td style="border-right: 1px solid black;">K%</td>
                <td style="border-right: 1px solid black;">SwStr%</td>
                <td style="border-right: 1px solid black;">Hard%</td>
                <td style="border-right: 1px solid black;">wRC+</td>
                <td style="font-weight: bold">Hard-K%</td>
            </tr>
            </thead>
            <tbody>
            @foreach($stats as $key => $stat)
                <tr>
                    <td class="align-middle" style="font-size: 1.2em;">{{$key+1}}</td>
                    <td class="align-middle" style="text-align: left; font-size: 1.2em;"><a href="{{route('hitter', $stat->player['slug'])}}">{{$stat->player['name']}}</a></td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{$stat['age']}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{$stat['pa']}}</td>
                    <td class="align-middle">{{$stat['r']}}</td>
                    <td class="align-middle">{{number_format($stat['avg'], 3)}}</td>
                    <td class="align-middle">{{$stat['hr']}}</td>
                    <td class="align-middle">{{$stat['rbi']}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{$stat['sb']}}</td>
                    <td class="align-middle">{{number_format($stat['bb_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['k_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['swstr_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['hardhit_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{$stat['wrc_plus']}}</td>
                    <td class="align-middle" style="font-weight: bold;font-size: 1.2em;">{{number_format($stat['hardhit_percentage'] - $stat['k_percentage'], 1)}}</td>
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
                order: [[ 14, "desc" ]],
                columnDefs: [
                    { "width": "6%", "targets": [0,2,3,4,5,6,7,8,9,10,11,12,13,14] }
                ]
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
