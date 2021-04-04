<?php

namespace App\Console\Commands;

use App\Player;
use App\Stat;
use Illuminate\Console\Command;
use duzun\hQuery;
use Illuminate\Support\Str;

class scrapeSavant extends Command
{
    const RAWpitchersXwobaURL = 'https://baseballsavant.mlb.com/statcast_search?hfPT=&hfAB=&hfBBT=&hfPR=&hfZ=&stadium=&hfBBL=&hfNewZones=&hfGT=R%7C&hfC=&hfSea=2019%7C&hfSit=&player_type=pitcher&hfOuts=&opponent=&pitcher_throws=&batter_stands=&hfSA=&game_date_gt=&game_date_lt=&hfInfield=&team=&position=&hfOutfield=&hfRO=&home_road=&hfFlag=&hfPull=&metric_1=&hfInn=&min_pitches=0&min_results=0&group_by=name&sort_col=xwoba&player_event_sort=h_launch_speed&sort_order=asc&min_pas=0&chk_stats_pa=on&chk_stats_xwoba=on#results';
    const RAWpitchersXwoba2ndHalfURL = 'https://baseballsavant.mlb.com/statcast_search?hfPT=&hfAB=&hfBBT=&hfPR=&hfZ=&stadium=&hfBBL=&hfNewZones=&hfGT=R%7C&hfC=&hfSea=2019%7C&hfSit=&player_type=pitcher&hfOuts=&opponent=&pitcher_throws=&batter_stands=&hfSA=&game_date_gt=2019-07-09&game_date_lt=&hfInfield=&team=&position=&hfOutfield=&hfRO=&home_road=&hfFlag=&hfPull=&metric_1=&hfInn=&min_pitches=0&min_results=0&group_by=name&sort_col=xwoba&player_event_sort=h_launch_speed&sort_order=asc&min_pas=0&chk_stats_pa=on&chk_stats_xwoba=on#results';
    const RAWpitchersVeloURL = 'https://baseballsavant.mlb.com/statcast_search?hfPT=FF%7CFT%7CSI%7C&hfAB=&hfBBT=&hfPR=&hfZ=&stadium=&hfBBL=&hfNewZones=&hfGT=R%7C&hfC=&hfSea=2019%7C&hfSit=&player_type=pitcher&hfOuts=&opponent=&pitcher_throws=&batter_stands=&hfSA=&game_date_gt=&game_date_lt=&hfInfield=&team=&position=&hfOutfield=&hfRO=&home_road=&hfFlag=&hfPull=&metric_1=&hfInn=&min_pitches=0&min_results=0&group_by=name&sort_col=velocity&player_event_sort=h_launch_speed&sort_order=desc&min_pas=0&chk_stats_velocity=on#results';
    const RAWpitchersVelo2ndHalfURL = 'https://baseballsavant.mlb.com/statcast_search?hfPT=FF%7CFT%7CSI%7C&hfAB=&hfBBT=&hfPR=&hfZ=&stadium=&hfBBL=&hfNewZones=&hfGT=R%7C&hfC=&hfSea=2019%7C&hfSit=&player_type=pitcher&hfOuts=&opponent=&pitcher_throws=&batter_stands=&hfSA=&game_date_gt=2019-07-09&game_date_lt=&hfInfield=&team=&position=&hfOutfield=&hfRO=&home_road=&hfFlag=&hfPull=&metric_1=&hfInn=&min_pitches=0&min_results=0&group_by=name&sort_col=velocity&player_event_sort=h_launch_speed&sort_order=desc&min_pas=0&chk_stats_velocity=on#results';
    const RAWpitchersCswURL = 'https://baseballsavant.mlb.com/statcast_search?hfPT=&hfAB=&hfBBT=&hfPR=called%5C.%5C.strike%7Cfoul%5C.%5C.tip%7Cswinging%5C.%5C.pitchout%7Cswinging%5C.%5C.strike%7Cswinging%5C.%5C.strike%5C.%5C.blocked%7C&hfZ=&stadium=&hfBBL=&hfNewZones=&hfGT=R%7C&hfC=&hfSea=2019%7C&hfSit=&player_type=pitcher&hfOuts=&opponent=&pitcher_throws=&batter_stands=&hfSA=&game_date_gt=&game_date_lt=&hfInfield=&team=&position=&hfOutfield=&hfRO=&home_road=&hfFlag=&hfPull=&metric_1=&hfInn=&min_pitches=0&min_results=0&group_by=name&sort_col=pitch_percent&player_event_sort=api_h_launch_speed&sort_order=desc&min_pas=0&chk_stats_pa=on&chk_stats_xwoba=on#results';
    const RAWpitchersCsw2ndHalfURL = 'https://baseballsavant.mlb.com/statcast_search?hfPT=&hfAB=&hfBBT=&hfPR=called%5C.%5C.strike%7Cfoul%5C.%5C.tip%7Cswinging%5C.%5C.pitchout%7Cswinging%5C.%5C.strike%7Cswinging%5C.%5C.strike%5C.%5C.blocked%7C&hfZ=&stadium=&hfBBL=&hfNewZones=&hfGT=R%7C&hfC=&hfSea=2019%7C&hfSit=&player_type=pitcher&hfOuts=&opponent=&pitcher_throws=&batter_stands=&hfSA=&game_date_gt=2019-07-09&game_date_lt=&hfInfield=&team=&position=&hfOutfield=&hfRO=&home_road=&hfFlag=&hfPull=&metric_1=&hfInn=&min_pitches=0&min_results=0&group_by=name&sort_col=pitch_percent&player_event_sort=api_h_launch_speed&sort_order=desc&min_pas=0&chk_stats_pa=on&chk_stats_xwoba=on#results';

    private $pitchersXwobaURL;
    private $pitchersXwoba2ndHalfURL;
    private $pitchersVeloURL;
    private $pitchersVelo2ndHalfURL;
    private $pitchersCswURL;
    private $pitchersCsw2ndHalfURL;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:savant {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape Baseball Savant Pitchers';

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

        $data = $this->getpitchersXwobaData();
        $data_2nd = $this->getPitchersXwobaData2ndHalf();
        $velo = $this->getpitchersVeloData();
        $velo_2nd = $this->getPitchersVeloData2ndHalf();
        $csw = $this->getPitchersCswData();
        $csw_2nd = $this->getPitchersCswData2ndHalf();

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

            $stats->pa = $player['pa'];
            $stats->secondhalf_pa = $data_2nd[$lowername]['pa'] ?? null;
            $stats->xwoba = $player['xwoba'];
            $stats->secondhalf_xwoba = $data_2nd[$lowername]['xwoba'] ?? null;
            $stats->velo = isset($velo[$lowername]) ? $velo[$lowername]['velo'] : 0;
            $stats->secondhalf_velo = isset($velo_2nd[$lowername]) ? $velo_2nd[$lowername]['velo'] : 0;
            $stats->csw = isset($csw[$lowername]) ? $csw[$lowername]['csw'] : 0;
            $stats->secondhalf_csw = isset($csw_2nd[$lowername]) ? $csw_2nd[$lowername]['csw'] : 0;
            $stats->save();
        }

        return 1;
    }

    private function setUrls(?int $year = null)
    {
        $this->pitchersXwobaURL = str_replace('2019',$year,self::RAWpitchersXwobaURL);
        $this->pitchersXwoba2ndHalfURL = str_replace('2019', $year, self::RAWpitchersXwoba2ndHalfURL);
        $this->pitchersVeloURL = str_replace('2019',$year,self::RAWpitchersVeloURL);
        $this->pitchersVelo2ndHalfURL = str_replace('2019',$year,self::RAWpitchersVelo2ndHalfURL);
        $this->pitchersCswURL = str_replace('2019',$year,self::RAWpitchersCswURL);
        $this->pitchersCsw2ndHalfURL = str_replace('2019',$year,self::RAWpitchersCsw2ndHalfURL);
    }

    private function parseXwobaData($players) {
        $data = [];

        if (is_array($players)) foreach ($players as $player) {

            $vals = $player->find('td');

            $i = 0;
            $player_data = [];
            foreach ($vals as $val) {

                if ($i == 2) {
                    // Name
                    $name = explode(', ', $val->innerHTML);
                    $player_data['name'] = trim($name[1]) . ' ' . trim($name[0]);
                    $player_data['name'] = trim(preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']));
                } elseif ($i == 6) {
                    // PAs
                    $player_data['pa'] = (int)(str_replace(['<span>','</span>'],'',$val->innerHTML));
                } elseif ($i == 7) {
                    // xWOBA
                    $player_data['xwoba'] = floatval(str_replace(['<span>','</span>'],'',$val->innerHTML));
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

    private function parseVeloData($players) {
        $data = [];

        if (is_array($players)) foreach ($players as $player) {

            $vals = $player->find('td');

            $i = 0;
            $player_data = [];
            foreach ($vals as $val) {

                if ($i == 2) {
                    // Name
                    $name = explode(', ', $val->innerHTML);
                    $player_data['name'] = trim($name[1]) . ' ' . trim($name[0]);
                    $player_data['name'] = trim(preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']));
                } elseif ($i == 6) {
                    // Velo
                    $player_data['velo'] = (str_replace(['<span>','</span>'],'',$val->innerHTML));
                }

                $i++;
            }
            if (!empty($player_data['velo'])) {
                $data[strtolower($player_data['name'])] = [
                    'name' => $player_data['name'],
                    'velo' => $player_data['velo'],
                ];
            }
        }
        return $data;
    }

    private function parseCswData($players) {
        $data = [];

        if (is_array($players)) foreach ($players as $player) {

            $vals = $player->find('td');

            $i = 0;
            $player_data = [];
            foreach ($vals as $val) {

                if ($i == 2) {
                    // Name
                    $name = explode(', ', $val->innerHTML);
                    $player_data['name'] = trim($name[1]) . ' ' . trim($name[0]);
                    $player_data['name'] = trim(preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']));
                } elseif ($i == 5) {
                    // CSW
                    $player_data['csw'] = (str_replace(['<span>','</span>'],'',$val->innerHTML));
                }

                $i++;
            }
            if (!empty($player_data['csw'])) {
                $data[strtolower($player_data['name'])] = [
                    'name' => $player_data['name'],
                    'csw' => $player_data['csw'],
                ];
            }
        }
        return $data;
    }

    public function getpitchersXwobaData()
    {
        hQuery::$cache_expires = 0;

        $doc = hQuery::fromUrl($this->pitchersXwobaURL, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $players = $doc->find('#search_results tbody tr.search_row');

        $data = [];
        if ($players) {
            $data = $this->parseXwobaData($players);
        }

        return $data;
    }

    public function getPitchersXwobaData2ndHalf()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl($this->pitchersXwoba2ndHalfURL, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $players = $doc->find('#search_results tbody tr.search_row');
        $data = $this->parseXwobaData($players);
        return $data;
    }

    public function getpitchersVeloData()
    {
        hQuery::$cache_expires = 0;

        $doc = hQuery::fromUrl($this->pitchersVeloURL, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $players = $doc->find('#search_results tbody tr.search_row');

        $data = [];
        if ($players) {
            $data = $this->parseVeloData($players);
        }

        return $data;
    }

    public function getPitchersVeloData2ndHalf()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl($this->pitchersVelo2ndHalfURL, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $players = $doc->find('#search_results tbody tr.search_row');
        $data = $this->parseVeloData($players);
        return $data;
    }

    public function getPitchersCswData()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl($this->pitchersCswURL, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $players = $doc->find('#search_results tbody tr.search_row');
        $data = $this->parseCswData($players);
        return $data;
    }

    public function getPitchersCswData2ndHalf()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl($this->pitchersCsw2ndHalfURL, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $players = $doc->find('#search_results tbody tr.search_row');
        $data = $this->parseCswData($players);
        return $data;
    }
}
