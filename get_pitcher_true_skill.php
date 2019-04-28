<?php

require_once __DIR__ . '/lib/CustomStats.php';

$a = new CustomStats();

$fg = new FangraphsScraper();
$bs = new BaseballSavantScraper();

$a->setFangraphsScraper($fg);
$a->setBaseballSavantScraper($bs);
$a->data = $a->mergeSourceData($a->fgData, $a->bsData);

$KpercentMinusXwoba = $a->computeKpercentMinusXwoba($a->filterData($a->data, 10, null));

foreach ($KpercentMinusXwoba as $key => $player) {
    $rank = $key + 1;
    $val = ((string) (number_format($player['value'] * 100, 1)) . '%');
    echo "{$rank} | {$player['name']} | {$val}\n";
}
