<?php

require_once __DIR__ . '/lib/CustomStats.php';

const MY_PITCHERS = [
    'Jacob deGrom',
    'Zack Wheeler',
    'Hyun-Jin Ryu',
    'Tyler Skaggs',
    'Griffin Canning',
    'Jeff Samardzija',
    'Drew Pomeranz',
    'Pablo Lopez',
    'Anibal Sanchez',
    'Julio Urias',
    'Derek Holland',
    'Drew Smyly',
    'CC Sabathia',
    'Nick Pivetta'
];

const BEST_AVAILABLE = [
    'Wade Miley',
    'Jon Duplantier',
    'John Means',
    'Chris Bassitt',
    'Mike Fiers',
    'Trent Thornton',
    'Danny Duffy',
    'Merrill Kelly'
];

$my_pitchers = [];
$best_available = [];

$a = new CustomStats();

$fg = new FangraphsScraper();
$bs = new BaseballSavantScraper();

$a->setFangraphsScraper($fg);
$a->setBaseballSavantScraper($bs);
$a->data = $a->mergeSourceData($a->fgData, $a->bsData);

$KpercentMinusXwoba = $a->computeKpercentMinusXwoba($a->filterData($a->data, 10, null));

ob_start();

echo "\nAll Pitchers\n";
foreach ($KpercentMinusXwoba as $key => $player) {
    $rank = $player['rank'] = $key + 1;
    $KpercentMinusXwoba[$key]['rank'] = $rank;
    $val = $player['val_formatted'] = ((string) (number_format($player['value'] * 100, 1)) . '%');
    echo "{$rank} | {$player['name']} | {$player['ip']} IP | {$val}\n";

    if (in_array($player['name'], MY_PITCHERS)) {
        $my_pitchers[] = $player;
    }

    if (in_array($player['name'], BEST_AVAILABLE)) {
        $best_available[] = $player;
    }
}

echo "\nBest Available\n";
foreach ($best_available as $pitcher) {
    echo "{$pitcher['rank']} | {$pitcher['name']} | {$pitcher['ip']} IP | {$pitcher['val_formatted']}\n";
}

echo "\nMy Pitchers\n";
foreach ($my_pitchers as $pitcher) {
    echo "{$pitcher['rank']} | {$pitcher['name']} | {$pitcher['ip']} IP | {$pitcher['val_formatted']}\n";
}

$output = ob_get_contents();
ob_end_clean();

file_put_contents('./data.txt', $output);
