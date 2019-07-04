<?php

class Formatter
{
    public static function pitcher($data)
    {
        $player = [];

        $player['rank_formatted'] = str_pad($data['rank_k_minus_adj_xwoba'], 3);
        $player['rank_last_30_formatted'] = str_pad($data['rank_k_minus_adj_xwoba_last_30'] ?? '', 3);
        $player['name_formatted'] = str_pad(substr($data['name'], 0, 16), 16);
        $player['swstr_percentage_formatted'] = str_pad(number_format($data['swstr_percentage'], 1), 4, ' ', STR_PAD_LEFT);
        //$player['k_percentage_formatted'] = str_pad(number_format($data['k_percentage'], 1), 4, ' ', STR_PAD_LEFT);
        $player['k_percentage_plus_formatted'] = str_pad($data['k_percentage_plus'], 3, ' ', STR_PAD_LEFT);
        $player['velo_formatted'] = str_pad(round($data['velo'], 0, PHP_ROUND_HALF_UP), 2);
        //$player['ip_formatted'] = str_pad($data['ip'], 5);
        //$player['kpg_formatted'] = str_pad(number_format($data['k'] / $data['g'], 1), 4);

        //$KpercentMinusXwoba[$key]['rank'] = $rank;

        //$val = $player['val_formatted'] = ((string) (number_format($data['value'] * 100, 1)) . '%');

        return $player;
    }

    public static function pitcherOutput($player)
    {
        //100 150 | Jordan Yamamoto  | 120+ | 13.3% | 91
        $output = "{$player['rank_formatted']} " .
                  "{$player['rank_last_30_formatted']} | " .
                  "{$player['name_formatted']} | " .
                  "{$player['k_percentage_plus_formatted']}+ | " .
                  "{$player['swstr_percentage_formatted']}% | " .
                  "{$player['velo_formatted']}\n";
        return $output;
    }
}