<?php

require_once __DIR__ . '/lib/CustomStats.php';
require_once __DIR__ . '/lib/Formatter.php';

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

$filtered_data_last_30 = $a->filterPitcherData($dataLast30, 7);
$filtered_data = $a->filterPitcherData($data);

$startersLast30 = $a->computeKperGameMinusAdjustedXwoba($filtered_data_last_30['sp'], $a->fgLeagueBatterData['ops'], null, false);
$starters = $a->computeKperGameMinusAdjustedXwoba($filtered_data['sp'], $a->fgLeagueBatterData['ops'], $startersLast30);
$relieversLast30 = $a->computeKpercentMinusAdjustedXwoba($filtered_data_last_30['rp'], $a->fgLeagueBatterData['ops'], null, false);
$relievers = $a->computeKpercentMinusAdjustedXwoba($filtered_data['rp'], $a->fgLeagueBatterData['ops'], $relieversLast30);

ob_start();

echo Formatter::leagueAveragePitcher($a->fgLeaguePitcherData);

foreach ($starters as $key => $player) {

    $player_formatted_data = Formatter::pitcher($player);

    foreach ($custom_lists as $list) {
        if (in_array($player['name'], $players_of_interest[$list])) {
            $custom_players[$list][] = $player_formatted_data;
        }
    }
}

foreach ($relievers as $key => $player) {

    $player_formatted_data = Formatter::pitcher($player);

    foreach ($custom_lists as $list) {
        if (in_array($player['name'], $players_of_interest[$list])) {
            $custom_players[$list][] = $player_formatted_data;
        }
    }
}

foreach ($custom_lists as $list) {
    usort($custom_players[$list], function ($a, $b) {
        return $a['rank_k_minus_adj_xwoba'] <=> $b['rank_k_minus_adj_xwoba'];
    });
    echo "\n{$list}\n";
    foreach ($custom_players[$list] as $player) {
        echo Formatter::pitcherOutput($player);
    }
}

echo "\nAll Starters\n";
foreach ($starters as $key => $player) {

    $player_formatted_data = Formatter::pitcher($player);

    echo Formatter::pitcherOutput($player_formatted_data);

    foreach ($custom_lists as $list) {
        if (in_array($player['name'], $players_of_interest[$list])) {
            $custom_players[$list][] = $player_formatted_data;
        }
    }
}

echo "\nAll Relievers\n";
foreach ($relievers as $key => $player) {

    $player_formatted_data = Formatter::pitcher($player);

    echo Formatter::pitcherOutput($player_formatted_data);

    foreach ($custom_lists as $list) {
        if (in_array($player['name'], $players_of_interest[$list])) {
            $custom_players[$list][] = $player_formatted_data;
        }
    }
}

echo "\n\n";

$output = ob_get_contents();
ob_end_clean();

file_put_contents('./data.txt', $output);
file_put_contents('./archive/pitchers-' . date('Y-m-d') . '.txt', $output);
