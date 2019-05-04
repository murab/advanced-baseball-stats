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
$my_pitchers = [];

$a = new CustomStats();

$fg = new FangraphsScraper();
$bs = new BaseballSavantScraper();

$a->setFangraphsScraper($fg);
$a->setBaseballSavantScraper($bs);
$a->data = $a->mergeSourceData($a->fgData, $a->bsData);

$KpercentMinusXwoba = $a->computeKpercentMinusXwoba($a->filterData($a->data, 10, null));

foreach ($KpercentMinusXwoba as $key => $player) {
    $rank = $player['rank'] = $key + 1;
    $KpercentMinusXwoba[$key]['rank'] = $rank;
    $val = $player['val_formatted'] = ((string) (number_format($player['value'] * 100, 1)) . '%');
    echo "{$rank} | {$player['name']} | {$val}\n";

    if (in_array($player['name'], MY_PITCHERS)) {
        $my_pitchers[] = $player;
    }
}

echo "\n";
foreach ($my_pitchers as $pitcher) {
    echo "{$pitcher['rank']} | {$pitcher['name']} | {$pitcher['val_formatted']}\n";
}
