<?php

require_once __DIR__ . '/../vendor/autoload.php';

use duzun\hQuery;

class BaseballSavantScraper
{
    const pitchersXwobaURL = 'https://baseballsavant.mlb.com/statcast_search?hfPT=&hfAB=&hfBBT=&hfPR=&hfZ=&stadium=&hfBBL=&hfNewZones=&hfGT=R%7C&hfC=&hfSea=2019%7C&hfSit=&player_type=pitcher&hfOuts=&opponent=&pitcher_throws=&batter_stands=&hfSA=&game_date_gt=&game_date_lt=&hfInfield=&team=&position=SP&hfOutfield=&hfRO=&home_road=&hfFlag=&hfPull=&metric_1=&hfInn=&min_pitches=0&min_results=0&group_by=name&sort_col=xwoba&player_event_sort=h_launch_speed&sort_order=asc&min_pas=50#results';

    private $data;

    public function __construct()
    {
        $doc = hQuery::fromUrl(self::pitchersXwobaURL, [
            'Accept'     => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
            'User-Agent' => 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/59.0.3071.115 Safari/537.36',
        ]);

        $players = $doc->find('#search_results tbody tr');

        foreach ($players as $player) {

            $vals = $player->find('td');

            $i = 0;
            $player_data = [];
            foreach ($vals as $val) {

                if ($i == 1) {
                    // Name
                    $player_data['name'] = preg_replace("/[^A-Za-z0-9\- ]/", '', $val->innerHTML);
                } elseif ($i == 2) {
                    // PAs
                    $player_data['pa'] = explode(' for ', trim($val->innerHTML))[1];
                } elseif ($i == 3) {
                    // xWOBA
                    $player_data['xwoba'] = floatval($val->innerHTML);
                }

                $i++;
            }
            $this->data[$player_data['name']] = [
                'name' => $player_data['name'],
                'xwoba' => $player_data['xwoba'],
                'pa' => $player_data['pa']
            ];
        }
    }

    public function getData()
    {
        return $this->data;
    }
}
