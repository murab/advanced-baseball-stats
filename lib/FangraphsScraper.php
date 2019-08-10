<?php

require_once __DIR__ . '/../vendor/autoload.php';

use duzun\hQuery;

class FangraphsScraper
{
    const pitcherDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=pit&lg=all&qual=0&type=c,36,37,38,40,120,121,217,113,42,43,117,118,6,45,62,122,3,7,8,24,13,310,76&season=2019&month=0&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=&enddate=&page=1_1500';
    const pitcherDataSourceLast30Days = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=pit&lg=all&qual=0&type=c,36,37,38,40,120,121,217,113,42,43,117,118,6,45,62,122,3,7,8,24,13,310,76&season=2019&month=3&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=&enddate=&page=1_1500';
    const leagueBattersDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=bat&lg=all&qual=0&type=c,6,39&season=2019&month=0&season1=2019&ind=0&team=0,ss&rost=0&age=0&filter=&players=0&startdate=2019-01-01&enddate=2019-12-31';
    const leaguePitchersDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=sta&lg=all&qual=0&type=c,76,113,217,6,42&season=2019&month=0&season1=2019&ind=0&team=0,ss&rost=0&age=0&filter=&players=0&startdate=2019-01-01&enddate=2019-12-31';

    private $league_batter_data;
    private $league_pitcher_data;

    public function __construct()
    {

    }

    public function parsePitcherData($stats)
    {
        $i = 0;
        $player_data = [];
        foreach ($stats as $stat) {

            if ($i%26 == 1) {
                $player_data = [];
                // Name
                $player_data['name'] = hQuery::fromHTML($stat->innerHTML)->find('a')->innerHTML;
                $player_data['name'] = preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']);
            } elseif ($i%26 == 7) {
                // K%
                $player_data['k_percentage'] = floatval($stat->innerHTML);
            } elseif ($i%26 == 8) {
                // BB%
                $player_data['bb_percentage'] = floatval($stat->innerHTML);
            } elseif ($i%26 == 9) {
                // K-BB%
                $player_data['kbb_percentage'] = floatval($stat->innerHTML);
            } elseif ($i%26 == 10) {
                // SwStr%
                $player_data['swstr_percentage'] = floatval($stat->innerHTML);
            } elseif ($i%26 == 19) {
                // Age
                $player_data['age'] = (int) $stat->innerHTML;
            } elseif ($i%26 == 20) {
                // Games
                $player_data['g'] = (int) $stat->innerHTML;
            } elseif ($i%26 == 21) {
                // Games
                $player_data['gs'] = (int) $stat->innerHTML;
            } elseif ($i%26 == 22) {
                // Games
                $player_data['k'] = (int) $stat->innerHTML;
            } elseif ($i%26 == 23) {
                $player_data['ip'] = $stat->innerHTML;
            } elseif ($i%26 == 24) {
                $player_data['k_percentage_plus'] = $stat->innerHTML;
            } elseif ($i%26 == 25) {
                $player_data['velo'] = (float) $stat->innerHTML;
                $data[strtolower($player_data['name'])] = [
                    'name' => $player_data['name'],
                    'k_percentage' => $player_data['k_percentage'],
                    'bb_percentage' => $player_data['bb_percentage'],
                    'kbb_percentage' => $player_data['kbb_percentage'],
                    'swstr_percentage' => $player_data['swstr_percentage'],
                    'k_percentage_plus' => $player_data['k_percentage_plus'],
                    'age' => $player_data['age'],
                    'g' => $player_data['g'],
                    'k' => $player_data['k'],
                    'k_per_game' => $player_data['k'] / $player_data['g'],
                    'gs' => $player_data['gs'],
                    'ip' => $player_data['ip'],
                    'velo' => $player_data['velo']
                ];
            }
            $i++;
        }

        return $data;
    }

    public function getPitcherData()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl(self::pitcherDataSource, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            //'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $stats = $doc->find('.grid_line_regular');

        return $this->parsePitcherData($stats);
    }

    public function getPitcherDataLast30Days()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl(self::pitcherDataSourceLast30Days, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            //'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $stats = $doc->find('.grid_line_regular');

        return $this->parsePitcherData($stats);
    }

    public function getLeaguePitcherData()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl(self::leaguePitchersDataSource, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            //'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        try {
            $stats = $doc->find('.grid_line_regular');
        } catch (Throwable $t) {
            echo $t->getMessage();
            exit;
        }

        $i = 0;
        $data = [];

        if (empty($stats)) {
            echo "\nError: Could not fetch league pitcher data from FanGraphs.\n\n";
            exit;
        }

        foreach ($stats as $stat) {
            if ($i == 2) {
                $data['fbv'] = floatval($stat->innerHTML);
                $this->league_pitcher_data['fbv'] = number_format($data['fbv'], 1);
            } else if ($i == 3) {
                $data['swstr_percentage'] = floatval($stat->innerHTML);
                $this->league_pitcher_data['swstr_percentage'] = number_format($data['swstr_percentage'], 1);
            } else if ($i == 4) {
                $data['kbb_percentage'] = floatval($stat->innerHTML);
                $this->league_pitcher_data['kbb_percentage'] = number_format($data['kbb_percentage'], 1);
            } else if ($i == 5) {
                $data['era'] = floatval($stat->innerHTML);
                $this->league_pitcher_data['era'] = number_format($data['era'], 2);
            } else if ($i == 6) {
                $data['whip'] = floatval($stat->innerHTML);
                $this->league_pitcher_data['whip'] = number_format($data['whip'], 2);
            }
            $i++;
        }

        return $this->league_pitcher_data;
    }

    public function getLeagueBatterData()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl(self::leagueBattersDataSource, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            //'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        try {
            $stats = $doc->find('.grid_line_regular');
        } catch (Throwable $t) {
            echo $t->getMessage();
            exit;
        }

        $i = 0;
        $data = [];

        if (empty($stats)) {
            echo "\nError: Could not fetch league batter data from FanGraphs.\n\n";
            exit;
        }

        foreach ($stats as $stat) {
            if ($i == 3) {
                $data['ops'] = floatval($stat->innerHTML);
                $this->league_batter_data['ops'] = $data['ops'];
            }
            $i++;
        }

        return $this->league_batter_data;
    }
}
