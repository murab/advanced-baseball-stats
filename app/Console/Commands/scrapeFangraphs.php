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
    const RAWpitcherDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=sta&lg=all&qual=0&type=c,36,37,38,40,120,121,217,113,42,43,117,118,6,45,62,122,3,7,8,24,13,310,76,48,332,331,14,139,140,144&season=2019&month=0&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=&enddate=&page=1_1500';
    const RAWpitcherDataSource2ndHalf = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=sta&lg=all&qual=0&type=c,36,37,38,40,120,121,217,113,42,43,117,118,6,45,62,122,3,7,8,24,13,310,76,48,332,331,14,139,140,144&season=2019&month=31&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=&enddate=&page=1_1500';
    const RAWpitcherDataSource1stHalf = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=sta&lg=all&qual=0&type=c,36,37,38,40,120,121,217,113,42,43,117,118,6,45,62,122,3,7,8,24,13,310,76,48,332,331,14,139,140,144&season=2019&month=30&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=&enddate=&page=1_1500';
    const RAWleagueBattersDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=bat&lg=all&qual=0&type=c,6,39&season=2019&month=0&season1=2019&ind=0&team=0,ss&rost=0&age=0&filter=&players=0&startdate=2019-01-01&enddate=2019-12-31';
    const RAWleaguePitchersDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=all&stats=sta&lg=all&qual=0&type=c,76,113,217,6,42,48&season=2019&month=0&season1=2019&ind=0&team=0,ss&rost=0&age=0&filter=&players=0&startdate=2019-01-01&enddate=2019-12-31';

    const RAWhitterDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=np&stats=bat&lg=all&qual=0&type=c,3,4,6,12,23,11,13,21,35,34,110,311,61,308,199,317&season=2019&month=0&season1=2019&ind=0&team=&rost=&age=&filter=&players=&startdate=&enddate=&page=1_3000';
    const RAWhitterDataSource2ndHalf = 'https://www.fangraphs.com/leaders.aspx?pos=np&stats=bat&lg=all&qual=0&type=c,3,4,6,12,23,11,13,21,35,34,110,311,61,308,199,317&season=2020&month=31&season1=2020&ind=0&team=&rost=&age=0&filter=&players=&page=1_3000';

    const RAWhitterVsLeftDataSource = 'https://www.fangraphs.com/leaders.aspx?pos=np&stats=bat&lg=all&qual=0&type=1&season=2019&month=13&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=2019-01-01&enddate=2019-12-31&page=1_3000';
    const RAWhitterVsLeftDataSource2ndHalf = 'https://www.fangraphs.com/leaders.aspx?pos=np&stats=bat&lg=all&qual=0&type=1&season=2019&month=13&season1=2019&ind=0&team=0&rost=0&age=0&filter=&players=0&startdate=2019-01-01&enddate=2019-12-31&page=1_3000';

    const RAWhitterBattedBallSplitsSource = 'https://www.fangraphs.com/leaders/splits-leaderboards?splitArr=12,18&splitArrPitch=&position=B&autoPt=false&splitTeams=false&statType=player&statgroup=3&startDate=2019-03-01&endDate=2019-11-01&players=&filter=&groupBy=season&wxTemperature=&wxPressure=&wxAirDensity=&wxElevation=&wxWindSpeed=&sort=12,1&pageitems=10000000000000&pg=0';

    const DUPLICATES_TO_SKIP = [
        'Luis Garcia' => ['STL', 'TEX'],
    ];

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
        $this->year = $year;

        $this->setUrls($year);

        $data = $this->getHitterData();
        $data = $this->getHitterSplitData($data);
        $data_2nd = $this->getHitterData2ndHalf();

        foreach ($data as $player) {
            if (!isset($player['name'])) { continue; }

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
                if (isset($player['secondhalf_'.$stat])) {
                    $player['secondhalf_'.$stat] = $data_2nd[$lowername][$stat] ?? null;
                }
            }
            unset($player['name']);

            $player['pa_per_g'] = $player['pa'] / $player['g'] ?? 0;

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
            $stats->era = $player['era'];
            $stats->whip = $player['whip'];
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
            $stats->secondhalf_k_percentage = $data_2nd[$lowername]['k_percentage'] ?? 0;
            $stats->secondhalf_era = $data_2nd[$lowername]['era'] ?? 0;
            $stats->secondhalf_whip = $data_2nd[$lowername]['whip'] ?? 0;
            $stats->secondhalf_bb_percentage = $data_2nd[$lowername]['bb_percentage'] ?? 0;
            $stats->secondhalf_swstr_percentage = $data_2nd[$lowername]['swstr_percentage'] ?? 0;
            $stats->secondhalf_gb_percentage = $data_2nd[$lowername]['gb_percentage'] ?? 0;
            $stats->secondhalf_k_percentage_plus = $data_2nd[$lowername]['k_percentage_plus'] ?? 0;
            $stats->secondhalf_g = $data_2nd[$lowername]['g'] ?? 0;
            $stats->secondhalf_gs = $data_2nd[$lowername]['gs'] ?? 0;
            $stats->secondhalf_k = $data_2nd[$lowername]['k'] ?? 0;
            $stats->secondhalf_k_per_game = $data_2nd[$lowername]['k_per_game'] ?? 0;
            $stats->secondhalf_ip = $data_2nd[$lowername]['ip'] ?? 0;
            $stats->secondhalf_pa = $data_2nd[$lowername]['pa'] ?? 0;
            //$stats->secondhalf_xwoba = $data_2nd[$lowername]['xera'] ?? null;
            $stats->secondhalf_velo = isset($data_2nd[$lowername]) ? $data_2nd[$lowername]['velo'] : 0;
            $stats->secondhalf_csw = isset($data_2nd[$lowername]) ? $data_2nd[$lowername]['csw'] : 0;

            $stats->save();
        }

        foreach ($rp_data as $player) {
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

            if (!empty($data[$lowername]) && $data[$lowername]['ip'] > $player['ip']) {
                continue;
            }

            $stats->age = $player['age'];
            $stats->position = 'RP';

            //$stats->velo = $player['velo'];
            $stats->k_percentage = $player['k_percentage'];
            $stats->era = $player['era'];
            $stats->whip = $player['whip'];
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
            $stats->secondhalf_k_percentage = $rp_data_2nd[$lowername]['k_percentage'] ?? 0;
            $stats->secondhalf_era = $rp_data_2nd[$lowername]['era'] ?? 0;
            $stats->secondhalf_whip = $rp_data_2nd[$lowername]['whip'] ?? 0;
            $stats->secondhalf_bb_percentage = $rp_data_2nd[$lowername]['bb_percentage'] ?? 0;
            $stats->secondhalf_swstr_percentage = $rp_data_2nd[$lowername]['swstr_percentage'] ?? 0;
            $stats->secondhalf_gb_percentage = $rp_data_2nd[$lowername]['gb_percentage'] ?? 0;
            $stats->secondhalf_k_percentage_plus = $rp_data_2nd[$lowername]['k_percentage_plus'] ?? 0;
            $stats->secondhalf_g = $rp_data_2nd[$lowername]['g'] ?? 0;
            $stats->secondhalf_gs = $rp_data_2nd[$lowername]['gs'] ?? 0;
            $stats->secondhalf_k = $rp_data_2nd[$lowername]['k'] ?? 0;
            $stats->secondhalf_k_per_game = $rp_data_2nd[$lowername]['k_per_game'] ?? 0;
            $stats->secondhalf_ip = $rp_data_2nd[$lowername]['ip'] ?? 0;
            $stats->secondhalf_pa = $rp_data_2nd[$lowername]['pa'] ?? 0;
            //$stats->secondhalf_xwoba = $data_2nd[$lowername]['xera'] ?? null;
            $stats->secondhalf_velo = isset($rp_data_2nd[$lowername]) ? $rp_data_2nd[$lowername]['velo'] : 0;
            $stats->secondhalf_csw = isset($rp_data_2nd[$lowername]) ? $rp_data_2nd[$lowername]['csw'] : 0;

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
        $this->hitterVsLeftDataSource = str_replace('2019',$year,self::RAWhitterVsLeftDataSource);
        $this->hitterVsLeftDataSource2ndHalf = str_replace('2019',$year,self::RAWhitterVsLeftDataSource2ndHalf);
        $this->RAWhitterBattedBallSplitsSource = str_replace('2019',$year,self::RAWhitterBattedBallSplitsSource);
    }

    public function getHitterSplitData($stats) {

        $cmd = "curl 'https://www.fangraphs.com/api/leaders/splits/splits-leaders' \
  -H 'authority: www.fangraphs.com' \
  -H 'accept: application/json, text/plain, */*' \
  -H 'accept-language: en-US,en;q=0.9' \
  -H 'cache-control: no-cache' \
  -H 'content-type: application/json' \
  -H 'cookie: _omappvp=0PXX6y2vYNCnNDIseEPRMOJlwIdkPDI4CkVvFSS0Kyzv86y6ixhB6gqJcJ19DnTFBImy2lMtCkaukxjAp7UPScvKZgnKdZ9z; wordpress_test_cookie=WP%20Cookie%20check; __qca=P0-1357311515-1679081076826; _ga=GA1.1.816244008.1679081079; _ga_757YGY2LKP=GS1.1.1681036933.2.1.1681037841.0.0.0; fg__ab-test=enabled; abtest_FL928EAM=ezoic; wordpress_logged_in_0cae6f5cb929d209043cb97f8c2eee44=yashi%7C1712936683%7C2zdon6eu5ySAObcv5jfWN6Upu2sBBGscSo7QAzuqKqK%7C55c61d8c02bae197fd079c6b8b0de6021e831f4c10221c1e92edcfc7d6eed4c4' \
  -H 'origin: https://www.fangraphs.com' \
  -H 'pragma: no-cache' \
  -H 'referer: https://www.fangraphs.com/leaders/splits-leaderboards?splitArr=12,18&splitArrPitch=&position=B&autoPt=false&splitTeams=false&statType=player&statgroup=3&startDate=2019-03-01&endDate=2019-11-01&players=&filter=&groupBy=season&wxTemperature=&wxPressure=&wxAirDensity=&wxElevation=&wxWindSpeed=&sort=12,1&pageitems=10000000000000&pg=0' \
  -H 'sec-ch-ua: \"Not.A/Brand\";v=\"8\", \"Chromium\";v=\"114\", \"Google Chrome\";v=\"114\"' \
  -H 'sec-ch-ua-mobile: ?0' \
  -H 'sec-ch-ua-platform: \"macOS\"' \
  -H 'sec-fetch-dest: empty' \
  -H 'sec-fetch-mode: cors' \
  -H 'sec-fetch-site: same-origin' \
  -H 'user-agent: Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/114.0.0.0 Safari/537.36' \
  --data-raw '{\"strPlayerId\":\"all\",\"strSplitArr\":[12],\"strGroup\":\"season\",\"strPosition\":\"B\",\"strType\":\"3\",\"strStartDate\":\"2019-03-01\",\"strEndDate\":\"2019-11-01\",\"strSplitTeams\":false,\"dctFilters\":[],\"strStatType\":\"player\",\"strAutoPt\":\"false\",\"arrPlayerId\":[],\"strSplitArrPitch\":[],\"arrWxTemperature\":null,\"arrWxPressure\":null,\"arrWxAirDensity\":null,\"arrWxElevation\":null,\"arrWxWindSpeed\":null}' \
  --compressed";

        $output = exec(str_replace("2019", $this->year, $cmd));

        $output = json_decode($output, true);

        if (is_iterable($output['data'])) foreach ($output['data'] as $stat) {

            if (!key_exists('playerName', $stat) || !is_numeric($stat['PA']) || !is_numeric($stat['Pull%'])) { continue; }

            $player_data = [];
            $player_data['name'] = $stat['playerName'];
            $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
            $player_data['name'] = strtr( $player_data['name'], $unwanted_array );
            $player_data['name'] = preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']);

            if (!isset($stats[strtolower($player_data['name'])]['g'])) {
                continue;
            }

            $stats[strtolower($player_data['name'])]['flyballs'] = trim($stat['PA']);
            $stats[strtolower($player_data['name'])]['pulled_flyball_percentage'] = trim($stat['Pull%']);
            $stats[strtolower($player_data['name'])]['pulled_flyballs'] = round(trim($stat['Pull%']) * trim($stat['PA']));
            $stats[strtolower($player_data['name'])]['pulled_flyballs_per_g'] = $stats[strtolower($player_data['name'])]['pulled_flyballs'] / $stats[strtolower($player_data['name'])]['g'];
            var_dump($stat);
        }

        return $stats;
    }

    public function parseHitterData($stats)
    {
        $i = 0;
        $player_data = [];
        $data = [];
        if (is_iterable($stats)) foreach ($stats as $stat)
        {
            $col = $i%19;
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
                    break;
                case 17:
                    // Def
                    $player_data['def'] = floatval($stat->innerHTML);
                    break;
                case 18:
                    // xWOBA
                    $player_data['xwoba'] = floatval($stat->innerHTML);
                    foreach ($player_data as $stat => $val) {
                        $data[strtolower($player_data['name'])][$stat] = $val;
                    }
                    break;
            }
            $i++;
        }
        return $data;
    }

    public function parseHitterVsLeftData($stats, $data = [])
    {
        $i = 0;
        $player_data = [];
        if (is_iterable($stats)) foreach ($stats as $stat)
        {
            $col = $i%18;
            switch ($col) {
                case 1:
                    $player_data = [];
                    // Name
                    $player_data['name'] = hQuery::fromHTML($stat->innerHTML)->find('a')->innerHTML;
                    $player_data['name'] = preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']);
                    break;
                case 17:
                    // wRC+ vs lefties
                    $data[strtolower($player_data['name'])]['vsleft_wrc_plus'] = (int) $stat->innerHTML;
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
            } elseif ($i%33 == 2) {
                // Team
                $team = hQuery::fromHTML($stat->innerHTML)->find('a');
                if ($team != null) {
                    $player_data['team'] = trim($team->innerHTML);
                    $player_data['team'] = preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['team']);
                }
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
            } elseif ($i%33 == 11) {
                $player_data['whip'] = (float)$stat->innerHTML;
            } elseif ($i%33 == 15) {
                $player_data['era'] = (float)$stat->innerHTML;
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
                $player_data['ip'] = (float) $stat->innerHTML;
            } elseif ($i%33 == 24) {
                $player_data['k_percentage_plus'] = (float) $stat->innerHTML;
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

                // skip certain players that have the same name that we don't care about (e.g. Luis Garcia TEX vs. Luis Garcia HOU)
                if (isset(self::DUPLICATES_TO_SKIP[$player_data['name']]) && in_array($player_data['team'], self::DUPLICATES_TO_SKIP[$player_data['name']])) {
                    $i++;
                    continue;
                }

                $data[strtolower($player_data['name'])] = [
                    'name' => $player_data['name'],
                    'era' => $player_data['era'],
                    'whip' => $player_data['whip'],
                    'k_percentage' => $player_data['k_percentage'],
                    'bb_percentage' => $player_data['bb_percentage'],
                    'kbb_percentage' => $player_data['kbb_percentage'],
                    'swstr_percentage' => $player_data['swstr_percentage'],
                    'gb_percentage' => $player_data['gb_percentage'],
                    'k_percentage_plus' => $player_data['k_percentage_plus'],
                    'age' => $player_data['age'],
                    'g' => $player_data['g'],
                    'k' => $player_data['k'],
                    'k_per_game' => $player_data['g'] ? $player_data['k'] / $player_data['g'] : 0,
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

        $data = $this->parseHitterData($stats);

        $response = $this->httpClient->get($this->hitterVsLeftDataSource);
        $responseBody = (string) $response->getBody();
        $doc = hQuery::fromHTML($responseBody);

        $stats = $doc->find('.grid_line_regular');

        $data = $this->parseHitterVsLeftData($stats, $data);

        return $data;
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
        $response = $this->httpClient->get(str_replace('stats=sta','stats=rel',$this->pitcherDataSource));
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
        $response = $this->httpClient->get($this->pitcherDataSource1stHalf,['http_errors' => false]);
        $responseBody = (string) $response->getBody();
        $doc = hQuery::fromHTML($responseBody);

        $stats = $doc->find('.grid_line_regular');

        return $this->parsePitcherData($stats);
    }

    public function getPitcherData2ndHalf()
    {
        $response = $this->httpClient->get($this->pitcherDataSource2ndHalf,['http_errors' => false]);
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
        $response = $this->httpClient->get(str_replace('stats=sta','stats=rel',$this->pitcherDataSource2ndHalf),['http_errors' => false]);
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
