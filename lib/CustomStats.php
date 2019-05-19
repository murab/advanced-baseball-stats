<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/FangraphsScraper.php';
require_once __DIR__ . '/BaseballSavantScraper.php';
require_once __DIR__ . '/BaseballProspectusScraper.php';

class CustomStats
{
    public $fgScraper;
    public $bsScraper;
    public $prospectusScraper;
    public $fgData;
    public $bsData;
    public $prospectusData;

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

    public function setBaseballProspectusScraper(BaseballProspectusScraper $prospectusScraper)
    {
        $this->prospectusScraper = $prospectusScraper;
        $this->prospectusData = $this->prospectusScraper->getData();
    }

    public function mergeSourceData($fgData, $bsData, $prospectusData) : array
    {
        $data = [];
        foreach ($fgData as $name => $player) {
            if (array_key_exists($name, $bsData) && array_key_exists($name, $fgData) && array_key_exists($name, $prospectusData)) {
                $data[$name] = array_merge_recursive($bsData[$name], $fgData[$name]);
                $data[$name] = array_merge_recursive($data[$name], $prospectusData[$name]);
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
            // Minimum 9 ip and 2 innings per start
            if ($data['ip'] >= 15 && $data['ip'] / $data['g'] > 3) {
                $output[] = [
                    'name' => $data['name'],
                    'pa' => $data['pa'],
                    'ip' => $data['ip'],
                    'g' => $data['g'],
                    'k' => $data['k'],
                    'gs' => $data['gs'],
                    'opprpa' => $data['opprpa'],
                    'value' => $data['k_percentage'] / 100 - $data['xwoba']
                ];
            }
        }
        usort($output, function($a, $b) {
            return ($a['value'] > $b['value']) ? -1 : 1;
        });
        return $output;
    }

    public function computeKpercentMinusAdjustedXwoba($all_data)
    {
        $output = [];
        foreach ($all_data as $name => $data) {
            // Minimum 9 ip and 2 innings per start
            if ($data['ip'] >= 15 && $data['ip'] / $data['g'] > 3) {
//                if ($data['opprpa'] >= 100) {
//                    $data['xwoba'] = (100 - ($data['opprpa'] + 100)) / 100 * $data['xwoba'];
//                } elseif ($data['opprpa'] < 100) {
//                    $data['xwoba'] = (100 - ($data['opprpa'] + 100)) / 100 * $data['xwoba'];
//                }

                // calculate adjusted xwoba
                $data['xwoba'] = ((100 - $data['opprpa']) / 2 + 100) / 100 * $data['xwoba'];

                $output[] = [
                    'name' => $data['name'],
                    'pa' => $data['pa'],
                    'ip' => $data['ip'],
                    'g' => $data['g'],
                    'k' => $data['k'],
                    'gs' => $data['gs'],
                    'opprpa' => $data['opprpa'],
                    'value' => $data['k_percentage'] / 100 - $data['xwoba']
                ];
            }
        }
        usort($output, function($a, $b) {
            return ($a['value'] > $b['value']) ? -1 : 1;
        });
        return $output;
    }
}
