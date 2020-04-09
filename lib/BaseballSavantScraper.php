<?php

require_once __DIR__ . '/../vendor/autoload.php';

use duzun\hQuery;

class BaseballSavantScraper
{
    const pitchersXwobaURL = 'https://baseballsavant.mlb.com/statcast_search?hfPT=&hfAB=&hfBBT=&hfPR=&hfZ=&stadium=&hfBBL=&hfNewZones=&hfGT=R%7C&hfC=&hfSea=2019%7C&hfSit=&player_type=pitcher&hfOuts=&opponent=&pitcher_throws=&batter_stands=&hfSA=&game_date_gt=&game_date_lt=&hfInfield=&team=&position=&hfOutfield=&hfRO=&home_road=&hfFlag=&hfPull=&metric_1=&hfInn=&min_pitches=0&min_results=0&group_by=name&sort_col=xwoba&player_event_sort=h_launch_speed&sort_order=asc&min_pas=0&chk_stats_pa=on&chk_stats_xwoba=on#results';
    const pitchersXwoba2ndHalfURL = 'https://baseballsavant.mlb.com/statcast_search?hfPT=&hfAB=&hfBBT=&hfPR=&hfZ=&stadium=&hfBBL=&hfNewZones=&hfGT=R%7C&hfC=&hfSea=2019%7C&hfSit=&player_type=pitcher&hfOuts=&opponent=&pitcher_throws=&batter_stands=&hfSA=&game_date_gt=2019-07-09&game_date_lt=&hfInfield=&team=&position=&hfOutfield=&hfRO=&home_road=&hfFlag=&hfPull=&metric_1=&hfInn=&min_pitches=0&min_results=0&group_by=name&sort_col=xwoba&player_event_sort=h_launch_speed&sort_order=asc&min_pas=0&chk_stats_pa=on&chk_stats_xwoba=on#results';

    public function __construct()
    {
    }

    private function parseData($players) {
        $data = [];

        foreach ($players as $player) {

            $vals = $player->find('td');

            $i = 0;
            $player_data = [];
            foreach ($vals as $val) {

                if ($i == 2) {
                    // Name
                    $player_data['name'] = trim(preg_replace("/[^A-Za-z0-9\- ]/", '', $val->innerHTML));
                } elseif ($i == 6) {
                    // PAs
                    $player_data['pa'] = (int)($val->innerHTML);
                } elseif ($i == 7) {
                    // xWOBA
                    $player_data['xwoba'] = floatval($val->innerHTML);
                }

                $i++;
            }
            if (!empty($player_data['pa'])) {
                $data[strtolower($player_data['name'])] = [
                    'name' => $player_data['name'],
                    'xwoba' => $player_data['xwoba'],
                    'pa' => $player_data['pa']
                ];
            }
        }
        return $data;
    }

    public function getpitchersXwobaData()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl(self::pitchersXwobaURL, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            //'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $players = $doc->find('#search_results tbody tr');
        $data = $this->parseData($players);
        return $data;
    }

    public function getPitchersXwobaDataLast30Days()
    {
        // create last 30 days URL
        $url_parts = parse_url(self::pitchersXwobaURL);
        parse_str($url_parts['query'], $params);
        $params['game_date_gt'] = date('Y-m-d', strtotime('30 days ago'));     // Overwrite if exists
        $url_parts['query'] = http_build_query($params);
        $url = $url_parts['scheme'] . '://' . $url_parts['host'] . $url_parts['path'] . '?' . $url_parts['query'];

        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl($url, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            //'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $players = $doc->find('#search_results tbody tr');
        $data = $this->parseData($players);
        return $data;
    }

    public function getPitchersXwobaData2ndHalf()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl(self::pitchersXwoba2ndHalfURL, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            //'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $players = $doc->find('#search_results tbody tr');
        $data = $this->parseData($players);
        return $data;
    }
}
