<?php

require_once __DIR__ . '/../vendor/autoload.php';

use duzun\hQuery;

class FangraphsScraper
{
    const pitchersKpercentageURL = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=sta&lg=all&qual=10&type=1&season=2019&month=0&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&sort=7,d&page=1_250';

    private $data;

    public function __construct()
    {
        $doc = hQuery::fromUrl(self::pitchersKpercentageURL, [
            'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36',
        ]);

        $players = $doc->find('.rgMasterTable tbody tr');

        foreach ($players as $player) {

            $vals = $player->find('td');

            $i = 0;
            $player_data = [];
            foreach ($vals as $val) {

                if ($i == 1) {
                    // Name
                    $player_data['name'] = hQuery::fromHTML($val->innerHTML)->find('a')->innerHTML;
                } elseif ($i == 7) {
                    // K%
                    $player_data['k_percentage'] = floatval($val->innerHTML);
                } elseif ($i == 8) {
                    // BB%
                    $player_data['bb_percentage'] = floatval($val->innerHTML);
                }

                $i++;
            }
            $this->data[$player_data['name']] = [
                'name' => $player_data['name'],
                'k_percentage' => $player_data['k_percentage'],
                'bb_percentage' => $player_data['bb_percentage']
            ];
        }
    }

    public function getData()
    {
        return $this->data;
    }
}
