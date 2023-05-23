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

        return view('hitter', [
            'page_title' => "{$hitter->name} Stats",
            'stats' => $stats,
            'player' => $hitter,
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

        // select name, *, rank() over (order by xwoba desc) as xwoba_rank, RANK() OVER (ORDER BY brls_per_pa DESC) as brls_rank from hitters inner join players p on p.id = hitters.player_id where year = 2023 and pa >= 50 and pa_per_g >= 3.6 and sb >= 0
//
//        $stats = \DB::table('hitters')
//            ->join('stats', 'stats.player_id', '=', 'hitters.player_id')
//            ->select('*, RANK() OVER (ORDER BY xwoba DESC) as xwoba_rank, RANK() OVER (ORDER BY brls_per_pa DESC) as brls_per_pa_rank')->toSql();
//echo $stats;

        $arr = [];

        foreach ($stats as $i => $player) {
            $arr[$player['id']]['xwoba_rank'] = $i;
        }

        $stats = Hitter::where([
            'year' => $year,
            ['pa', '>=', $pa_min],
            ['pa_per_g', '>=', $pa_per_g_min],
            ['sb', '>=', $sb_min],
        ])->with('player')->orderBy('brls_per_pa', 'desc')->get();

        foreach ($stats as $i => $player) {
            $stats[$i]['pa_per_g'] = ltrim(number_format($stats[$i]['pa_per_g'], 1));
            $stats[$i]['avg'] = ltrim(number_format($stats[$i]['avg'], 3),"0");
            $stats[$i]['brls_rank'] = $i+1;
            $stats[$i]['xwoba_rank'] = $arr[$player['id']]['xwoba_rank']+1;
            $stats[$i]['avg_rank'] = ($stats[$i]['brls_rank'] + $stats[$i]['xwoba_rank']) / 2;
        }

        $stats = $stats->toArray();

        usort($stats, function($a, $b) {
            if ($a['avg_rank'] == $b['avg_rank']) return 0;
            return ($a['avg_rank'] < $b['avg_rank']) ? -1 : 1;
        });

        echo json_encode($stats);
    }
}
