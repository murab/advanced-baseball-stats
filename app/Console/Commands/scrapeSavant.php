<?php

namespace App\Console\Commands;

use App\Player;
use App\Hitter;
use App\Stat;
use Illuminate\Console\Command;
use duzun\hQuery;
use Illuminate\Support\Str;

class scrapeSavant extends Command
{
    const RAWpitchersXwobaURL = 'https://baseballsavant.mlb.com/statcast_search?hfPT=&hfAB=&hfBBT=&hfPR=&hfZ=&stadium=&hfBBL=&hfNewZones=&hfGT=R%7C&hfC=&hfSea=2019%7C&hfSit=&player_type=pitcher&hfOuts=&opponent=&pitcher_throws=&batter_stands=&hfSA=&game_date_gt=&game_date_lt=&hfInfield=&team=&position=&hfOutfield=&hfRO=&home_road=&hfFlag=&hfPull=&metric_1=&hfInn=&min_pitches=0&min_results=0&group_by=name&sort_col=xwoba&player_event_sort=h_launch_speed&sort_order=asc&min_pas=0&chk_stats_pa=on&chk_stats_xwoba=on#results';
    const RAWpitchersXwoba2ndHalfURL = 'https://baseballsavant.mlb.com/statcast_search?hfPT=&hfAB=&hfBBT=&hfPR=&hfZ=&stadium=&hfBBL=&hfNewZones=&hfGT=R%7C&hfC=&hfSea=2019%7C&hfSit=&player_type=pitcher&hfOuts=&opponent=&pitcher_throws=&batter_stands=&hfSA=&game_date_gt=2019-07-09&game_date_lt=&hfInfield=&team=&position=&hfOutfield=&hfRO=&home_road=&hfFlag=&hfPull=&metric_1=&hfInn=&min_pitches=0&min_results=0&group_by=name&sort_col=xwoba&player_event_sort=h_launch_speed&sort_order=asc&min_pas=0&chk_stats_pa=on&chk_stats_xwoba=on#results';

    const RAWhittersSprintSpeedURL = 'https://baseballsavant.mlb.com/leaderboard/sprint_speed?year=2019&position=&team=&min=0&csv=true';

    const namesFangraphsToSavant = [
        'Cedric Mullins' => 'Cedric Mullins II',
    ];

    private $pitchersXwobaURL;
    private $pitchersXwoba2ndHalfURL;

    private $hittersSprintSpeedURL;

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
    protected $description = 'Scrape Baseball Savant';

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

        foreach ($data as $player) {

            if (isset(self::namesFangraphsToSavant[$player['name']])) {
                $player['name'] = self::namesFangraphsToSavant[$player['name']];
            }

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

            $stats->xwoba = $player['xwoba'];
            $stats->secondhalf_xwoba = $data_2nd[$lowername]['xwoba'] ?? null;
            $stats->save();
        }

        $hitters = $this->getHittersSprintSpeedData();

        foreach ($hitters as $player) {

            if (isset(self::namesFangraphsToSavant[$player['name']])) {
                $player['name'] = self::namesFangraphsToSavant[$player['name']];
            }

            $Player = Player::firstOrCreate([
                'slug' => Str::slug($player['name'])
            ], [
                'name' => $player['name'],
            ]);

            $stats = Hitter::firstOrNew([
                'player_id' => $Player->id,
                'year' => $year,
            ]);

            $stats->sprint_speed = $player['sprint_speed'] ?? null;
            $stats->save();
        }

        return 1;
    }

    private function setUrls(?int $year = null)
    {
        $this->pitchersXwobaURL = str_replace('2019',$year,self::RAWpitchersXwobaURL);
        $this->pitchersXwoba2ndHalfURL = str_replace('2019', $year, self::RAWpitchersXwoba2ndHalfURL);
        $this->hittersSprintSpeedURL = str_replace('2019', $year, self::RAWhittersSprintSpeedURL);
    }

    private function parseXwobaData($players) {
        $data = [];

        if (is_iterable($players)) foreach ($players as $player) {

            $vals = $player->find('td');

            $i = 0;
            $player_data = [];
            foreach ($vals as $val) {

                if ($i == 2) {
                    // Name
                    $name = explode(', ', $val->innerHTML);
                    $player_data['name'] = trim($name[1]) . ' ' . trim($name[0]);
                    $player_data['name'] = iconv('UTF-8','ASCII//TRANSLIT',$player_data['name']);
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

    public function parseSprintSpeedData($players)
    {
        $data = [];

        if (is_iterable($players)) foreach ($players as $key => $player) {

            if ($key == 0) {
                continue;
            }

            $player_data = [];

                $player_data['name'] = trim($player[1]) . " " . trim($player[0]);
                $player_data['sprint_speed'] = floatval(trim($player['9']));

            if (!empty($player_data)) {
                $data[strtolower($player_data['name'])] = [
                    'name' => $player_data['name'],
                    'sprint_speed' => $player_data['sprint_speed']
                ];
            }
        }

        return $data;
    }

    public function getHittersSprintSpeedData()
    {
        $data = file_get_contents($this->hittersSprintSpeedURL);

        $rows = explode("\n", $data);
        $data_parsed = [];
        foreach($rows as $row){
            $data_parsed[] = ( str_getcsv( $row, ",", "'") );
        }

        $data = $this->parseSprintSpeedData($data_parsed);

        return $data;
    }

    public function getpitchersXwobaData()
    {
        hQuery::$cache_expires = 0;

        $doc = hQuery::fromUrl($this->pitchersXwobaURL, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            //'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
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
}
