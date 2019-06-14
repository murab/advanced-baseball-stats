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
$a->data = $a->mergeSourceData($a->fgPitcherData, $a->bsData, $a->prospectusData);

$KpercentMinusXwoba = $a->computeKpercentMinusAdjustedXwoba($a->filterData($a->data, 10, null));

echo "\nAll Pitchers\n";
foreach ($KpercentMinusXwoba as $key => $player) {

    $rank = $player['rank'] = $key + 1;

    $player['rank_formatted'] = str_pad($rank, 3);
    $player['name_formatted'] = str_pad($player['name'], 18);
    $player['kbb_percentage_formatted'] = str_pad(number_format($player['kbb_percentage'], 1), 4, ' ', STR_PAD_LEFT);
    $player['velo_formatted'] = str_pad(number_format($player['velo'], 1), 4);
    //$player['ip_formatted'] = str_pad($player['ip'], 5);
    //$player['kpg_formatted'] = str_pad(number_format($player['k'] / $player['g'], 1), 4);

    $KpercentMinusXwoba[$key]['rank'] = $rank;
    $val = $player['val_formatted'] = ((string) (number_format($player['value'] * 100, 1)) . '%');

    echo "{$player['rank_formatted']} | {$player['name_formatted']} | {$player['kbb_percentage_formatted']}% | {$player['velo_formatted']} mph\n";

    foreach ($custom_lists as $list) {
        if (in_array($player['name'], $players_of_interest[$list])) {
            $custom_players[$list][] = $player;
        }
    }
}

foreach ($custom_lists as $list) {
    echo "\n{$list}\n";
    foreach ($custom_players[$list] as $pitcher) {
        echo "{$pitcher['rank_formatted']} | {$pitcher['name_formatted']} | {$pitcher['kbb_percentage_formatted']}% | {$pitcher['velo_formatted']} mph\n";
    }
}

echo "\n\n";
