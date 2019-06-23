<?php

require_once __DIR__ . '/lib/CustomStats.php';

$players_of_interest = json_decode(file_get_contents('./players_of_interest.json'), true);

$custom_lists = array_keys($players_of_interest);
$custom_players = [];

$a = new CustomStats();

$fg = new FangraphsScraper();
$bs = new BaseballSavantScraper();
$prosp = new BaseballProspectusScraper();

$a->setFangraphsScraper($fg);
$a->setBaseballSavantScraper($bs);
$a->setBaseballProspectusScraper($prosp);
$data = $a->mergeSourceData($a->fgPitcherData, $a->bsData, $a->prospectusData);
$dataLast30 = $a->mergeSourceData($a->fgPitcherDataLast30Days, $a->bsDataLast30Days, $a->prospectusData);

$KpercentMinusXwobaLast30 = $a->computeKpercentMinusAdjustedXwoba($a->filterPitcherData($dataLast30), $a->fgLeagueBatterData['ops'], null, false);
$KpercentMinusXwoba = $a->computeKpercentMinusAdjustedXwoba($a->filterPitcherData($data), $a->fgLeagueBatterData['ops'], $KpercentMinusXwobaLast30);

echo "\nAll Pitchers\n";
foreach ($KpercentMinusXwoba as $key => $player) {

    $rank = $player['rank'] = $key + 1;

    $player['rank_formatted'] = str_pad($rank, 3);
    $player['rank_last_30_formatted'] = str_pad($player['rank_k_minus_adj_xwoba_last_30'] ?? '', 3);
    $player['name_formatted'] = str_pad(substr($player['name'], 0, 18), 18);
    $player['k_percentage_formatted'] = str_pad(number_format($player['k_percentage'], 1), 4, ' ', STR_PAD_LEFT);
    $player['k_percentage_plus_formatted'] = str_pad($player['k_percentage_plus'], 3, ' ', STR_PAD_LEFT);
    $player['velo_formatted'] = str_pad(number_format($player['velo'], 1), 4);
    //$player['ip_formatted'] = str_pad($player['ip'], 5);
    //$player['kpg_formatted'] = str_pad(number_format($player['k'] / $player['g'], 1), 4);

    $KpercentMinusXwoba[$key]['rank'] = $rank;
    $val = $player['val_formatted'] = ((string) (number_format($player['value'] * 100, 1)) . '%');

    echo "{$player['rank_formatted']} | {$player['rank_last_30_formatted']} | {$player['name_formatted']} | {$player['k_percentage_plus_formatted']} K+ | {$player['velo_formatted']}\n";

    foreach ($custom_lists as $list) {
        if (in_array($player['name'], $players_of_interest[$list])) {
            $custom_players[$list][] = $player;
        }
    }
}

foreach ($custom_lists as $list) {
    echo "\n{$list}\n";
    foreach ($custom_players[$list] as $pitcher) {
        echo "{$pitcher['rank_formatted']} | {$pitcher['rank_last_30_formatted']} | {$pitcher['name_formatted']} | {$pitcher['k_percentage_plus_formatted']} K+ | {$pitcher['velo_formatted']}\n";
    }
}

echo "\n\n";
