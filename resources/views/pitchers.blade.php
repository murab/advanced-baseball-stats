@extends('_base')

@section('css')
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.css"/>
    <link rel="canonical" href="{{route('pitcher_ranks', [$year, $position])}}" />
@endsection

@section('content')
    <h1>
        Pitcher Rankings
    </h1>

    @if ($position == 'sp')
        <p>Minimum 3.0 IP per appearance, 10 total IP</p>
    @else
        <p>Less than 3.0 IP per appearance, at least 5 total IP</p>
    @endif

    <div class="row">
        <div class="col-xl-1 col-lg-2 col-md-3">
            <label for="positionSelect">Position</label>
            <select class="form-control" id="positionSelect" name="positionSelect">
                <option value="sp">SP</option>
                <option value="rp" @if ($position == 'rp') selected @endif>RP</option>
            </select>
        </div>
        <div class="col-xl-2 col-lg-3 col-md-4">
            <label for="yearSelect">Year</label>
            <select class="form-control" id="yearSelect" name="yearSelect">
                @foreach ($years as $oneYear)
                    <option value="{{$oneYear}}" @if ($year == $oneYear) selected @endif>{{$oneYear}}</option>
                @endforeach
            </select>
        </div>
        <div class="col-xl-9 col-lg-7 col-md-5" style="text-align: right">Last updated: @if (date('G') > 7) {{ date('F j, Y') }}@else {{ date('F j, Y', strtotime('yesterday')) }}@endif</div>
    </div>

    <div class="table-responsive-md">
        <table id="pitchers" class="table table-bordered table-hover table-sm" style="font-size: 12px">
            <thead>
            <tr>
                <td>Rank</td>
                <td style="width: 125px">Name</td>
                <td style="border-right: 1px solid black;">Age</td>
{{--                <td>G</td>--}}
                <td>IP</td>
                <td style="border-right: 1px solid black;">IPpG</td>
                <td>K%</td>
                <td>BB%</td>
                <td style="border-right: 1px solid black;">K-BB%</td>
                <td style="border-right: 1px solid black;">SwStr%</td>
                <td style="border-right: 1px solid black;">GB%</td>
                <td style="border-right: 1px solid black;"><a href="https://www.pitcherlist.com/csw-rate-an-intro-to-an-important-new-metric/">CSW%</a></td>
                <td style="border-right: 1px solid black;">Velo</td>
                <td>IPpG Rank</td>
                <td>K% Rank</td>
                <td>xERA Rank</td>
                <td style="font-weight: bold">Avg</td>
            </tr>
            </thead>
            <tbody>
            @foreach($stats as $key => $stat)
                <tr>
                    <td style="font-size: 1.2em;" class="align-middle">{{$key+1}}</td>
                    <td class="align-middle" style="text-align: left;font-size: 1.2em;"><a href={{route('pitcher', $stat->player['slug'])}}>{{$stat->player['name']}}</a></td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{$stat['age']}}</td>
{{--                    <td>{{$stat['g']}}</td>--}}
                    <td class="align-middle">{{$stat['ip']}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['ip'] / $stat['g'], 1)}}</td>
                    <td class="align-middle">{{number_format($stat['k_percentage'],1)}}</td>
                    <td class="align-middle">{{number_format($stat['bb_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['k_percentage'] - $stat['bb_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['swstr_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['gb_percentage'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['csw'], 1)}}</td>
                    <td class="align-middle" style="border-right: 1px solid black;">{{number_format($stat['velo'], 1)}}</td>
                    <td class="align-middle">{{ $position != 'rp' ? $stat['ip_per_g_rank'] : ''}}</td>
                    <td class="align-middle">{{ $stat['k_rank'] ?? ''}}</td>
                    <td class="align-middle">{{ $stat['xwoba_rank'] ?? ''}}</td>

                    @if ($position == 'sp')
                        <td class="align-middle" style="font-weight: bold;font-size: 1.2em;">{{ number_format(($stat['ip_per_g_rank'] + $stat['k_rank'] + $stat['xwoba_rank']) / 3, 1) }}</td>
                    @else
                        <td class="align-middle" style="font-weight: bold;font-size: 1.2em;">{{ number_format(($stat['k_rank'] + $stat['xwoba_rank']) / 2, 1) }}</td>
                    @endif
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
@endsection

@section('javascript')
    <script type="text/javascript" src="https://cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            var t = $('#pitchers').DataTable({
                fixedHeader: true,
                paging: false,
                columnDefs: [
                    { "width": "5.5%", "targets": [0,2,3,4,5,6,7,8,9,10,11,12,13,14,15] }
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
                window.location.href = '/pitchers/'+year+'/'+position;
            });
        });
    </script>
@endsection
