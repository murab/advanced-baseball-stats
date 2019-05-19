<?php

require_once __DIR__ . '/lib/CustomStats.php';

$players_of_interest = json_decode(file_get_contents('./players_of_interest.json'), true);
$MyPlayersConst = $players_of_interest['my_pitchers'];
$BestAvailableConst = $players_of_interest['fa_pitchers'];

$my_pitchers = [];
$best_available = [];

$a = new CustomStats();

$fg = new FangraphsScraper();
$bs = new BaseballSavantScraper();
$prosp = new BaseballProspectusScraper();

$a->setFangraphsScraper($fg);
$a->setBaseballSavantScraper($bs);
$a->setBaseballProspectusScraper($prosp);
$a->data = $a->mergeSourceData($a->fgData, $a->bsData, $a->prospectusData);

$KpercentMinusXwoba = $a->computeKpercentMinusAdjustedXwoba($a->filterData($a->data, 10, null));

ob_start();

echo "\nAll Pitchers\n";
foreach ($KpercentMinusXwoba as $key => $player) {
    $rank = $player['rank'] = $key + 1;

    $player['rank_formatted'] = str_pad($rank, 3);
    $player['name_formatted'] = str_pad($player['name'], 18);
    $player['ip_formatted'] = str_pad($player['ip'], 5);
    $player['kpg_formatted'] = str_pad(number_format($player['k'] / $player['g'], 1), 4);

    $KpercentMinusXwoba[$key]['rank'] = $rank;
    $val = $player['val_formatted'] = ((string) (number_format($player['value'] * 100, 1)) . '%');

    echo "{$player['rank_formatted']} | {$player['name_formatted']} | {$player['ip_formatted']} IP | {$player['kpg_formatted']} KPG\n";

    if (in_array($player['name'], $MyPlayersConst)) {
        $my_pitchers[] = $player;
    }

    if (in_array($player['name'], $BestAvailableConst)) {
        $best_available[] = $player;
    }
}

echo "\nBest Available\n";
foreach ($best_available as $pitcher) {
    echo "{$pitcher['rank_formatted']} | {$pitcher['name_formatted']} | {$pitcher['ip_formatted']} IP | {$pitcher['kpg_formatted']} KPG\n";
}

echo "\nMy Pitchers\n";
foreach ($my_pitchers as $pitcher) {
    echo "{$pitcher['rank_formatted']} | {$pitcher['name_formatted']} | {$pitcher['ip_formatted']} IP | {$pitcher['kpg_formatted']} KPG\n";
}

echo "\n\n";

$output = ob_get_contents();
ob_end_clean();

file_put_contents('./data.txt', $output);
