<?php

namespace App\Http\Controllers;

use App\Player;
use App\Stat;
use Illuminate\Http\Request;
use App\Hitter;
use Illuminate\Support\Facades\DB;

class HitterController extends Controller
{
    public function index(Request $request, $year = null)
    {
        if (empty($year)) { $year = date('Y'); }

        $stats = Hitter::where([
            'year' => $year,
            ['pa', '>=', 100],
        ])->orderBy('hardhit_percentage', 'desc')->get();

        $years = DB::table('stats')->groupBy('year')->orderBy('year', 'desc')->pluck('year')->toArray();

        if (!in_array(date('Y'),$years)) {
            array_unshift($years, date('Y'));
        }

        return view('hitters', [
            'page' => 'hitters',
            'page_title' => "{$year} - Hitter Rankings",
            'stats' => $stats,
            'years' => $years,
            'year' => $year,
            'num' => count($stats),
        ]);
    }

    public function individual(Request $request, string $slug)
    {
        $hitter = Player::where('slug', $slug)->first();

        $stats = Hitter::where('player_id', $hitter->id)->orderBy('year', 'asc')->get();

        return view('hitter', [
            'page_title' => "{$hitter->name} Stats",
            'stats' => $stats,
            'player' => $hitter,
        ]);
    }
}
