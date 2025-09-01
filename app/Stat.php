<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Stat extends Model
{
    protected $guarded = [];

    const MIN_STARTER_IP_PER_GAME = 3;
    const BEST_K_PER_GAME = 9.45;
    const WORST_K_PER_GAME = 3.75;
    const BEST_XWOBA = 0.249;
    const WORST_XWOBA = 0.350;

    public function player()
    {
        return $this->belongsTo('App\Player');
    }

    public static function total($year, $position = 'SP')
    {
        $players = Stat::where([
            'year' => $year,
            'position' => strtoupper($position),
            ['xwoba', '<>', null],
            ['k', '<>', null],
            ['g', '<>', null],
            ['k_per_game', '<>', null],
        ])->get();

        $data = [];
        foreach ($players as $player) {
            $data[$player->player['name']] = [
                'id' => $player['id'],
                'name' => $player->player['name'],
                'age' => $player['age'],
                'velo' => $player['velo'],
                'k_percentage' => $player['k_percentage'],
                'bb_percentage' => $player['bb_percentage'],
                'swstr_percentage' => $player['swstr_percentage'],
                'kbb_percentage' => $player['kbb_percentage'],
                'gb_percentage' => $player['gb_percentage'],
                'k_percentage_plus' => $player['k_percentage_plus'],
                'g' => $player['g'],
                'gs' => $player['gs'],
                'k' => $player['k'],
                'k_per_game' => $player['k_per_game'],
                'whip' => $player['whip'],
                'ip' => $player['ip'],
                'xwoba' => $player['xwoba'],
                'opprpa' => $player['opprpa'],
                'oppops' => $player['oppops'],
                'pa' => $player['pa'],
            ];
        }

        return $data;
    }

    public static function secondHalf($year, $position = 'SP')
    {
        $players = Stat::where([
            'year' => $year,
            'position' => strtoupper($position),
            ['secondhalf_xwoba', '<>', null],
            ['secondhalf_whip', '<>', null],
            ['secondhalf_k', '<>', null],
            ['secondhalf_g', '<>', null],
            ['secondhalf_k_per_game', '<>', null],
        ])->get();

        $data = [];
        foreach ($players as $player) {
            $data[$player->player['name']] = [
                'id' => $player['id'],
                'name' => $player->player['name'],
                'age' => $player['age'],
                'velo' => $player['secondhalf_velo'],
                'k_percentage' => $player['secondhalf_k_percentage'],
                'bb_percentage' => $player['secondhalf_bb_percentage'],
                'kbb_percentage' => $player['secondhalf_kbb_percentage'],
                'swstr_percentage' => $player['secondhalf_swstr_percentage'],
                'gb_percentage' => $player['secondhalf_gb_percentage'],
                'k_percentage_plus' => $player['secondhalf_k_percentage_plus'],
                'g' => $player['secondhalf_g'],
                'gs' => $player['secondhalf_gs'],
                'k' => $player['secondhalf_k'],
                'k_per_game' => $player['secondhalf_k_per_game'],
                'ip' => $player['secondhalf_ip'],
                'xwoba' => $player['secondhalf_xwoba'],
                'whip' => $player['secondhalf_whip'],
                'opprpa' => $player['opprpa'],
                'oppops' => $player['oppops'],
                'pa' => $player['pa'],
            ];
        }

        return $data;
    }

    public static function startingPitcherStats(?int $year = null, ?bool $secondHalf = false, ?int $min_ip = null, ?float $min_ip_per_g = 3.0)
    {
        if (is_null($min_ip)) {
            $min_ip = self::calculateMinInningsPitched($year);
        }
        if (empty($year)) {
            if (date('m-d') > '03-25') {
                $year = date('Y');
            } else {
                $year = date('Y')-1;
            }
        }

        if ($secondHalf) {
            $stats = Stat::secondHalf($year, 'SP');
        } else {
            $stats = Stat::total($year, 'SP');
        }
        $data = [];
        foreach ($stats as $stat) {
            if ($stat['ip'] && $stat['g'] && $stat['ip'] >= $min_ip) {
                $data[$stat['id']] = $stat;
            }
        }

        return $data;
    }

    public static function reliefPitcherStats(?int $year = null, ?bool $secondHalf = false, ?int $min_ip = 5, ?float $min_ip_per_g = 3.0)
    {
        if (is_null($min_ip)) {
            $min_ip = self::calculateMinInningsPitched($year, 'rp');
        }
        if (empty($year)) {
            if (date('m-d') > '03-25') {
                $year = date('Y');
            } else {
                $year = date('Y')-1;
            }
        }

        if ($secondHalf) {
            $stats = Stat::secondHalf($year, 'RP');
        } else {
            $stats = Stat::total($year, 'RP');
        }

        $data = [];
        foreach ($stats as $stat) {
            if ($stat['ip'] && $stat['g'] && $stat['ip'] >= $min_ip) {
                $data[$stat['id']] = $stat;
            }
        }
        return $data;
    }

    public function filterPitcherData($orig_data = [], $min_ip = 15, $min_ip_per_g = 4.0, $limit = null)
    {
        $data = [];
        if ($min_ip && $min_ip_per_g) {
            foreach ($orig_data as $key => $player) {
                if ($player['ip'] >= $min_ip && $player['position'] == 'SP') {
                    $data['sp'][] = $player;
                } else if ($player['ip'] >= $min_ip)  {
                    $data['rp'][] = $player;
                }
            }
        }
        if ($limit) {
            $data = array_slice($data, 0, $limit);
        }
        return $data;
    }

    public static function leagueAverageStats($year)
    {
        if (empty($year)) {
            if (date('m-d') > '03-25') {
                $year = date('Y');
            } else {
                $year = date('Y')-1;
            }
        }

        $stats = Stat::startingPitcherStats($year);
        $league = League::where('year', $year)->first()->toArray();

        $total_k = 0;
        $total_g = 0;
        foreach ($stats as $stat) {
            $total_k += $stat['k'];
            $total_g += $stat['g'];
        }

        $league['k_per_game'] = $total_g ? $total_k / $total_g : 0;

        return $league;
    }

    public static function worstKperGame(int $year, string $position)
    {
        return self::WORST_K_PER_GAME;

        $kpg = Stat::where([
            'year' => $year,
            'position' => $position,
        ])->orderBy('k_per_game', 'asc')->first();

        if (count($kpg)) {
            return $kpg['k_per_game'];
        } else {
            return 0;
        }
    }

    public static function bestKperGame(int $year, string $position)
    {
        return self::BEST_K_PER_GAME;

        $kpg = Stat::where([
            'year' => $year,
            'position' => $position,
        ])->orderBy('k_per_game', 'desc')->first()->get();

        if (count($kpg)) {
            return $kpg['k_per_game'];
        } else {
            return 0;
        }
    }

    public static function bestXwoba(int $year, string $position)
    {
        return self::BEST_XWOBA;
    }

    public static function worstXwoba(int $year, string $position)
    {
        return self::WORST_XWOBA;
    }

    public static function computeKperGameMinusAdjustedXwoba(int $year, string $position = 'SP', $second_half = false)
    {
        $enable_opp_quality_adjustment = ($second_half == false);

        if (strtoupper($position) == 'SP') {
            if (!$second_half) {
                $all_data = Stat::startingPitcherStats($year,$second_half);
            } else {
                $all_data = Stat::startingPitcherStats($year,$second_half,10);
            }
        } else {
            $all_data = Stat::reliefPitcherStats($year,$second_half, 5);
        }

        $league_ops = Stat::leagueAverageStats($year)['ops'];

        foreach ($all_data as $key => $data) {

            $opponent_quality_multiplier = 1;
            if ($enable_opp_quality_adjustment == true && !empty($data['oppops'])) {
                $opponent_quality_multiplier = $league_ops / $data['oppops'];
            }
            // calculate adjusted xwoba
            $all_data[$key]['adjusted_xwoba'] = $opponent_quality_multiplier * $data['xwoba'];
            $all_data[$key]['innings_per_game'] = $data['ip'] / $data['g'];
        }

        $xwoba_sorted = $all_data;
        $whip_sorted = $all_data;
        $k_sorted = $all_data;
        $ipg_sorted = $all_data;

        if ($second_half == false) {
            usort($xwoba_sorted, function($a, $b) {
                if ($a['adjusted_xwoba'] == $b['adjusted_xwoba']) {
                    return $a['ip'] > $b['ip'] ? -1 : 1;
                }
                return $a['adjusted_xwoba'] > $b['adjusted_xwoba'];
            });

            if ($position == 'SP') {
                usort($k_sorted, function($a, $b) {
                    if ($a['k_per_game'] == $b['k_per_game']) {
                        return $a['ip'] > $b['ip'] ? -1 : 1;
                    }
                    return $a['k_per_game'] < $b['k_per_game'];
                });
            } else {
                usort($k_sorted, function($a, $b) {
                    if ($a['k_percentage'] == $b['k_percentage']) {
                        return $a['ip'] > $b['ip'] ? -1 : 1;
                    }
                    return $a['k_percentage'] < $b['k_percentage'];
                });
            }

//            usort($ipg_sorted, function ($a, $b) {
//                if ($a['innings_per_game'] == $b['innings_per_game']) {
//                    return $a['ip'] > $b['ip'] ? -1 : 1;
//                }
//                return $a['innings_per_game'] < $b['innings_per_game'];
//            });
        } else {
            usort($xwoba_sorted, function($a, $b) {
                if ($a['xwoba'] == $b['xwoba']) {
                    return $a['ip'] > $b['ip'] ? -1 : 1;
                }
                return $a['xwoba'] > $b['xwoba'];
            });

            $k_sorted = $all_data;
            if ($position == 'SP') {
                usort($k_sorted, function($a, $b) {
                    if ($a['k_per_game'] == $b['k_per_game']) {
                        return $a['ip'] > $b['ip'] ? -1 : 1;
                    }
                    return $a['k_per_game'] < $b['k_per_game'];
                });
            } else {
                usort($k_sorted, function($a, $b) {
                    if ($a['k_percentage'] == $b['k_percentage']) {
                        return $a['ip'] > $b['ip'] ? -1 : 1;
                    }
                    return $a['k_percentage'] < $b['k_percentage'];
                });
            }

//            usort($ipg_sorted, function ($a, $b) {
//                if ($a['innings_per_game'] == $b['innings_per_game']) {
//                    return $a['ip'] > $b['ip'] ? -1 : 1;
//                }
//                return $a['innings_per_game'] < $b['innings_per_game'];
//            });
        }

        usort($whip_sorted, function($a, $b) {
            if ($a['whip'] == $b['whip']) {
                return $a['ip'] > $b['ip'] ? -1 : 1;
            }
            return $a['whip'] > $b['whip'];
        });

        foreach ($all_data as $key => $data) {

            $k_rank = array_search($data['id'], array_column($k_sorted, 'id'));
            $xwoba_rank = array_search($data['id'], array_column($xwoba_sorted, 'id'));
            $whip_rank = array_search($data['id'], array_column($whip_sorted, 'id'));
//            $ipg_rank = array_search($data['id'], array_column($ipg_sorted, 'id'));

            if (strtoupper($position) == 'SP') {
                $all_data[$key]['tru'] = (
                ($k_rank + ($xwoba_rank + $whip_rank)/ 2) / 2
//                    $k_rank + $xwoba_rank + $ipg_rank
//                    (($data['k_per_game'] - $worst_k_per_game) / ($best_k_per_game - $worst_k_per_game))
//                    +
//                    (($data['xwoba'] - $worst_xwoba) / ($best_xwoba - $worst_xwoba))
                );
                $all_data[$key]['k_rank'] = $k_rank+1;
                $all_data[$key]['xwoba_rank'] = $xwoba_rank+1;
                $all_data[$key]['whip_rank'] = $whip_rank+1;
//                $all_data[$key]['ipg_rank'] = $ipg_rank+1;
            } else if (strtoupper($position) == 'RP') {
                // rank among rp at k percentage + rank among rp at xadjusted xwoba might be better
                $all_data[$key]['tru'] = (
                    //$data['k_percentage'] - $data['xwoba'] * 100
                    $k_rank + $xwoba_rank
                );
                $all_data[$key]['k_rank'] = $k_rank+1;
                $all_data[$key]['xwoba_rank'] = $xwoba_rank+1;
            }
        }

        $tru_sorted = $all_data;
        usort($tru_sorted, function($a, $b) {
            if ($a['tru'] == $b['tru']) {
                return $a['k_percentage'] < $b['k_percentage'];
            }
            return $a['tru'] > $b['tru'];
        });

        $ret = [];
        foreach ($all_data as $key => $player) {
            $ret[$player['id']] = $player;
            $ret[$player['id']]['tru_rank'] = array_search($player['id'], array_column($tru_sorted, 'id')) + 1;
        }

        return $ret;
    }

    public static function calculateMinInningsPitched(int $year, string $position = 'sp')
    {
        $position = strtoupper($position);
        return min(50, floor(current(DB::select("select floor(avg(ip)) from stats where year = ? and ip > 0 and position = '{$position}'", [$year])[0])));
    }

    public static function calculateMinPlateAppearances(int $year)
    {
        return min(150, floor(current(DB::select('select floor(avg(pa)) from hitters where pa > 0 and year = ?', [$year])[0])));
    }

    public static function calculateMinPlateAppearancesSecondHalf(int $year)
    {
        return min(150, floor(current(DB::select('select floor(avg(secondhalf_pa)) from hitters where secondhalf_pa > 0 and year = ?', [$year])[0])));
    }

    public static function computeHitterRanks(int $year)
    {
        DB::statement('
                update hitters set
                   rank_avg_rank = null,
                   rank_avg = null,
                   secondhalf_rank_avg_rank = null,
                   secondhalf_rank_avg = null,
                   pa_per_g = null,
                   pa_per_g_rank = null,
                   sb_per_pa_rank = null,
                   pulled_flyballs_per_g = null,
                   pulled_flyballs_per_g_rank = null,
                   xhr_per_g = null,
                   xhr_per_g_rank = null,
                   hr_per_g = null,
                   hr_per_g_rank = null,
                   avg_rank = null,
                   xba_rank = null,
                   wrcplus_rank = null,
                   k_percentage_rank = null,
                   sprint_speed_rank = null,
                   brls_rank = null,
                   xwoba_rank = null
                where year = ?' ,[$year]);

        $min_pa = self::calculateMinPlateAppearances($year);

        $players = Hitter::where([
            'year' => $year,
            ['pa', '<>', null],
            ['g', '<>', null],
        ])->orderBy('pa', 'desc')->get();

        foreach ($players as &$player) {
            $player->pa_per_g = $player->pa / $player->g ?? 0;
            $player->pulled_flyballs_per_g = $player->pulled_flyballs / $player->g ?? 0;
            $player->xhr_per_g = $player->xhr / $player->g ?? 0;
            $player->hr_per_g = $player->hr / $player->g ?? 0;
            $player->save();
        }

        $players = Hitter::where([
            'year' => $year,
            ['pa', '>=', $min_pa],
            ['xwoba', '<>', null],
            ['pa_per_g', '>=', 3.6],
        ])->orderBy('xwoba', 'desc')->orderBy('pa', 'desc')->get();

        $i = 1;
        foreach ($players as $player) {
            $player->xwoba_rank = $i;
            $i++;
            $player->save();
        }

        $players = Hitter::where([
            'year' => $year,
            ['pa', '>=', $min_pa],
            ['avg', '<>', null],
            ['pa_per_g', '>=', 3.6],
        ])->orderBy('avg', 'desc')->orderBy('pa', 'desc')->get();

        $i = 1;
        foreach ($players as $player) {
            $player->avg_rank = $i;
            $i++;
            $player->save();
        }

        $players = Hitter::where([
            'year' => $year,
            ['hr_per_g', '<>', null],
            ['pa', '>=', $min_pa],
            ['pa_per_g', '>=', 3.6],
        ])->orderBy('hr_per_g', 'desc')->orderBy('pa', 'desc')->get();

        $i = 1;
        foreach ($players as $player) {
            $player->hr_per_g_rank = $i;
            $i++;
            $player->save();
        }

        $i = 1;
        $all = [];

        $min_pa = Stat::calculateMinPlateAppearances($year);

        $players = Hitter::where([
            'year' => $year,
            ['pa', '>=', $min_pa],
            ['pa_per_g', '>=', 3.6],
        ])->get();

        foreach ($players as $player) {

            if ($min_pa >= 150) { // use batting average for >= 150 plate appearances
                $player->rank_avg = ($player->hr_per_g_rank + $player->xwoba_rank + $player->avg_rank) / 3.0;
            } else { // use expected batting average for less than 150 plate appearances
                $player->rank_avg = ($player->hr_per_g_rank < $player->xwoba_rank + $player->xba_rank) / 3.0;
            }

            $all[] = [
                'id' => $player->id,
                'avg' => $player->rank_avg,
                'pa' => $player->pa,
            ];
            $i++;

            $player->save();
        }

        usort($all, function($a,$b) {
            if ($a['avg'] == $b['avg']) {
                return $a['pa'] > $b['pa'] ? -1 : 1;
            }
            return $a['avg'] < $b['avg'] ? -1 : 1;
        });

        $i = 1;

        foreach ($all as $player) {
            Hitter::where('id', $player['id'])->update(['rank_avg_rank' => $i]);
            $i++;
        }



        /**
         * SECOND HALF
         */
        $min_pa = Stat::calculateMinPlateAppearancesSecondHalf($year);

        $players = Hitter::where([
            'year' => $year,
            ['secondhalf_pa', '>=', $min_pa],
            ['secondhalf_g', '<>', null],
        ])->orderBy('secondhalf_pa', 'desc')->get();

        foreach ($players as &$player) {
            $player->secondhalf_hr_per_g = $player->secondhalf_hr / $player->secondhalf_g ?? 0;
            $player->secondhalf_pa_per_g = $player->secondhalf_pa / $player->secondhalf_g ?? 0;
            $player->save();
        }

        $players = Hitter::where([
            'year' => $year,
            ['secondhalf_pa', '>=', $min_pa],
            ['secondhalf_g', '<>', null],
            ['secondhalf_xwoba', '<>', null],
            ['secondhalf_pa_per_g', '>=', 3.6],
        ])->orderBy('secondhalf_xwoba', 'desc')->orderBy('secondhalf_pa', 'desc')->get();

        $i = 1;
        foreach ($players as $player) {
            $player->secondhalf_xwoba_rank = $i;
            $i++;
            $player->save();
        }

        $players = Hitter::where([
            'year' => $year,
            ['secondhalf_pa', '>=', $min_pa],
            ['secondhalf_g', '<>', null],
            ['secondhalf_avg', '<>', null],
            ['secondhalf_pa_per_g', '>=', 3.6],
        ])->orderBy('secondhalf_avg', 'desc')->orderBy('secondhalf_pa', 'desc')->get();

        $i = 1;
        foreach ($players as $player) {
            $player->secondhalf_avg_rank = $i;
            $i++;
            $player->save();
        }

        $players = Hitter::where([
            'year' => $year,
            ['secondhalf_pa', '>=', $min_pa],
            ['secondhalf_g', '<>', null],
            ['secondhalf_hr_per_g', '<>', null],
            ['secondhalf_pa_per_g', '>=', 3.6],
        ])->orderBy('secondhalf_hr_per_g', 'desc')->orderBy('secondhalf_pa', 'desc')->get();

        $i = 1;
        foreach ($players as $player) {
            $player->secondhalf_hr_per_g_rank = $i;
            $i++;
            $player->save();
        }

        $i = 1;
        $all = [];

        $players = Hitter::where([
            'year' => $year,
            ['secondhalf_pa', '>=', $min_pa],
            ['secondhalf_pa_per_g', '>=', 3.6],
        ])->get();

        foreach ($players as $player) {

            // NOTE: Fangraphs doesn't have 2nd half expected stats available!
//            if ($min_pa >= 150) { // use batting average for >= 150 plate appearances
                $player->secondhalf_rank_avg = ($player->secondhalf_hr_per_g_rank + $player->secondhalf_xwoba_rank + $player->secondhalf_avg_rank) / 3.0;
//            } else { // use expected batting average for less than 150 plate appearances
//                $player->secondhalf_rank_avg = ($player->secondhalf_hr_per_g_rank < $player->secondhalf_xwoba_rank + $player->secondhalf_xba_rank) / 3.0;
//            }

            $all[] = [
                'id' => $player->id,
                'avg' => $player->secondhalf_rank_avg,
                'pa' => $player->secondhalf_pa,
            ];
            $i++;

            $player->save();
        }

        usort($all, function($a,$b) {
            if ($a['avg'] == $b['avg']) {
                return $a['pa'] > $b['pa'] ? -1 : 1;
            }
            return $a['avg'] < $b['avg'] ? -1 : 1;
        });

        $i = 1;

        foreach ($all as $player) {
            Hitter::where('id', $player['id'])->update(['secondhalf_rank_avg_rank' => $i]);
            $i++;
        }
    }
}
