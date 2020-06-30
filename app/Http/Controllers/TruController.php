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
        ])->orderBy('tru', 'desc')->get();

        $years = DB::table('stats')->groupBy('year')->orderBy('year', 'desc')->pluck('year')->toArray();

        if (!in_array(date('Y'),$years)) {
            array_unshift($years, date('Y'));
        }

        return view('tru', [
            'stats' => $stats,
            'years' => $years,
            'year' => $year,
            'position' => $position,
        ]);
    }
}
