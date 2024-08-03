<?php

namespace App\Console\Commands;

use App\Player;
use App\Stat;
use App\League;
use App\Hitter;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use GuzzleHttp\Client;

class scrapeFangraphs extends Command
{
    const RAWpitcherDataSource = 'https://www.fangraphs.com/api/leaders/major-league/data?age=0&pos=all&stats=sta&lg=all&qual=0&season=2019&season1=2019&startdate=&enddate=&month=0&team=0&pageitems=2000000000&pagenum=1&ind=0&rost=0&players=0&type=c%2C36%2C37%2C38%2C40%2C120%2C121%2C217%2C113%2C42%2C43%2C117%2C118%2C6%2C45%2C62%2C122%2C3%2C7%2C8%2C24%2C13%2C310%2C76%2C48%2C332%2C331%2C14%2C139%2C140%2C144&sortdir=default&sortstat=pfxvSI';
    const RAWpitcherDataSource2ndHalf = 'https://www.fangraphs.com/api/leaders/major-league/data?age=0&pos=all&stats=sta&lg=all&qual=0&season=2019&season1=2019&startdate=&enddate=&month=31&team=0&pageitems=2000000000&pagenum=1&ind=0&rost=0&players=0&type=c%2C36%2C37%2C38%2C40%2C120%2C121%2C217%2C113%2C42%2C43%2C117%2C118%2C6%2C45%2C62%2C122%2C3%2C7%2C8%2C24%2C13%2C310%2C76%2C48%2C332%2C331%2C14%2C139%2C140%2C144&sortdir=default&sortstat=pfxvSI';
    const RAWpitcherDataSource1stHalf = 'https://www.fangraphs.com/api/leaders/major-league/data?age=0&pos=all&stats=sta&lg=all&qual=0&season=2019&season1=2019&startdate=&enddate=&month=30&team=0&pageitems=2000000000&pagenum=1&ind=0&rost=0&players=0&type=c%2C36%2C37%2C38%2C40%2C120%2C121%2C217%2C113%2C42%2C43%2C117%2C118%2C6%2C45%2C62%2C122%2C3%2C7%2C8%2C24%2C13%2C310%2C76%2C48%2C332%2C331%2C14%2C139%2C140%2C144&sortdir=default&sortstat=pfxvSI';
    const RAWleagueBattersDataSource = 'https://www.fangraphs.com/api/leaders/major-league/data?age=0&pos=all&stats=bat&lg=all&qual=0&season=2019&season1=2019&startdate=2019-01-01&enddate=2019-12-31&month=0&team=0%2Css&pageitems=30&pagenum=1&ind=0&rost=0&players=0&type=c%2C6%2C39&sortdir=default&sortstat=OPS';
    const RAWleaguePitchersDataSource = 'https://www.fangraphs.com/api/leaders/major-league/data?age=0&pos=all&stats=sta&lg=all&qual=0&season=2019&season1=2019&startdate=2019-01-01&enddate=2019-12-31&month=0&team=0%2Css&pageitems=30&pagenum=1&ind=0&rost=0&players=0&type=c%2C76%2C113%2C217%2C6%2C42%2C48&sortdir=default&sortstat=GB%25';

    const RAWhitterDataSource = 'https://www.fangraphs.com/api/leaders/major-league/data?age=&pos=np&stats=bat&lg=all&qual=0&season=2019&season1=2019&startdate=&enddate=&month=0&team=0&pageitems=2000000000&pagenum=1&ind=0&rost=0&players=&type=c%2C3%2C4%2C6%2C12%2C23%2C11%2C13%2C21%2C35%2C34%2C110%2C311%2C61%2C308%2C199%2C317&sortdir=default&sortstat=xwOBA';
    const RAWhitterDataSource2ndHalf = 'https://www.fangraphs.com/api/leaders/major-league/data?age=0&pos=np&stats=bat&lg=all&qual=0&season=2020&season1=2020&startdate=2023-03-01&enddate=2023-11-01&month=31&team=0&pageitems=2000000000&pagenum=1&ind=0&rost=0&players=&type=c%2C3%2C4%2C6%2C12%2C23%2C11%2C13%2C21%2C35%2C34%2C110%2C311%2C61%2C308%2C199%2C317&sortdir=default&sortstat=xwOBA';

    const RAWhitterVsLeftDataSource = 'https://www.fangraphs.com/api/leaders/major-league/data?age=0&pos=np&stats=bat&lg=all&qual=0&season=2019&season1=2019&startdate=2019-01-01&enddate=2019-12-31&month=13&team=0&pageitems=2000000000&pagenum=1&ind=0&rost=0&players=0&type=1&sortdir=default&sortstat=wRC%2B';
    const RAWhitterVsLeftDataSource2ndHalf = 'https://www.fangraphs.com/api/leaders/major-league/data?age=0&pos=np&stats=bat&lg=all&qual=0&season=2019&season1=2019&startdate=2019-01-01&enddate=2019-12-31&month=13&team=0&pageitems=2000000000&pagenum=1&ind=0&rost=0&players=0&type=1&sortdir=default&sortstat=wRC%2B';

    const RAWhitterBattedBallSplitsSource = 'https://www.fangraphs.com/leaders/splits-leaderboards?splitArr=12,18&splitArrPitch=&position=B&autoPt=false&splitTeams=false&statType=player&statgroup=3&startDate=2019-03-01&endDate=2019-11-01&players=&filter=&groupBy=season&wxTemperature=&wxPressure=&wxAirDensity=&wxElevation=&wxWindSpeed=&sort=12,1&pageitems=10000000000000&pg=0';

    const namesSavantToFangraphs = [
//        'Cedric Mullins' => 'Cedric Mullins II',
//        'Luis Robert Jr' => 'Luis Robert',
    ];

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
  --data-raw '{\"strPlayerId\":\"all\",\"strSplitArr\":[12,18],\"strGroup\":\"season\",\"strPosition\":\"B\",\"strType\":\"3\",\"strStartDate\":\"2019-03-01\",\"strEndDate\":\"2019-11-01\",\"strSplitTeams\":false,\"dctFilters\":[],\"strStatType\":\"player\",\"strAutoPt\":\"false\",\"arrPlayerId\":[],\"strSplitArrPitch\":[],\"arrWxTemperature\":null,\"arrWxPressure\":null,\"arrWxAirDensity\":null,\"arrWxElevation\":null,\"arrWxWindSpeed\":null}' \
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

            if (isset(self::namesSavantToFangraphs[$player_data['name']])) {
                $player_data['name'] = self::namesSavantToFangraphs[$player_data['name']];
            }

            if (!isset($stats[strtolower($player_data['name'])]['g'])) {
                continue;
            }

            $stats[strtolower($player_data['name'])]['flyballs'] = trim($stat['PA']); // hard hit flyballs
            $stats[strtolower($player_data['name'])]['pulled_flyball_percentage'] = trim($stat['Pull%']); //
            $stats[strtolower($player_data['name'])]['pulled_flyballs'] = round(trim($stat['Pull%']) * trim($stat['PA']));
            $stats[strtolower($player_data['name'])]['pulled_flyballs_per_g'] = $stats[strtolower($player_data['name'])]['pulled_flyballs'] / $stats[strtolower($player_data['name'])]['g'];
        }

        return $stats;
    }

    public function parseHitterData($stats)
    {
        $data = [];
        if (is_iterable($stats['data'])) foreach ($stats['data'] as $stat)
        {
            $player_data = [];

            // Name
            $player_data['name'] = $stat['PlayerName'];

            $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
            $player_data['name'] = strtr( $player_data['name'], $unwanted_array );

            $player_data['name'] = preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']);

            // Age
            $player_data['age'] = (int) $stat['Age'];

            // G
            $player_data['g'] = (int) $stat['G'];

            // PA
            $player_data['pa'] = (int) $stat['PA'];

            // R
            $player_data['r'] = (int) $stat['R'];

            // AVG
            $player_data['avg'] = floatval($stat['AVG']);

            // HR
            $player_data['hr'] = (int) $stat['HR'];

            // RBI
            $player_data['rbi'] = (int) $stat['RBI'];

            // SB
            $player_data['sb'] = (int) $stat['SB'];

            // CS
            $player_data['cs'] = (int) $stat['CS'];

            // K%
            $player_data['k_percentage'] = floatval($stat['K%'])*100;

            // BB%
            $player_data['bb_percentage'] = floatval($stat['BB%'])*100;

            // SwStr%
            $player_data['swstr_percentage'] = floatval($stat['SwStr%'])*100;

            // HardHit%
            $player_data['hardhit_percentage'] = floatval($stat['Hard%'])*100;

            // wRC+
            $player_data['wrc_plus'] = (int) $stat['wRC+'];

            // OPS
            $player_data['ops'] = floatval($stat['OPS']);

            // Barrels/BBE
            $player_data['brls_bbe'] = floatval($stat['Barrel%'])*100;

            // Def
            $player_data['def'] = floatval($stat['Defense']);

            // xWOBA
            if (!empty($stat['xwOBA'])) {
                $player_data['xwoba'] = floatval($stat['xwOBA']);
            }

            // xBA
            if (!empty($stat['xAVG'])) {
                $player_data['xba'] = floatval($stat['xAVG']);
            }

            foreach ($player_data as $stat => $val) {
                $data[strtolower($player_data['name'])][$stat] = $val;
            }
        }
        return $data;
    }

    public function parseHitterVsLeftData($stats, $data = [])
    {
        if (is_iterable($stats['data'])) foreach ($stats['data'] as $stat)
        {
                $player_data = [];

                // Name
                $player_data['name'] = $stat['PlayerName'];

                $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                    'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                    'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                    'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                    'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
                $player_data['name'] = strtr( $player_data['name'], $unwanted_array );

                $player_data['name'] = preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']);

                // wRC+ vs lefties
                $data[strtolower($player_data['name'])]['vsleft_wrc_plus'] = (int) $stat['wRC+'];
        }
        return $data;
    }

    public function parsePitcherData($stats)
    {
        $data = [];

        if (is_iterable($stats['data'])) foreach ($stats['data'] as $stat) {

            $player_data = [];

            // Name
            $player_data['name'] = $stat['PlayerName'];

            $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
            $player_data['name'] = strtr( $player_data['name'], $unwanted_array );

            $player_data['name'] = preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']);

            // Team
            $player_data['team'] = $stat['TeamName'];

            // K%
            $player_data['k_percentage'] = floatval($stat['K%'])*100;

            // BB%
            $player_data['bb_percentage'] = floatval($stat['BB%'])*100;

            // K-BB%
            $player_data['kbb_percentage'] = floatval($stat['K-BB%'])*100;

            // SwStr%
            $player_data['swstr_percentage'] = floatval($stat['SwStr%'])*100;

            $player_data['whip'] = (float)$stat['WHIP'];

            $player_data['era'] = (float)$stat['ERA'];

            // Age
            $player_data['age'] = (int) $stat['Age'];

            // Games
            $player_data['g'] = (int) $stat['G'];

            $player_data['gs'] = (int) $stat['GS'];

            $player_data['k'] = (int) $stat['SO'];

            $player_data['ip'] = (float) $stat['IP'];

            $player_data['k_percentage_plus'] = (float) $stat['K%+'];

            $player_data['velo'] = (float) max($stat['pfxvFA'], $stat['pfxvFT'], $stat['pfxvFC'], $stat['pfxvSI']);

            $player_data['gb_percentage'] = (float)$stat['GB%']*100;

            if (!empty($stat['xERA'])) {
                $player_data['xera'] = (float)$stat['xERA'];
            }

//            if (!empty($stat['CSW%'])) {
                $player_data['csw'] = (float)$stat['C+SwStr%']*100;
//            }

//            if (!empty($stat['PA'])) {
                $player_data['pa'] = (int)$stat['TBF'];
//            }

            // skip certain players that have the same name that we don't care about (e.g. Luis Garcia TEX vs. Luis Garcia HOU)
            if (isset(self::DUPLICATES_TO_SKIP[$player_data['name']]) && in_array($player_data['team'], self::DUPLICATES_TO_SKIP[$player_data['name']])) {
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

        return $data;
    }

    public function getHitterData()
    {
        $response = $this->httpClient->get($this->hitterDataSource);
        $responseBody = json_decode((string) $response->getBody(), true);
        $data = $this->parseHitterData($responseBody);

        $response = $this->httpClient->get($this->hitterVsLeftDataSource);
        $responseBody = json_decode((string) $response->getBody(), true);
        $data = $this->parseHitterVsLeftData($responseBody, $data);

        return $data;
    }

    public function getHitterData2ndHalf()
    {
        $response = $this->httpClient->get($this->hitterDataSource2ndHalf);
        $responseBody = json_decode((string) $response->getBody(), true);

        if ($responseBody) {
            return $this->parseHitterData($responseBody);
        }
        return [];
    }

    public function getPitcherData()
    {
        $response = $this->httpClient->get($this->pitcherDataSource);
        $responseBody = json_decode((string) $response->getBody(), true);

        return $this->parsePitcherData($responseBody);
    }

    public function getReliefPitcherData()
    {
        $response = $this->httpClient->get(str_replace('stats=sta','stats=rel',$this->pitcherDataSource));
        $responseBody = json_decode((string) $response->getBody(), true);

        return $this->parsePitcherData($responseBody);
    }

    public function getPitcherDataLast30Days()
    {
        $response = $this->httpClient->get($this->pitcherDataSourceLast30Days);
        $responseBody = json_decode((string) $response->getBody(), true);

        return $this->parsePitcherData($responseBody);
    }

    public function getPitcherData1stHalf()
    {
        $response = $this->httpClient->get($this->pitcherDataSource1stHalf,['http_errors' => false]);
        $responseBody = json_decode((string) $response->getBody(), true);

        return $this->parsePitcherData($responseBody);
    }

    public function getPitcherData2ndHalf()
    {
        $response = $this->httpClient->get($this->pitcherDataSource2ndHalf,['http_errors' => false]);
        $responseBody = json_decode((string) $response->getBody(), true);

        if ($responseBody) {
            return $this->parsePitcherData($responseBody);
        }
        return [];
    }

    public function getReliefPitcherData2ndHalf()
    {
        $response = $this->httpClient->get(str_replace('stats=sta','stats=rel',$this->pitcherDataSource2ndHalf),['http_errors' => false]);
        $responseBody = json_decode((string) $response->getBody(), true);

        if ($responseBody) {
            return $this->parsePitcherData($responseBody);
        }
        return [];
    }

    public function getLeaguePitcherData()
    {
        $response = $this->httpClient->get($this->leaguePitchersDataSource);
        $responseBody = json_decode((string) $response->getBody(), true);

        $data = [];

        if (empty($responseBody)) {
            echo "\nError: Could not fetch league pitcher data from FanGraphs.\n\n";
            exit;
        }

        foreach ($responseBody['data'] as $stat) {
            $data['fbv'] = floatval($stat['pfxvFA']);
            $this->league_pitcher_data['fbv'] = number_format($data['fbv'], 1);

            $data['swstr_percentage'] = floatval($stat['SwStr%']*100);
            $this->league_pitcher_data['swstr_percentage'] = number_format($data['swstr_percentage'], 1);

            $data['kbb_percentage'] = floatval($stat['K-BB%'])*100;
            $this->league_pitcher_data['kbb_percentage'] = number_format($data['kbb_percentage'], 1);

            $data['era'] = floatval($stat['ERA']);
            $this->league_pitcher_data['era'] = number_format($data['era'], 2);

            $data['whip'] = floatval($stat['WHIP']);
            $this->league_pitcher_data['whip'] = number_format($data['whip'], 2);

            $data['gb_percentage'] = floatval($stat['GB%']*100);
            $this->league_pitcher_data['gb_percentage'] = number_format($data['gb_percentage'], 1);
        }

        return $this->league_pitcher_data;
    }

    public function getLeagueBatterData()
    {
        $response = $this->httpClient->get($this->leagueBattersDataSource);
        $responseBody = json_decode((string) $response->getBody(), true);

        $data = [];

        if (empty($responseBody)) {
            echo "\nError: Could not fetch league batter data from FanGraphs.\n\n";
            exit;
        }

        foreach ($responseBody['data'] as $stat) {
            $data['ops'] = floatval($stat['OPS']);
            $this->league_batter_data['ops'] = $data['ops'];
        }

        return $this->league_batter_data;
    }
}
