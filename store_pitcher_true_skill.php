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

$KpercentMinusXwobaLast30 = $a->computeKpercentMinusAdjustedXwoba($a->filterPitcherData($dataLast30), $a->fgLeagueBatterData['ops'], null, false);
$KpercentMinusXwoba = $a->computeKpercentMinusAdjustedXwoba($a->filterPitcherData($data), $a->fgLeagueBatterData['ops'], $KpercentMinusXwobaLast30);

ob_start();

echo "\nLeague Average FBv: {$a->fgLeaguePitcherData['fbv']}";
echo "\nLeague Average SwStr%: {$a->fgLeaguePitcherData['swstr_percentage']}%\n";

echo "\nAll Pitchers\n";
foreach ($KpercentMinusXwoba as $key => $player) {

    $player_formatted_data = Formatter::pitcher($player);

    echo Formatter::pitcherOutput($player_formatted_data);

    foreach ($custom_lists as $list) {
        if (in_array($player['name'], $players_of_interest[$list])) {
            $custom_players[$list][] = $player_formatted_data;
        }
    }
}

foreach ($custom_lists as $list) {
    echo "\n{$list}\n";
    foreach ($custom_players[$list] as $player) {
        echo Formatter::pitcherOutput($player);
    }
}

echo "\n\n";

$output = ob_get_contents();
ob_end_clean();

file_put_contents('./data.txt', $output);
file_put_contents('./archive/pitchers-' . date('Y-m-d') . '.txt', $output);
