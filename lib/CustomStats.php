<?php

require_once __DIR__ . '/../vendor/autoload.php';

require_once __DIR__ . '/FangraphsScraper.php';
require_once __DIR__ . '/BaseballSavantScraper.php';
require_once __DIR__ . '/BaseballProspectusScraper.php';

class CustomStats
{
    const BEST_K_PER_GAME = 9.45;
    const WORST_K_PER_GAME = 3.75;
    const BEST_XWOBA = 0.249;
    const WORST_XWOBA = 0.350;

    public $fgScraper;
    public $bsScraper;
    public $prospectusScraper;
    public $fgPitcherData;
    public $fgPitcherDataLast30Days;
    public $fgLeagueBatterData;
    public $fgLeaguePitcherData;
    public $bsData;
    public $bsDataLast30Days;
    public $prospectusData;

    public $data;

    public function __construct()
    {

    }

    public function setFangraphsScraper(FangraphsScraper $fgScraper)
    {
        $this->fgScraper = $fgScraper;
        $this->fgPitcherData = $this->fgScraper->getPitcherData();
        $this->fgPitcherDataLast30Days = $this->fgScraper->getPitcherDataLast30Days();
        $this->fgLeagueBatterData = $this->fgScraper->getLeagueBatterData();
        $this->fgLeaguePitcherData = $this->fgScraper->getLeaguePitcherData();
    }

    public function setBaseballSavantScraper(BaseballSavantScraper $bsScraper)
    {
        $this->bsScraper = $bsScraper;
        $this->bsData = $this->bsScraper->getpitchersXwobaData();
        $this->bsDataLast30Days = $this->bsScraper->getPitchersXwobaDataLast30Days();
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
            if (array_key_exists($name, $bsData) && array_key_exists($name, $fgData)) {
                $data[$name] = array_merge_recursive($bsData[$name], $fgData[$name]);
                $data[$name]['name'] = $bsData[$name]['name'];
            }
            if (array_key_exists($name, $prospectusData)) {
                $data[$name] = array_merge_recursive($data[$name], $prospectusData[$name]);
                $data[$name]['name'] = $bsData[$name]['name'];
            }
        }
        return $data;
    }

    public function filterPitcherData($orig_data = [], $min_ip = 15, $min_ip_per_g = 4.0, $limit = null)
    {
        $data = [];
        if ($min_ip && $min_ip_per_g) {
            foreach ($orig_data as $key => $player) {
                if ($player['ip'] >= $min_ip && ($player['ip'] / $player['g']) > $min_ip_per_g) {
                    $data['sp'][] = $player;
                } else if ($player['ip'] >= $min_ip)  {
                    $data['rp'][] = $player;
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
            $output[] = array_merge($this->generatePlayerOutput($data), ['value' => $data['k_percentage'] / 100 - $data['xwoba']]);
        }
        usort($output, function($a, $b) {
            return ($a['value'] > $b['value']) ? -1 : 1;
        });
        $rank = 1;
        foreach ($output as $key => $player) {
            $output[$key]['rank_k_minus_xwoba'] = $rank;
            $rank++;
        }
        return $output;
    }

    public function computeKpercentMinusAdjustedXwoba($all_data, $league_ops, $last_30_data = null, $enable_opp_quality_adjustment = true)
    {
        $output = [];
        foreach ($all_data as $name => $data) {

            if ($enable_opp_quality_adjustment == true && !empty($data['oppops'])) {
                $opponent_quality_muliplier = $league_ops / $data['oppops'];

                // calculate adjusted xwoba
                $data['xwoba'] = $opponent_quality_muliplier * $data['xwoba'];
            }

            $output[] = array_merge($this->generatePlayerOutput($data), ['value' => number_format($data['k_percentage'] / 100 - $data['xwoba'], 3)]);
        }
        usort($output, function($a, $b) {
            return ($a['value'] > $b['value']) ? -1 : 1;
        });
        $rank = 1;
        foreach ($output as $key => $player) {
            $output[$key]['rank_k_minus_adj_xwoba'] = $rank;
            $rank++;
        }

        if ($last_30_data) {
            foreach ($output as $key => $player) {
                foreach ($last_30_data as $player30) {
                    if ($player['name'] == $player30['name']) {
                        $output[$key]['rank_k_minus_adj_xwoba_last_30'] = $player30['rank_k_minus_adj_xwoba'] ?? '';
                    }
                }
            }
        }

        return $output;
    }

    public function computeKperGameMinusAdjustedXwoba($all_data, $league_ops, $last_30_data = null, $enable_opp_quality_adjustment = true)
    {
        $output = [];

        foreach ($all_data as $name => $data) {

            if ($enable_opp_quality_adjustment == true && !empty($data['oppops'])) {
                $opponent_quality_multiplier = $league_ops / $data['oppops'];

                // calculate adjusted xwoba
                $data['xwoba'] = $opponent_quality_multiplier * $data['xwoba'];
            }

            $k_per_game = $data['k'] / $data['g'];

            $score = (
                (($k_per_game - self::WORST_K_PER_GAME) / (self::BEST_K_PER_GAME - self::WORST_K_PER_GAME))
                +
                (($data['xwoba'] - self::WORST_XWOBA) / (self::BEST_XWOBA - self::WORST_XWOBA))
            );

            $output[] = array_merge($this->generatePlayerOutput($data), ['value' => number_format($score, 3)]);
        }
        usort($output, function($a, $b) {
            return ($a['value'] > $b['value']) ? -1 : 1;
        });
        $rank = 1;
        foreach ($output as $key => $player) {
            $output[$key]['rank_k_minus_adj_xwoba'] = $rank;
            $rank++;
        }

        if ($last_30_data) {
            foreach ($output as $key => $player) {
                foreach ($last_30_data as $player30) {
                    if ($player['name'] == $player30['name']) {
                        $output[$key]['rank_k_minus_adj_xwoba_last_30'] = $player30['rank_k_minus_adj_xwoba'] ?? '';
                    }
                }
            }
        }

        return $output;
    }

    private function generatePlayerOutput ($data)
    {
        $output = [
            'name' => $data['name'],
            'pa' => $data['pa'],
            'ip' => $data['ip'],
            'g' => $data['g'],
            'k' => $data['k'],
            'k_percentage' => $data['k_percentage'],
            'k_percentage_plus' => $data['k_percentage_plus'],
            'kbb_percentage' => $data['kbb_percentage'],
            'swstr_percentage' => $data['swstr_percentage'],
            'gs' => $data['gs'],
            'velo' => $data['velo'],
            'opprpa' => !empty($data['opprpa']) ? $data['opprpa'] : null,
            'oppops' => !empty($data['oppops']) ? $data['oppops'] : null
        ];

        return $output;
    }
}
