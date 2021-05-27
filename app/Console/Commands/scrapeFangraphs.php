<?php

namespace App\Console\Commands;

use App\Player;
use App\Stat;
use App\League;
use App\Hitter;
use Illuminate\Console\Command;
use duzun\hQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

class scrapeFangraphs extends Command
{
    const RAWpitcherDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=pit&lg=all&qual=0&type=c,36,37,38,40,120,121,217,113,42,43,117,118,6,45,62,122,3,7,8,24,13,310,76,48,332,331,14,139,140,144&season=2019&month=0&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=&enddate=&page=1_1500';
    const RAWpitcherDataSourceLast30Days = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=pit&lg=all&qual=0&type=c,36,37,38,40,120,121,217,113,42,43,117,118,6,45,62,122,3,7,8,24,13,310,76,48,332,331,14,139,140,144&season=2019&month=3&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=&enddate=&page=1_1500';
    const RAWpitcherDataSource2ndHalf = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=pit&lg=all&qual=0&type=c,36,37,38,40,120,121,217,113,42,43,117,118,6,45,62,122,3,7,8,24,13,310,76,48,332,331,14,139,140,144&season=2019&month=31&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=&enddate=&page=1_1500';
    const RAWpitcherDataSource1stHalf = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=pit&lg=all&qual=0&type=c,36,37,38,40,120,121,217,113,42,43,117,118,6,45,62,122,3,7,8,24,13,310,76,48,332,331,14,139,140,144&season=2019&month=30&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=&enddate=&page=1_1500';
    const RAWleagueBattersDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=bat&lg=all&qual=0&type=c,6,39&season=2019&month=0&season1=2019&ind=0&team=0,ss&rost=0&age=0&filter=&players=0&startdate=2019-01-01&enddate=2019-12-31';
    const RAWleaguePitchersDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=sta&lg=all&qual=0&type=c,76,113,217,6,42,48&season=2019&month=0&season1=2019&ind=0&team=0,ss&rost=0&age=0&filter=&players=0&startdate=2019-01-01&enddate=2019-12-31';

    const RAWhitterDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=np&stats=bat&lg=all&qual=0&type=c,3,4,6,12,23,11,13,21,35,34,110,311,61,308&season=2019&month=0&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=2019-01-01&enddate=2019-12-31&page=1_3000';
    const RAWhitterDataSource2ndHalf = 'https://www.fangraphs.com/leaders.aspx?pos=np&stats=bat&lg=all&qual=0&type=c,3,4,6,12,23,11,13,21,35,34,110,311,61,308&season=2019&month=31&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&page=1_3000';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:fangraphs {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape Fangraphs';

    /** @var Client */
    private $httpClient;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
        $this->httpClient = new Client();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $year = $this->argument('year');
        if (empty($year)) {
            if (date('m-d') > '03-25') {
                $year = date('Y');
            } else {
                $year = date('Y')-1;
            }
        }

        $this->setUrls($year);

        $data = $this->getHitterData();
        $data_2nd = $this->getHitterData2ndHalf();

        foreach ($data as $player) {
            $Player = Player::firstOrCreate([
                'slug' => Str::slug($player['name'])
            ], [
                'name' => $player['name'],
            ]);
            $lowername = strtolower($player['name']);

            $stats = Hitter::firstOrNew([
                'player_id' => $Player->id,
                'year' => $year,
            ]);

            $stats->age = $player['age'];

            foreach ($data[$lowername] as $stat => $val) {
                if (in_array($stat, ['name', 'age'])) { continue; }
                $player['secondhalf_'.$stat] = $data_2nd[$lowername][$stat] ?? null;
            }
            unset($player['name']);

            $stats->fill($player);

            $stats->save();
        }

        $data = $this->getPitcherData();
        $data_2nd = $this->getPitcherData2ndHalf();

        $rp_data = $this->getReliefPitcherData();
        $rp_data_2nd = $this->getReliefPitcherData2ndHalf();

        foreach ($data as $player) {
            $Player = Player::firstOrCreate([
                'slug' => Str::slug($player['name'])
            ], [
                'name' => $player['name'],
            ]);
            $lowername = strtolower($player['name']);

            $stats = Stat::firstOrNew([
                'player_id' => $Player->id,
                'year' => $year,
            ]);

            $stats->age = $player['age'];
            $stats->position = $player['ip'] / $player['g'] > 3.0 ? 'SP' : 'RP';

            if ($stats->position == 'RP' && !empty($rp_data[$lowername])) {
                $player = $rp_data[$lowername];
            }

            if ($stats->position == 'RP' && !empty($rp_data_2nd[$lowername])) {
                $data_2nd[$lowername] = $rp_data_2nd[$lowername];
            }

            //$stats->velo = $player['velo'];
            $stats->k_percentage = $player['k_percentage'];
            $stats->bb_percentage = $player['bb_percentage'];
            $stats->swstr_percentage = $player['swstr_percentage'];
            $stats->gb_percentage = $player['gb_percentage'];
            $stats->k_percentage_plus = $player['k_percentage_plus'];
            $stats->g = $player['g'];
            $stats->gs = $player['gs'];
            $stats->k = $player['k'];
            $stats->k_per_game = $player['k_per_game'];
            $stats->ip = $player['ip'];
            $stats->pa = $player['pa'];
            //$stats->xwoba = $player['xera'];
            $stats->velo = $player['velo'] ?? 0;
            $stats->csw = $player['csw'] ?? 0;

            //$stats->secondhalf_velo = $data_2nd[$lowername]['velo'] ?? null;
            $stats->secondhalf_k_percentage = $data_2nd[$lowername]['k_percentage'] ?? null;
            $stats->secondhalf_bb_percentage = $data_2nd[$lowername]['bb_percentage'] ?? null;
            $stats->secondhalf_swstr_percentage = $data_2nd[$lowername]['swstr_percentage'] ?? null;
            $stats->secondhalf_gb_percentage = $data_2nd[$lowername]['gb_percentage'] ?? null;
            $stats->secondhalf_k_percentage_plus = $data_2nd[$lowername]['k_percentage_plus'] ?? null;
            $stats->secondhalf_g = $data_2nd[$lowername]['g'] ?? null;
            $stats->secondhalf_gs = $data_2nd[$lowername]['gs'] ?? null;
            $stats->secondhalf_k = $data_2nd[$lowername]['k'] ?? null;
            $stats->secondhalf_k_per_game = $data_2nd[$lowername]['k_per_game'] ?? null;
            $stats->secondhalf_ip = $data_2nd[$lowername]['ip'] ?? null;
            $stats->secondhalf_pa = $data_2nd[$lowername]['pa'] ?? null;
            //$stats->secondhalf_xwoba = $data_2nd[$lowername]['xera'] ?? null;
            $stats->secondhalf_velo = isset($velo_2nd[$lowername]) ? $data_2nd[$lowername]['velo'] : 0;
            $stats->secondhalf_csw = isset($data_2nd[$lowername]) ? $data_2nd[$lowername]['csw'] : 0;

            $stats->save();
        }

        $league_hitters = $this->getLeagueBatterData();
        $league_pitchers = $this->getLeaguePitcherData();

        $league = League::firstOrCreate([
            'year' => $year,
        ]);

        $league->velo = $league_pitchers['fbv'];
        $league->swstr_percentage = $league_pitchers['swstr_percentage'];
        $league->kbb_percentage = $league_pitchers['kbb_percentage'];
        $league->gb_percentage = $league_pitchers['gb_percentage'];
        $league->era = $league_pitchers['era'];
        $league->whip = $league_pitchers['whip'];

        $league->ops = $league_hitters['ops'];

        $league->save();
    }

    private function setUrls($year)
    {
        $this->pitcherDataSource = str_replace('2019',$year,self::RAWpitcherDataSource);
        $this->pitcherDataSource2ndHalf = str_replace('2019',$year,self::RAWpitcherDataSource2ndHalf);
        $this->pitcherDataSource1stHalf = str_replace('2019',$year,self::RAWpitcherDataSource1stHalf);
        $this->leagueBattersDataSource = str_replace('2019',$year,self::RAWleagueBattersDataSource);
        $this->leaguePitchersDataSource = str_replace('2019',$year,self::RAWleaguePitchersDataSource);
        $this->hitterDataSource = str_replace('2019',$year,self::RAWhitterDataSource);
        $this->hitterDataSource2ndHalf = str_replace('2019',$year,self::RAWhitterDataSource2ndHalf);
    }

    public function parseHitterData($stats)
    {
        $i = 0;
        $player_data = [];
        $data = [];
        if (is_iterable($stats)) foreach ($stats as $stat)
        {
            $col = $i%17;
            switch ($col) {
                case 1:
                    $player_data = [];
                    // Name
                    $player_data['name'] = hQuery::fromHTML($stat->innerHTML)->find('a')->innerHTML;
                    $player_data['name'] = preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']);
                    break;
                case 3:
                    // Age
                    $player_data['age'] = (int) $stat->innerHTML;
                    break;
                case 4:
                    // G
                    $player_data['g'] = (int) $stat->innerHTML;
                    break;
                case 5:
                    // PA
                    $player_data['pa'] = (int) $stat->innerHTML;
                    break;
                case 6:
                    // R
                    $player_data['r'] = (int) $stat->innerHTML;
                    break;
                case 7:
                    // AVG
                    $player_data['avg'] = floatval($stat->innerHTML);
                    break;
                case 8:
                    // HR
                    $player_data['hr'] = (int) $stat->innerHTML;
                    break;
                case 9:
                    // RBI
                    $player_data['rbi'] = (int) $stat->innerHTML;
                    break;
                case 10:
                    // SB
                    $player_data['sb'] = (int) $stat->innerHTML;
                    break;
                case 11:
                    // K%
                    $player_data['k_percentage'] = floatval($stat->innerHTML);
                    break;
                case 12:
                    // BB%
                    $player_data['bb_percentage'] = floatval($stat->innerHTML);
                    break;
                case 13:
                    // SwStr%
                    $player_data['swstr_percentage'] = floatval($stat->innerHTML);
                    break;
                case 14:
                    // HardHit%
                    $player_data['hardhit_percentage'] = floatval($stat->innerHTML);
                    break;
                case 15:
                    // wRC+
                    $player_data['wrc_plus'] = (int) $stat->innerHTML;
                    break;
                case 16:
                    // Barrels/BBE
                    $player_data['brls_bbe'] = floatval($stat->innerHTML);
                    foreach ($player_data as $stat => $val) {
                        $data[strtolower($player_data['name'])][$stat] = $val;
                    }
                    break;
            }
            $i++;
        }
        return $data;
    }

    public function parsePitcherData($stats)
    {
        $i = 0;
        $player_data = [];
        $data = [];

        if (is_iterable($stats)) foreach ($stats as $stat) {

            if ($i%33 == 1) {
                $player_data = [];
                // Name
                $player_data['name'] = hQuery::fromHTML($stat->innerHTML)->find('a')->innerHTML;
                $player_data['name'] = preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']);
            } elseif ($i%33 == 7) {
                // K%
                $player_data['k_percentage'] = floatval($stat->innerHTML);
            } elseif ($i%33 == 8) {
                // BB%
                $player_data['bb_percentage'] = floatval($stat->innerHTML);
            } elseif ($i%33 == 9) {
                // K-BB%
                $player_data['kbb_percentage'] = floatval($stat->innerHTML);
            } elseif ($i%33 == 10) {
                // SwStr%
                $player_data['swstr_percentage'] = floatval($stat->innerHTML);
            } elseif ($i%33 == 19) {
                // Age
                $player_data['age'] = (int) $stat->innerHTML;
            } elseif ($i%33 == 20) {
                // Games
                $player_data['g'] = (int) $stat->innerHTML;
            } elseif ($i%33 == 21) {
                // Games
                $player_data['gs'] = (int) $stat->innerHTML;
            } elseif ($i%33 == 22) {
                // Games
                $player_data['k'] = (int) $stat->innerHTML;
            } elseif ($i%33 == 23) {
                $player_data['ip'] = $stat->innerHTML;
            } elseif ($i%33 == 24) {
                $player_data['k_percentage_plus'] = $stat->innerHTML;
            } elseif ($i%33 == 25) {
                $player_data['velo'] = (float) $stat->innerHTML;
            } elseif ($i%33 == 26) {
                $player_data['gb_percentage'] = (float)$stat->innerHTML;
            } elseif ($i%33 == 27) {
                $player_data['xera'] = (float)$stat->innerHTML;
            } elseif ($i%33 == 28) {
                $player_data['csw'] = (float)$stat->innerHTML;
            } elseif ($i%33 == 29) {
                $player_data['pa'] = (int)$stat->innerHTML;
            } elseif ($i%33 == 30) {
                if (!empty(trim($stat->innerHTML))) {
                    $velo = (float)$stat->innerHTML;
                    $player_data['velo'] = max($velo, ($player_data['velo'] ?? 0));
                }
            } elseif ($i%33 == 31) {
                if (!empty(trim($stat->innerHTML))) {
                    $velo = (float)$stat->innerHTML;
                    $player_data['velo'] = max($velo, ($player_data['velo'] ?? 0));
                }
            } elseif ($i%33 == 32) {
                if (!empty(trim($stat->innerHTML))) {
                    $velo = (float)$stat->innerHTML;
                    $player_data['velo'] = max($velo, ($player_data['velo'] ?? 0));
                }
                $data[strtolower($player_data['name'])] = [
                    'name' => $player_data['name'],
                    'k_percentage' => $player_data['k_percentage'],
                    'bb_percentage' => $player_data['bb_percentage'],
                    'kbb_percentage' => $player_data['kbb_percentage'],
                    'swstr_percentage' => $player_data['swstr_percentage'],
                    'gb_percentage' => $player_data['gb_percentage'],
                    'k_percentage_plus' => $player_data['k_percentage_plus'],
                    'age' => $player_data['age'],
                    'g' => $player_data['g'],
                    'k' => $player_data['k'],
                    'k_per_game' => $player_data['k'] / $player_data['g'],
                    'gs' => $player_data['gs'],
                    'ip' => $player_data['ip'],
                    'csw' => $player_data['csw'],
                    'pa' => $player_data['pa'],
                    //'xwoba' => $player_data['xera'],
                    'velo' => $player_data['velo'],
                ];
            }
            $i++;
        }

        return $data;
    }

    public function getHitterData()
    {
        $response = $this->httpClient->get($this->hitterDataSource);
        $responseBody = (string) $response->getBody();
        $doc = hQuery::fromHTML($responseBody);

        $stats = $doc->find('.grid_line_regular');

        return $this->parseHitterData($stats);
    }

    public function getHitterData2ndHalf()
    {
        $response = $this->httpClient->get($this->hitterDataSource2ndHalf);
        $responseBody = (string) $response->getBody();
        $doc = hQuery::fromHTML($responseBody);

        if ($doc) {
            $stats = $doc->find('.grid_line_regular');

            return $this->parseHitterData($stats);
        }
        return [];
    }

    public function getPitcherData()
    {
        $response = $this->httpClient->get($this->pitcherDataSource);
        $responseBody = (string) $response->getBody();

        $doc = hQuery::fromHTML($responseBody);

        $stats = $doc->find('.grid_line_regular');

        return $this->parsePitcherData($stats);
    }

    public function getReliefPitcherData()
    {
        $response = $this->httpClient->get(str_replace('stats=pit','stats=rel',$this->pitcherDataSource));
        $responseBody = (string) $response->getBody();

        $doc = hQuery::fromHTML($responseBody);

        $stats = $doc->find('.grid_line_regular');

        return $this->parsePitcherData($stats);
    }

    public function getPitcherDataLast30Days()
    {
        $response = $this->httpClient->get($this->pitcherDataSourceLast30Days);
        $responseBody = (string) $response->getBody();
        $doc = hQuery::fromHTML($responseBody);

        $stats = $doc->find('.grid_line_regular');

        return $this->parsePitcherData($stats);
    }

    public function getPitcherData1stHalf()
    {
        $response = $this->httpClient->get($this->pitcherDataSource1stHalf);
        $responseBody = (string) $response->getBody();
        $doc = hQuery::fromHTML($responseBody);

        $stats = $doc->find('.grid_line_regular');

        return $this->parsePitcherData($stats);
    }

    public function getPitcherData2ndHalf()
    {
        $response = $this->httpClient->get($this->pitcherDataSource2ndHalf);
        $responseBody = (string) $response->getBody();
        $doc = hQuery::fromHTML($responseBody);

        if ($doc) {
            $stats = $doc->find('.grid_line_regular');

            return $this->parsePitcherData($stats);
        }
        return [];
    }

    public function getReliefPitcherData2ndHalf()
    {
        $response = $this->httpClient->get(str_replace('stats=pit','stats=rel',$this->pitcherDataSource2ndHalf));
        $responseBody = (string) $response->getBody();
        $doc = hQuery::fromHTML($responseBody);

        if ($doc) {
            $stats = $doc->find('.grid_line_regular');

            return $this->parsePitcherData($stats);
        }
        return [];
    }

    public function getLeaguePitcherData()
    {
        $response = $this->httpClient->get($this->leaguePitchersDataSource);
        $responseBody = (string) $response->getBody();
        $doc = hQuery::fromHTML($responseBody);

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
            } else if ($i == 7) {
                $data['gb_percentage'] = floatval($stat->innerHTML);
                $this->league_pitcher_data['gb_percentage'] = number_format($data['gb_percentage'], 1);
            }
            $i++;
        }

        return $this->league_pitcher_data;
    }

    public function getLeagueBatterData()
    {
        $response = $this->httpClient->get($this->leagueBattersDataSource);
        $responseBody = (string) $response->getBody();
        $doc = hQuery::fromHTML($responseBody);

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
