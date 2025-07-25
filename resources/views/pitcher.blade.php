@extends('_base')

@section('css')
    <link rel="stylesheet" type="text/css" href="//cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.css"/>
@endsection

@section('content')
    <h1 style="margin-bottom: 15px; text-align: center">
        {{$player['name']}} Stats
    </h1>

    <div style="text-align: center">
        <button id="expand" class="d-block d-sm-none btn btn-primary" style="margin: 0 auto; margin-bottom: 20px;">Expand</button>
    </div>

    @if ($has_sp_stats)
        <h2 class="text-center">As SP</h2>

        <div class="table-responsive-sm mb-5">
            <table id="pitchers" class="table-bordered table-hover table-sm" style="font-size: 12px; line-height: 16px; margin: 0 auto;">
                <thead style="text-align: center">
                <tr>
                    <th class="all">Year</th>
                    <th class="all">Age</th>
{{--                    <th class="all" style="border-right: 1px solid black;">Position</th>--}}
                    <th class="all">G</th>
                    <th class="all">IP</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">IP per G</th>
                    <th class="d-none d-md-table-cell"  class="all" style="border-right: 1px solid black;">K per G</th>
                    <th class="all">ERA</th>
                    <th class="all" style="border-right: 1px solid black;">WHIP</th>
                    <th class="all" >K%</th>
                    <th class="all" >BB%</th>
                    <th class="all" style="border-right: 1px solid black;">K-BB%</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">SwStr%</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">GB%</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">CSW%</th>
                    <th class="d-none d-md-table-cell"  style="border-right: 1px solid black;">Stuff+</th>
                    <th class="d-none d-md-table-cell"  style="border-right: 1px solid black;">Velo</th>
    {{--                <th class="all" style="border-right: 1px solid black;">IP per G Rank</th>--}}
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">K% or KpG<br>Rank</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">xERA Rank</th>
                    <th class="all" style="font-weight: bold">Rank</th>
                </tr>
                </thead>
                <tbody>
                @foreach($stats as $key => $stat)
                    @if ($stat->position == 'SP' && $stat->ip)
                    <tr>
                        <td><a href="{{route('pitcher_ranks', [$stat['year'], strtolower($stat['position'])])}}">{{$stat['year']}}</a></td>
                        <td>{{$stat['age']}}</td>
{{--                        <td style="border-right: 1px solid black;">{{$stat['position']}}</td>--}}
                        <td>{{$stat['g']}}</td>
                        <td>{{$stat['ip']}}</td>
                        @if (empty($stat['g']))
                            <td class="d-none d-md-table-cell"  style="border-right: 1px solid black;">N/A</td>
                        @else
                            <td class="d-none d-md-table-cell"  style="border-right: 1px solid black;">{{number_format($stat['ip'] / $stat['g'], 1)}}</td>
                        @endif
                        <td class="d-none d-md-table-cell"  style="border-right: 1px solid black;">{{number_format($stat['k_per_game'], 1)}}</td>
                        <td>{{number_format($stat['era'], 2)}}</td>
                        <td style="border-right: 1px solid black;">{{number_format($stat['whip'], 2)}}</td>
                        <td>{{number_format($stat['k_percentage'],1)}}</td>
                        <td>{{number_format($stat['bb_percentage'], 1)}}</td>
                        <td style="border-right: 1px solid black; @if ($stat['k_percentage'] - $stat['bb_percentage'] >= 17.5) color: green; @endif @if ($stat['k_percentage'] - $stat['bb_percentage'] >= 20) font-weight: bold; font-size: 1.2em; @endif">{{number_format($stat['k_percentage'] - $stat['bb_percentage'], 1)}}</td>
                        <td class="d-none d-md-table-cell"  style="border-right: 1px solid black;">{{number_format($stat['swstr_percentage'], 1)}}</td>
                        <td class="d-none d-md-table-cell"  style="border-right: 1px solid black;">{{number_format($stat['gb_percentage'], 1)}}</td>
                        <td class="d-none d-md-table-cell align-middle" style="border-right: 1px solid black;">{{number_format($stat['csw'], 1)}}</td>
                        <td class="d-none d-md-table-cell align-middle" style="border-right: 1px solid black;">{{$stat['stuff_plus']}}</td>
                        <td class="d-none d-md-table-cell"  style="border-right: 1px solid black;">{{number_format($stat['velo'], 1)}}</td>
    {{--                    <td style="border-right: 1px solid black;">{{ $stat['position'] != 'RP' ? abs($stat['ip_per_g_rank']) : ''}}</td>--}}
                        <td class="d-none d-md-table-cell" style="border-right: 1px solid black;">{{$stat['k_rank'] ?? ''}}</td>
                        <td class="d-none d-md-table-cell"  style="border-right: 1px solid black;">{{$stat['xwoba_rank'] ?? ''}}</td>
                        <td style="font-weight: bold">{{$stat['tru_rank']}}</td>

                    </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
    @if ($has_rp_stats)
        <h2 class="text-center">As RP</h2>

        <div class="table-responsive-sm">
            <table id="pitchers" class="table-bordered table-hover table-sm" style="font-size: 12px; line-height: 16px; margin: 0 auto;">
                <thead style="text-align: center">
                <tr>
                    <th class="all">Year</th>
                    <th class="all">Age</th>
{{--                    <th class="all" style="border-right: 1px solid black;">Position</th>--}}
                    <th class="all">G</th>
                    <th class="all">IP</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">IP per G</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">K per G</th>
                    <th class="all">ERA</th>
                    <th class="all" style="border-right: 1px solid black;">WHIP</th>
                    <th class="all">K%</th>
                    <th class="all">BB%</th>
                    <th class="all" style="border-right: 1px solid black;">K-BB%</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">SwStr%</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">GB%</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">CSW%</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">Stuff+</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">Velo</th>
                    {{--                <th class="all" style="border-right: 1px solid black;">IP per G Rank</th>--}}
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">K% or KpG<br>Rank</th>
                    <th class="d-none d-md-table-cell" style="border-right: 1px solid black;">xERA Rank</th>
                    <th class="all" style="font-weight: bold">Rank</th>
                </tr>
                </thead>
                <tbody>
                @foreach($stats as $key => $stat)
                    @if ($stat->position == 'RP' && $stat->ip)
                        <tr>
                            <td><a href="{{route('pitcher_ranks', [$stat['year'], strtolower($stat['position'])])}}">{{$stat['year']}}</a></td>
                            <td>{{$stat['age']}}</td>
{{--                            <td style="border-right: 1px solid black;">{{$stat['position']}}</td>--}}
                            <td>{{$stat['g']}}</td>
                            <td>{{$stat['ip']}}</td>
                            @if (empty($stat['g']))
                                <td class="d-none d-md-table-cell" style="border-right: 1px solid black;">N/A</td>
                            @else
                                <td class="d-none d-md-table-cell" style="border-right: 1px solid black;">{{number_format($stat['ip'] / $stat['g'], 1)}}</td>
                            @endif
                            <td class="d-none d-md-table-cell" style="border-right: 1px solid black;">{{number_format($stat['k_per_game'], 1)}}</td>
                            <td>{{number_format($stat['era'], 2)}}</td>
                            <td style="border-right: 1px solid black;">{{number_format($stat['whip'], 2)}}</td>
                            <td>{{number_format($stat['k_percentage'],1)}}</td>
                            <td>{{number_format($stat['bb_percentage'], 1)}}</td>
                            <td style="border-right: 1px solid black; @if ($stat['k_percentage'] - $stat['bb_percentage'] >= 20) color: green; @endif @if ($stat['k_percentage'] - $stat['bb_percentage'] >= 25) font-weight: bold; font-size: 1.2em; @endif">{{number_format($stat['k_percentage'] - $stat['bb_percentage'], 1)}}</td>
                            <td class="d-none d-md-table-cell" style="border-right: 1px solid black;">{{number_format($stat['swstr_percentage'], 1)}}</td>
                            <td class="d-none d-md-table-cell"  style="border-right: 1px solid black;">{{number_format($stat['gb_percentage'], 1)}}</td>
                            <td class="d-none d-md-table-cell align-middle" style="border-right: 1px solid black;">{{number_format($stat['csw'], 1)}}</td>
                            <td class="d-none d-md-table-cell align-middle" style="border-right: 1px solid black;">{{$stat['stuff_plus']}}</td>
                            <td class="d-none d-md-table-cell" style="border-right: 1px solid black;">{{number_format($stat['velo'], 1)}}</td>
                            {{--                    <td style="border-right: 1px solid black;">{{ $stat['position'] != 'RP' ? abs($stat['ip_per_g_rank']) : ''}}</td>--}}
                            <td class="d-none d-md-table-cell"  style="border-right: 1px solid black;">{{$stat['k_rank'] ?? ''}}</td>
                            <td class="d-none d-md-table-cell"  style="border-right: 1px solid black;">{{$stat['xwoba_rank'] ?? ''}}</td>
                            <td style="font-weight: bold">{{$stat['tru_rank']}}</td>

                        </tr>
                    @endif
                @endforeach
                </tbody>
            </table>
        </div>
    @endif
@endsection

@section('javascript')
    <script type="text/javascript" src="//cdn.datatables.net/v/bs4/jq-3.3.1/dt-1.10.24/fh-3.1.8/r-2.2.7/datatables.min.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#expand').on('click', function() {
                $('.d-none').removeClass('d-none');
            });

            if (window.screen.height <= 932) { // scroll to stats automatically on mobile
                $('html').animate({ scrollTop: $('table').offset().top }, 800);
            }
            $(window).on("orientationchange", function(event) {
                if (window.screen.height <= 932) { // scroll to stats automatically on mobile
                    $('html').animate({ scrollTop: $('table').offset().top }, 800);
                }
            });
        });
    </script>
@endsection
