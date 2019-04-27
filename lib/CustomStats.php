<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/FangraphsScraper.php';
require_once __DIR__ . '/BaseballSavantScraper.php';

class CustomStats
{
    private $fgScraper;
    private $bsScraper;
    private $fgData;
    private $bsData;

    private $data;

    public function __construct()
    {
        $this->fgScraper = new FangraphsScraper();
        $this->fgData = $this->fgScraper->getData();

        $this->bsScraper = new BaseballSavantScraper();
        $this->bsData = $this->bsScraper->getData();

        foreach ($this->fgData as $name => $player) {
            if (array_key_exists($name, $this->bsData) && array_key_exists($name, $this->fgData)){
                $this->data[$name] = array_merge_recursive($this->bsData[$name], $this->fgData[$name]);
            }
        }
    }

    public function getData()
    {
        return $this->data;
    }

    public function computeKpercentMinusXwoba($all_data)
    {
        $output = [];
        foreach ($all_data as $name => $data) {
            $output[] = [
                'name' => $name,
                'value' => $data['k_percentage'] / 100 - $data['xwoba']
            ];
        }
        usort($output, function($a, $b) {
            return ($a['value'] > $b['value']) ? -1 : 1;
        });
        return $output;
    }
}

$a = new CustomStats();
$KpercentMinusXwoba = $a->computeKpercentMinusXwoba($a->getData());
