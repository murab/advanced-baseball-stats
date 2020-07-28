<?php

namespace App\Console\Commands;

use App\Player;
use App\Stat;
use App\League;
use Illuminate\Console\Command;
use duzun\hQuery;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class scrapeFangraphs extends Command
{
    const RAWpitcherDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=pit&lg=all&qual=0&type=c,36,37,38,40,120,121,217,113,42,43,117,118,6,45,62,122,3,7,8,24,13,310,76&season=2019&month=0&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=&enddate=&page=1_1500';
    const RAWpitcherDataSourceLast30Days = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=pit&lg=all&qual=0&type=c,36,37,38,40,120,121,217,113,42,43,117,118,6,45,62,122,3,7,8,24,13,310,76&season=2019&month=3&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=&enddate=&page=1_1500';
    const RAWpitcherDataSource2ndHalf = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=pit&lg=all&qual=0&type=c,36,37,38,40,120,121,217,113,42,43,117,118,6,45,62,122,3,7,8,24,13,310,76&season=2019&month=31&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=&enddate=&page=1_1500';
    const RAWpitcherDataSource1stHalf = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=pit&lg=all&qual=0&type=c,36,37,38,40,120,121,217,113,42,43,117,118,6,45,62,122,3,7,8,24,13,310,76&season=2019&month=30&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=&enddate=&page=1_1500';
    const RAWleagueBattersDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=bat&lg=all&qual=0&type=c,6,39&season=2019&month=0&season1=2019&ind=0&team=0,ss&rost=0&age=0&filter=&players=0&startdate=2019-01-01&enddate=2019-12-31';
    const RAWleaguePitchersDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=sta&lg=all&qual=0&type=c,76,113,217,6,42&season=2019&month=0&season1=2019&ind=0&team=0,ss&rost=0&age=0&filter=&players=0&startdate=2019-01-01&enddate=2019-12-31';

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

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
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

        $data = $this->getPitcherData();
        $data_2nd = $this->getPitcherData2ndHalf();

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

            //$stats->velo = $player['velo'];
            $stats->k_percentage = $player['k_percentage'];
            $stats->bb_percentage = $player['bb_percentage'];
            $stats->swstr_percentage = $player['swstr_percentage'];
            $stats->k_percentage_plus = $player['k_percentage_plus'];
            $stats->g = $player['g'];
            $stats->gs = $player['gs'];
            $stats->k = $player['k'];
            $stats->k_per_game = $player['k_per_game'];
            $stats->ip = $player['ip'];

            //$stats->secondhalf_velo = $data_2nd[$lowername]['velo'] ?? null;
            $stats->secondhalf_k_percentage = $data_2nd[$lowername]['k_percentage'] ?? null;
            $stats->secondhalf_bb_percentage = $data_2nd[$lowername]['bb_percentage'] ?? null;
            $stats->secondhalf_swstr_percentage = $data_2nd[$lowername]['swstr_percentage'] ?? null;
            $stats->secondhalf_k_percentage_plus = $data_2nd[$lowername]['k_percentage_plus'] ?? null;
            $stats->secondhalf_g = $data_2nd[$lowername]['g'] ?? null;
            $stats->secondhalf_gs = $data_2nd[$lowername]['gs'] ?? null;
            $stats->secondhalf_k = $data_2nd[$lowername]['k'] ?? null;
            $stats->secondhalf_k_per_game = $data_2nd[$lowername]['k_per_game'] ?? null;
            $stats->secondhalf_ip = $data_2nd[$lowername]['ip'] ?? null;

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
        $doc = hQuery::fromUrl($this->pitcherDataSource, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            //'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $stats = $doc->find('.grid_line_regular');

        return $this->parsePitcherData($stats);
    }

    public function getPitcherDataLast30Days()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl($this->pitcherDataSourceLast30Days, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            //'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $stats = $doc->find('.grid_line_regular');

        return $this->parsePitcherData($stats);
    }

    public function getPitcherData1stHalf()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl($this->pitcherDataSource1stHalf, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            //'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $stats = $doc->find('.grid_line_regular');

        return $this->parsePitcherData($stats);
    }

    public function getPitcherData2ndHalf()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl($this->pitcherDataSource2ndHalf, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            //'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        if ($doc) {
            $stats = $doc->find('.grid_line_regular');

            return $this->parsePitcherData($stats);
        }
        return [];
    }

    public function getLeaguePitcherData()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl($this->leaguePitchersDataSource, [
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
        $doc = hQuery::fromUrl($this->leagueBattersDataSource, [
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
