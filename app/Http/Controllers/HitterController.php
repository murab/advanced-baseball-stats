<?php

namespace App\Http\Controllers;

use App\Player;
use App\Stat;
use Illuminate\Http\Request;
use App\Hitter;
use Illuminate\Support\Facades\DB;
use function Ramsey\Uuid\v1;

class HitterController extends Controller
{
    public function index(Request $request, $year = null)
    {
        if (empty($year)) { $year = date('Y'); }

        $min_pa = Stat::calculateMinPlateAppearances($year);

        $stats = Hitter::where([
            'year' => $year,
            ['brls_per_pa', '<>', null],
        ])->orderBy('rank_avg', 'asc')->get();

        if (count($stats) == 0) {
            $year = $year - 1;
            $min_pa = Stat::calculateMinPlateAppearances($year);
            $stats = Hitter::where([
                'year' => $year,
                ['brls_per_pa', '<>', null],
            ])->orderBy('rank_avg', 'asc')->get();
        }

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
            'min_pa' => $min_pa,
            'max_width' => true,
        ]);
    }

    public function individual(Request $request, string $slug)
    {
        $hitter = Player::where('slug', $slug)->first();

        $stats = Hitter::where('player_id', $hitter->id)->orderBy('year', 'asc')->get();

        $bats = false;
        foreach ($stats as $stat) {
            if ($stat['bats']) $bats = $stat['bats'];
        }

        return view('hitter', [
            'page_title' => "{$hitter->name} Stats",
            'stats' => $stats,
            'player' => $hitter,
            'bats' => $bats,
            'max_width' => true,
        ]);
    }

    public function filter(Request $request, $year, $pa_min = 0, $pa_per_g_min = 0, $sb_min = 0)
    {
        $stats = Hitter::where([
            'year' => $year,
            ['pa', '>=', $pa_min],
            ['pa_per_g', '>=', $pa_per_g_min],
            ['sb', '>=', $sb_min],
        ])->with('player')->orderBy('xwoba', 'desc')->get();

        $arr = [];

        foreach ($stats as $i => $player) {
            $arr[$player['id']]['xwoba_rank'] = $i;
        }

        $stats = Hitter::where([
            'year' => $year,
            ['pa', '>=', $pa_min],
            ['pa_per_g', '>=', $pa_per_g_min],
            ['sb', '>=', $sb_min],
        ])->with('player')->orderBy('sprint_speed', 'desc')->get();

        foreach ($stats as $i => $player) {
            $arr[$player['id']]['sprint_speed_rank'] = $i;
        }

        $stats = Hitter::where([
            'year' => $year,
            ['pa', '>=', $pa_min],
            ['pa_per_g', '>=', $pa_per_g_min],
            ['sb', '>=', $sb_min],
        ])->with('player')->orderBy('pulled_flyballs_per_g', 'desc')->get();

        foreach ($stats as $i => $player) {
            $arr[$player['id']]['pulled_fb_g_rank'] = $i;
        }

        $stats = Hitter::where([
            'year' => $year,
            ['pa', '>=', $pa_min],
            ['pa_per_g', '>=', $pa_per_g_min],
            ['sb', '>=', $sb_min],
        ])->with('player')->orderBy('brls_per_pa', 'desc')->get();


        foreach ($stats as $i => $player) {
            $stats[$i]['pa_per_g'] = ltrim(number_format($stats[$i]['pa_per_g'], 2));
            $stats[$i]['avg'] = ltrim(number_format($stats[$i]['avg'], 3),"0");
            $stats[$i]['xba'] = ltrim(number_format($stats[$i]['xba'], 3),"0");
            $stats[$i]['bb_percentage'] = number_format($stats[$i]['bb_percentage'], 1);
            $stats[$i]['k_percentage'] = number_format($stats[$i]['k_percentage'], 1);
            $stats[$i]['swstr_percentage'] = number_format($stats[$i]['swstr_percentage'], 1);
            $stats[$i]['def'] = number_format(round($stats[$i]['def'], 1), 1);
            $stats[$i]['brls_per_pa'] = number_format(round($stats[$i]['brls_per_pa']));
            $stats[$i]['brls_rank'] = $i+1;
            $stats[$i]['sprint_speed_rank'] = $arr[$player['id']]['sprint_speed_rank']+1;
            $stats[$i]['xwoba_rank'] = $arr[$player['id']]['xwoba_rank']+1;
            $stats[$i]['pulled_fb_g_rank'] = $arr[$player['id']]['pulled_fb_g_rank']+1;
            $stats[$i]['avg_rank'] = $stats[$i]['pulled_fb_g_rank'] < $stats[$i]['xwoba_rank'] ? ($stats[$i]['pulled_fb_g_rank'] + $stats[$i]['xwoba_rank']) / 2.0 : $stats[$i]['xwoba_rank'];
        }

        $stats = $stats->toArray();

        usort($stats, function($a, $b) {
            if ($a['avg_rank'] == $b['avg_rank']) return 0;
            return ($a['avg_rank'] < $b['avg_rank']) ? -1 : 1;
        });

        echo json_encode($stats);
    }
}
