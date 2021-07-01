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
        <div class="col-xl-9 col-lg-7 col-md-5" style="text-align: right">
            <div>Last updated: @if (date('G') > 7) {{ date('F j, Y') }}@else {{ date('F j, Y', strtotime('yesterday')) }}@endif</div>
            <div id="playerSets" style="margin-bottom: 5px"></div>
            <div id="saveSet" style="margin-bottom: 5px">Save current search as <input type="text" id="saveSetName"><button id="saveSetBtn">Save</button><button id="deleteSetBtn">Delete</button></div>
            <br />
        </div>
    </div>

    <div id="app">
        <pitchers :data='{!! json_encode($stats) !!}'></pitchers>
    </div>

@endsection

@section('javascript')

@endsection
