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

        if (count($stats) == 0) {
            $year = $year - 1;
            $stats = Stat::where([
                'year' => $year,
                'position' => strtoupper($position),
                ['tru', '<>', null],
            ])->orderBy('tru_rank', 'asc')->get();
        }

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
            'min_ip' => Stat::calculateMinInningsPitched($year, $position),
            'position' => $position,
            'num' => count($stats),
            'max_width' => true,
        ]);
    }

    public function filter(Request $request, $year = null, ?string $position = 'sp', ?int $min_ip)
    {
        if (empty($year)) { $year = date('Y'); }
        if (empty($min_ip)) { $min_ip = Stat::calculateMinInningsPitched($year, $position); }

        if ($position == 'sp') {
            $stats = Stat::where([
                'year' => $year,
                'position' => strtoupper($position),
                ['ip', '>=', $min_ip],
            ])->with('player')->orderBy('k_per_game', 'desc')->get();
        } else { // rp
            $stats = Stat::where([
                'year' => $year,
                'position' => strtoupper($position),
                ['ip', '>=', $min_ip],
            ])->with('player')->orderBy('k_percentage', 'desc')->get();
        }

        $arr = [];

        foreach ($stats as $i => $player) {
            $arr[$player['id']]['k_rank'] = $i;
        }

        $stats = Stat::where([
            'year' => $year,
            'position' => strtoupper($position),
            ['ip', '>=', $min_ip],
        ])->with('player')->orderBy('xwoba', 'asc')->get();

        foreach ($stats as $i => $player) {
            $stats[$i]['ip_per_g'] = number_format($player['ip'] / $player['g'], 1);
            $stats[$i]['k_per_game'] = number_format($player['k_per_game'], 1);
            $stats[$i]['era'] = number_format($player['era'], 2);
            $stats[$i]['whip'] = number_format($player['whip'], 2);
            $stats[$i]['k_percentage'] = number_format($player['k_percentage'],1);
            $stats[$i]['bb_percentage'] = number_format($player['bb_percentage'],1);
            $stats[$i]['kbb_percentage'] = number_format($player['k_percentage'] - $player['bb_percentage'], 1);
            $stats[$i]['swstr_percentage'] = number_format($player['swstr_percentage'], 1);
            $stats[$i]['gb_percentage'] = number_format($player['gb_percentage'], 1);
            $stats[$i]['csw'] = number_format($player['csw'], 1);
            $stats[$i]['velo'] = number_format($player['velo'], 1);
            $stats[$i]['k_rank'] = $arr[$player['id']]['k_rank'] + 1;
            $stats[$i]['xwoba_rank'] = $i + 1;
            $stats[$i]['avg_rank'] = ($stats[$i]['k_rank'] + $stats[$i]['xwoba_rank']) / 2;
        }

        $stats = $stats->toArray();

        usort($stats, function($a, $b) {
            if ($a['avg_rank'] == $b['avg_rank']) return 0;
            return ($a['avg_rank'] < $b['avg_rank']) ? -1 : 1;
        });

        echo json_encode($stats);
    }

    public function individual(Request $request, string $slug)
    {
        $pitcher = Player::where('slug', $slug)->first();

        $stats = Stat::where('player_id', $pitcher->id)->orderBy('year', 'asc')->get();

        return view('pitcher', [
            'page_title' => "{$pitcher->name} Stats",
            'stats' => $stats,
            'player' => $pitcher,
            'max_width' => true,
        ]);
    }
}
