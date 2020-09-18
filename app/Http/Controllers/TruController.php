<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Stat;
use Illuminate\Support\Facades\DB;

class TruController extends Controller
{
    public function index(Request $request, $year = null, ?string $position = 'sp')
    {
        if (empty($year)) { $year = date('Y'); }

        $stats = Stat::where([
            'year' => $year,
            'position' => strtoupper($position),
            ['tru', '<>', null],
        ])->orderBy('tru_rank', 'asc')->get();

        $years = DB::table('stats')->groupBy('year')->orderBy('year', 'desc')->pluck('year')->toArray();

        if (!in_array(date('Y'),$years)) {
            array_unshift($years, date('Y'));
        }

        $long_position = '';
        if ($position == 'sp') {
            $long_position = 'Starting';
        } else if ($position == 'rp') {
            $long_position = 'Relief';
        }

        return view('tru', [
            'page_title' => "{$year} " . strtoupper($position) . " - {$long_position} Pitcher Rankings",
            'stats' => $stats,
            'years' => $years,
            'year' => $year,
            'position' => $position,
            'num' => count($stats),
        ]);
    }
}
