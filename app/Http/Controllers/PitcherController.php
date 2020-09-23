<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Stat;
use App\Player;
use Illuminate\Support\Facades\DB;

class PitcherController extends Controller
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

        return view('pitchers', [
            'page' => 'pitchers',
            'page_title' => "{$year} " . strtoupper($position) . " - {$long_position} Pitcher Rankings",
            'stats' => $stats,
            'years' => $years,
            'year' => $year,
            'position' => $position,
            'num' => count($stats),
        ]);
    }

    public function individual(Request $request, string $slug)
    {
        $pitcher = Player::where('slug', $slug)->first();

        $stats = Stat::where('player_id', $pitcher->id)->orderBy('year', 'asc')->get();

        return view('pitcher', [
            'page_title' => "{$pitcher->name}",
            'stats' => $stats,
            'player' => $pitcher,
        ]);
    }
}
