<?php

require_once __DIR__ . '/../vendor/autoload.php';

use duzun\hQuery;

class FangraphsScraper
{
    const pitchersKpercentageURL = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=sta&lg=all&qual=10&type=c,36,37,38,40,120,121,217,41,42,43,44,117,118,119,6,45,124,62,122,3,7,13&season=2019&month=0&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&page=1_300';

    private $data;

    public function __construct()
    {
        $doc = hQuery::fromUrl(self::pitchersKpercentageURL, [
            'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36',
        ]);

        $stats = $doc->find('.grid_line_regular');

        $i = 0;
        $player_data = [];
        foreach ($stats as $stat) {

            if ($i%25 == 1) {
                $player_data = [];
                // Name
                $player_data['name'] = hQuery::fromHTML($stat->innerHTML)->find('a')->innerHTML;
                $player_data['name'] = preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']);
            } elseif ($i%25 == 7) {
                // K%
                $player_data['k_percentage'] = floatval($stat->innerHTML);
            } elseif ($i%25 == 8) {
                // BB%
                $player_data['bb_percentage'] = floatval($stat->innerHTML);
            } elseif ($i%25 == 22) {
                // Age
                $player_data['age'] = (int) $stat->innerHTML;
            } elseif ($i%25 == 23) {
                // Games
                $player_data['g'] = (int) $stat->innerHTML;
            } elseif ($i%25 == 24) {
                $player_data['ip'] = $stat->innerHTML;
                $this->data[$player_data['name']] = [
                    'name' => $player_data['name'],
                    'k_percentage' => $player_data['k_percentage'],
                    'bb_percentage' => $player_data['bb_percentage'],
                    'age' => $player_data['age'],
                    'g' => $player_data['g'],
                    'ip' => $player_data['ip']
                ];
            }

            $i++;
        }
    }

    public function getData()
    {
        return $this->data;
    }
}
