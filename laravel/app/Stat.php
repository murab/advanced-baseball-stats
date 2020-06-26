<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Stat extends Model
{
    protected $guarded = [];

    public function player()
    {
        return $this->belongsTo('App\Player');
    }

    public static function total($year)
    {
        $players = Stat::where('year', $year)->get();
        $data = [];
        foreach ($players as $player) {
            $data[$player->player['name']] = [
                'name' => $player->player['name'],
                'velo' => $player['velo'],
                'k_percentage' => $player['k_percentage'],
                'bb_percentage' => $player['bb_percentage'],
                'swstr_percentage' => $player['swstr_percentage'],
                'kbb_percentage' => $player['kbb_percentage'],
                'k_percentage_plus' => $player['k_percentage_plus'],
                'g' => $player['g'],
                'gs' => $player['gs'],
                'k' => $player['k'],
                'k_per_game' => $player['k_per_game'],
                'ip' => $player['ip'],
                'xwoba' => $player['xwoba'],
                'opprpa' => $player['opprpa'],
                'oppops' => $player['oppops'],
                'pa' => $player['pa'],
            ];
        }

        return $data;
    }

    public static function secondHalf($year)
    {
        $players = Stat::where('year', $year)->get();
        $data = [];
        foreach ($players as $player) {
            $data[$player->player['name']] = [
                'name' => $player->player['name'],
                'velo' => $player['secondhalf_velo'],
                'k_percentage' => $player['secondhalf_k_percentage'],
                'bb_percentage' => $player['secondhalf_bb_percentage'],
                'kbb_percentage' => $player['secondhalf_kbb_percentage'],
                'swstr_percentage' => $player['secondhalf_swstr_percentage'],
                'k_percentage_plus' => $player['secondhalf_k_percentage_plus'],
                'g' => $player['secondhalf_g'],
                'gs' => $player['secondhalf_gs'],
                'k' => $player['secondhalf_k'],
                'k_per_game' => $player['secondhalf_k_per_game'],
                'ip' => $player['secondhalf_ip'],
                'xwoba' => $player['secondhalf_xwoba'],
                'opprpa' => $player['opprpa'],
                'oppops' => $player['oppops'],
                'pa' => $player['pa'],
            ];
        }

        return $data;
    }

    public static function startingPitcherStats(?int $year = null, ?bool $secondHalf = false, ?int $min_ip = 15, ?float $min_ip_per_g = 4.0)
    {
        if (empty($year)) {
            if (date('m-d') > '03-25') {
                $year = date('Y');
            } else {
                $year = date('Y')-1;
            }
        }

        if ($secondHalf) {
            $stats = Stat::secondHalf($year);
        } else {
            $stats = Stat::total($year);
        }
        $data = [];
        foreach ($stats as $stat) {
            if ($stat['ip'] && $stat['g'] && $stat['ip'] > $min_ip && ($stat['ip'] / $stat['g'] > $min_ip_per_g)) {
                $data[] = $stat;
            }
        }

        return $data;
    }

    public static function reliefPitcherStats(?int $year = null, ?bool $secondHalf = false, ?int $min_ip = 10, ?float $min_ip_per_g = 3.0)
    {
        if (empty($year)) {
            if (date('m-d') > '03-25') {
                $year = date('Y');
            } else {
                $year = date('Y')-1;
            }
        }

        if ($secondHalf) {
            $stats = Stat::secondHalf($year);
        } else {
            $stats = Stat::total($year);
        }

        $data = [];
        foreach ($stats as $stat) {
            if ($stat['ip'] && $stat['g'] && $stat['ip'] > $min_ip && $stat['ip'] / $stat['g'] < $min_ip_per_g) {
                $data[] = $stat;
            }
        }
        return $data;
    }

    public function filterPitcherData($orig_data = [], $min_ip = 15, $min_ip_per_g = 4.0, $limit = null)
    {
        $data = [];
        if ($min_ip && $min_ip_per_g) {
            foreach ($orig_data as $key => $player) {
                if ($player['ip'] >= $min_ip && ($player['ip'] / $player['g']) > $min_ip_per_g) {
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

        $league['k_per_game'] = $total_k / $total_g;

        return $league;
    }
}
