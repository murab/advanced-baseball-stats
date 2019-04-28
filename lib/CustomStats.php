<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/FangraphsScraper.php';
require_once __DIR__ . '/BaseballSavantScraper.php';

class CustomStats
{
    public $fgScraper;
    public $bsScraper;
    public $fgData;
    public $bsData;

    public $data;

    public function __construct()
    {

    }

    public function setFangraphsScraper(FangraphsScraper $fgScraper)
    {
        $this->fgScraper = $fgScraper;
        $this->fgData = $this->fgScraper->getData();
    }

    public function setBaseballSavantScraper(BaseballSavantScraper $bsScraper)
    {
        $this->bsScraper = $bsScraper;
        $this->bsData = $this->bsScraper->getData();
    }

    public function mergeSourceData($fgData, $bsData) : array
    {
        $data = [];
        foreach ($fgData as $name => $player) {
            if (array_key_exists($name, $bsData) && array_key_exists($name, $fgData)) {
                $data[$name] = array_merge_recursive($bsData[$name], $fgData[$name]);
                $data[$name]['name'] = $bsData[$name]['name'];
            }
        }
        return $data;
    }

    public function filterData($orig_data = [], $min_pa = null, $limit = null)
    {
        $data = [];
        if ($min_pa || $limit) {
            foreach ($orig_data as $key => $player) {
                if ((int)$player['pa'] > $min_pa) {
                    $data[] = $player;
                }
            }
        }
        if ($limit) {
            $data = array_slice($data, 0, $limit);
        }
        return $data;
    }

    public function computeKpercentMinusXwoba($all_data)
    {
        $output = [];
        foreach ($all_data as $name => $data) {
            $output[] = [
                'name' => $data['name'],
                'pa' => $data['pa'],
                'value' => $data['k_percentage'] / 100 - $data['xwoba']
            ];
        }
        usort($output, function($a, $b) {
            return ($a['value'] > $b['value']) ? -1 : 1;
        });
        return $output;
    }
}
