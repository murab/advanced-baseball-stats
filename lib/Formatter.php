<?php

class Formatter
{
    /**
     * @param array $data
     * @return string
     */
    public static function leagueAveragePitcher(array $data)
    {
        $data['era'] = number_format($data['era'], 2);
        $data['whip'] = number_format($data['whip'], 2);
        $data['kbb_percentage'] = number_format($data['kbb_percentage'], 1);
        $data['swstr_percentage'] = number_format($data['swstr_percentage'], 1);
        $data['gb_percentage'] = number_format($data['gb_percentage'], 1);
        $data['velo'] = number_format($data['velo'], 1);

        $output = "\nLeague Averages";
        $output .= "\nERA: {$data['era']}";
        $output .= "\nWHIP: {$data['whip']}";
        $output .= "\n\nK-BB%: {$data['kbb_percentage']}%";
        $output .= "\nSwStr%: {$data['swstr_percentage']}%";
        $output .= "\nGB%: {$data['gb_percentage']}%";
        $output .= "\n\nFBv: {$data['velo']}";
        $output .= "\nK per game: " . number_format($data['k_per_game'], 2) . "\n";
        return $output;
    }

    /**
     * @param $data
     * @return array
     */
    public static function pitcher($data)
    {
        $player = [];

        $player['rank_k_minus_adj_xwoba'] = $data['tru'];
        $player['rank_formatted'] = str_pad($data['tru_rank'], 3);
        $player['ip_formatted'] = str_pad((int)$data['ip'], 3);
        $player['rank_last_30_formatted'] = str_pad($data['secondhalf_tru_rank'] ?? '', 3);
        $player['name_formatted'] = str_pad(substr($data['player']['name'], 0, 20), 20);
        $player['swstr_percentage_formatted'] = str_pad(number_format($data['swstr_percentage'], 1), 4, ' ', STR_PAD_LEFT);
        //$player['k_percentage_formatted'] = str_pad(number_format($data['k_percentage'], 1), 4, ' ', STR_PAD_LEFT);
        $player['gb_percentage_formatted'] = str_pad(round($data['gb_percentage'], 0, PHP_ROUND_HALF_UP), 2);
        $player['k_percentage_plus_formatted'] = str_pad($data['k_percentage_plus'], 3, ' ', STR_PAD_LEFT);
        $player['velo_formatted'] = str_pad($data['velo'], 5);
        //$player['ip_formatted'] = str_pad($data['ip'], 5);
        //$player['kpg_formatted'] = str_pad(number_format($data['k'] / $data['g'], 1), 4);

        //$KpercentMinusXwoba[$key]['rank'] = $rank;

        //$val = $player['val_formatted'] = ((string) (number_format($data['value'] * 100, 1)) . '%');
        return $player;
    }

    /**
     * @param $player
     * @return string
     */
    public static function pitcherOutput($player)
    {
        //100 150 | Jordan Yamamoto  | 120+ | 13.3% | 91
        //100 150|241 ip|Jordan Yamamoto |120+|13.3%|91
        $output = "{$player['rank_formatted']} " .
            "{$player['rank_last_30_formatted']}|" .
            "{$player['ip_formatted']}|" .
            "{$player['name_formatted']}|" .
            //"{$player['k_percentage_plus_formatted']}+|" .
            "{$player['swstr_percentage_formatted']}%|" .
            "{$player['gb_percentage_formatted']}%|" .
            "{$player['velo_formatted']}\n";
        return $output;
    }
}