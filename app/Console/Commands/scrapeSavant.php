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
    const RAWhittersBrlsPaURL = "https://baseballsavant.mlb.com/leaderboard/statcast?type=batter&year=2019&position=&team=&min=1&csv=true";
    const RAWhittersXstatsURL = "https://baseballsavant.mlb.com/leaderboard/expected_statistics?type=batter&year=2019&position=&team=&filterType=bip&min=1&csv=true";
    const RAWhittersHomeRunURL = "https://baseballsavant.mlb.com/leaderboard/home-runs?type=batter&year=2019&position=&team=&filterType=bip&min=1&csv=true";

    const RAWhittersPullFlyballURL = "https://baseballsavant.mlb.com/statcast_search?hfPT=&hfAB=&hfGT=R%7C&hfPR=&hfZ=&hfStadium=&hfBBL=&hfNewZones=&hfPull=Pull%7C&hfC=&hfSea=2019%7C&hfSit=&player_type=batter&hfOuts=&hfOpponent=&pitcher_throws=&batter_stands=&hfSA=&game_date_gt=&game_date_lt=&hfMo=&hfTeam=&home_road=&hfRO=&position=&hfInfield=&hfOutfield=&hfInn=&hfBBT=fly%5C.%5C.ball%7C&hfFlag=&metric_1=&group_by=name&min_pitches=0&min_results=0&min_pas=0&sort_col=pitches&player_event_sort=api_p_release_speed&sort_order=desc#results";

    const namesSavantToFangraphs = [
//        'Cedric Mullins' => 'Cedric Mullins II',
//        'Luis Robert Jr' => 'Luis Robert',
    ];

    const playersToSkip = [
        'Luis Garcia' => ['id_472610'],
        'Max Muncy' => ['id_691777'],
    ];

    const playersNotToSkipTeam = [
        'Luis Garcia' => 'WSH',
        'Max Muncy' => "LAD",
    ];

    private $pitchersXwobaURL;
    private $pitchersXwoba2ndHalfURL;

    private $hittersSprintSpeedURL;
    private $hittersBrlsPerPaURL;
    private $hittersXstatsURL;
    private $hittersHomeRunURL;

    private $hittersPullFlyballURL;

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

        $rp_data = $this->getreliefpitchersXwobaData();
        $rp_data_2nd = $this->getreliefpitchersXwobaData2ndHalf();

        foreach ($data as $player) {

            if (isset(self::namesSavantToFangraphs[$player['name']])) {
                $player['name'] = self::namesSavantToFangraphs[$player['name']];
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
                'position' => 'SP',
            ]);

            $stats->xwoba = $player['xwoba'];
            $stats->secondhalf_xwoba = $data_2nd[$lowername]['xwoba'] ?? null;
            $stats->save();
        }

        foreach ($rp_data as $player) {

            if (isset(self::namesSavantToFangraphs[$player['name']])) {
                $player['name'] = self::namesSavantToFangraphs[$player['name']];
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
                'position' => 'RP',
            ]);

            $stats->xwoba = $player['xwoba'];
            $stats->secondhalf_xwoba = $data_2nd[$lowername]['xwoba'] ?? null;
            $stats->save();
        }

        $hitters = $this->getHittersBrlPAData();
        $this->getHittersPullFlyballData($hitters);
        $this->getHittersXstatsData($hitters);
        $this->getHittersHomeRunData($hitters);
        $this->getHittersSprintSpeedData($hitters);

        foreach ($hitters as $player) {
            if (!isset($player['name'])) {
                continue;
            }
            if (isset(self::namesSavantToFangraphs[$player['name']])) {
                $player['name'] = self::namesSavantToFangraphs[$player['name']];
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
            $stats->brls_per_pa = $player['brls_per_pa'] ?? null;
            $stats->pulled_flyballs = $player['pulled_flyballs'] ?? null;
            $stats->xwoba = $player['xwoba'] ?? null;
            $stats->xhr = $player['xhr'] ?? null;
            $stats->save();
        }

        return 1;
    }

    private function setUrls(?int $year = null)
    {
        $this->pitchersXwobaURL = str_replace('2019',$year,self::RAWpitchersXwobaURL);
        $this->pitchersXwoba2ndHalfURL = str_replace('2019', $year, self::RAWpitchersXwoba2ndHalfURL);
        $this->hittersSprintSpeedURL = str_replace('2019', $year, self::RAWhittersSprintSpeedURL) . '&' . bin2hex(random_bytes(2));
        $this->hittersBrlsPerPaURL = str_replace('2019', $year, self::RAWhittersBrlsPaURL) . '&' . bin2hex(random_bytes(2));
        $this->hittersPullFlyballURL = str_replace('2019', $year, self::RAWhittersPullFlyballURL);
        $this->hittersXstatsURL = str_replace('2019', $year, self::RAWhittersXstatsURL) . '&' . bin2hex(random_bytes(2));
        $this->hittersHomeRunURL = str_replace('2019', $year, self::RAWhittersHomeRunURL) . '&' . bin2hex(random_bytes(2));
    }

    private function parseXwobaData($players) {
        $data = [];

        if (is_iterable($players)) foreach ($players as $player) {

            $vals = $player->find('td');

            $i = 0;
            $player_data = [];
            foreach ($vals as $val) {

                if ($i == 2) {
                    // Team
                    $player_data['savant_id'] = $val->attr('id');
                    // Name
                    $name = explode(', ', $val->innerHTML);
                    $player_data['name'] = trim($name[1]) . ' ' . trim($name[0]);
                    $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                        'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
                    $player_data['name'] = strtr( $player_data['name'], $unwanted_array );
                    $player_data['name'] = trim(preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']));
                    $player_data['name'] = str_replace(['span classsearch-labelRHPspan ', 'span classsearch-labelLHPspan ','span classsearch-labelspan ', 'span classsearch-labelLHP span ','span classsearch-labelRHP span '], '', $player_data['name']);
                } elseif ($i == 6) {
                    // PAs
                    $player_data['pa'] = (int)(str_replace(['<span>','</span>'],'',$val->innerHTML));
                } elseif ($i == 7) {
                    // xWOBA
                    $player_data['xwoba'] = floatval(str_replace(['<span>','</span>'],'',$val->innerHTML));
                }

                $i++;
            }

            if (isset(self::playersToSkip[$player_data['name']]) && in_array($player_data['savant_id'], self::playersToSkip[$player_data['name']])) {
                continue;
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

    public function parsePullFlyballData(&$data, $players) {
        if (is_iterable($players)) foreach ($players as $player) {

            $vals = $player->find('td');

            $i = 0;
            $player_data = [];
            foreach ($vals as $val) {

                if ($i == 2) {
                    // Team
                    $player_data['savant_id'] = $val->attr('id');
                    // Name
                    $name = explode(', ', $val->innerHTML);
                    $player_data['name'] = trim($name[1]) . ' ' . trim($name[0]);
                    $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                        'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                        'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                        'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                        'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
                    $player_data['name'] = strtr( $player_data['name'], $unwanted_array );
                    $player_data['name'] = trim(preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']));
                    $player_data['name'] = str_replace([' span classsearch-label span', 'span classsearch-labelRHPspan ', 'span classsearch-labelLHPspan ','span classsearch-labelspan ', 'span classsearch-labelLHP span ','span classsearch-labelRHP span '], '', $player_data['name']);
                } elseif ($i == 3) {
                    // PAs
                    $player_data['pulled_flyballs'] = (int)(str_replace(['<span>','</span>'],'',$val->innerHTML));
                }

                $i++;
            }

            if (isset(self::playersToSkip[$player_data['name']]) && in_array($player_data['savant_id'], self::playersToSkip[$player_data['name']])) {
                continue;
            }

            if (!empty($player_data['pulled_flyballs'])) {
                $data[strtolower($player_data['name'])]['pulled_flyballs'] = $player_data['pulled_flyballs'];
            }
        }
    }

    public function parseSprintSpeedData(&$hitters, $players)
    {
        if (is_iterable($players)) foreach ($players as $key => $player) {

            if ($key == 0) {
                continue;
            }

            $player_data = [];

            $player_data['name'] = trim($player[1]) . ' ' . trim($player[0]);
            $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
            $player_data['name'] = strtr( $player_data['name'], $unwanted_array );
            $player_data['name'] = trim(preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']));

            $player_data['sprint_speed'] = floatval(trim($player[10]," \""));

            if (isset(self::playersToSkip[$player_data['name']]) && in_array('id_'.trim($player['2'],"\""), self::playersToSkip[$player_data['name']])) {
                continue;
            }

            if (!empty($player_data) && !empty($hitters[strtolower($player_data['name'])])) {
                $hitters[strtolower($player_data['name'])]['sprint_speed'] = $player_data['sprint_speed'];
            }
        }
    }

    public function parseXstatsData(&$hitters, $players)
    {
        if (is_iterable($players)) foreach ($players as $key => $player) {

            if ($key == 0) {
                continue;
            }

            $player_data = [];

            $player_data['name'] = trim($player[1]) . ' ' . trim($player[0]);
            $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
            $player_data['name'] = strtr( $player_data['name'], $unwanted_array );
            $player_data['name'] = trim(preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']));

            $player_data['xwoba'] = floatval(trim($player[13]," \""));

            if (isset(self::playersToSkip[$player_data['name']]) && in_array('id_'.trim($player['2'],"\""), self::playersToSkip[$player_data['name']])) {
                continue;
            }

            if (!empty($player_data) && !empty($hitters[strtolower($player_data['name'])])) {
                $hitters[strtolower($player_data['name'])]['xwoba'] = $player_data['xwoba'];
            }
        }
    }

    public function parseHomeRunData(&$hitters, $players)
    {
        if (is_iterable($players)) foreach ($players as $key => $player) {

            if ($key == 0) {
                continue;
            }

            $player_data = [];

            $player_data['name'] = trim($player[1]) . ' ' . trim($player[0]);
            $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
            $player_data['name'] = strtr( $player_data['name'], $unwanted_array );
            $player_data['name'] = trim(preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']));

            $player_data['xhr'] = floatval(trim($player[12]," \""));
            $player_data['team'] = trim($player[3]," \"");

            if (isset(self::playersNotToSkipTeam[$player_data['name']]) && $player_data['team'] != self::playersNotToSkipTeam[$player_data['name']]) {
                continue;
            }

            if (!empty($player_data) && !empty($hitters[strtolower($player_data['name'])])) {
                $hitters[strtolower($player_data['name'])]['xhr'] = $player_data['xhr'];
            }
        }
    }

    public function parseBrlsPerPaData($players)
    {
        $data = [];

        if (is_iterable($players)) foreach ($players as $key => $player) {

            if ($key == 0) {
                continue;
            }

            $player_data = [];

            $player_data['name'] = trim($player[1]) . ' ' . trim($player[0]);
            $unwanted_array = array(    'Š'=>'S', 'š'=>'s', 'Ž'=>'Z', 'ž'=>'z', 'À'=>'A', 'Á'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Ä'=>'A', 'Å'=>'A', 'Æ'=>'A', 'Ç'=>'C', 'È'=>'E', 'É'=>'E',
                'Ê'=>'E', 'Ë'=>'E', 'Ì'=>'I', 'Í'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ñ'=>'N', 'Ò'=>'O', 'Ó'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 'Ù'=>'U',
                'Ú'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y', 'Þ'=>'B', 'ß'=>'Ss', 'à'=>'a', 'á'=>'a', 'â'=>'a', 'ã'=>'a', 'ä'=>'a', 'å'=>'a', 'æ'=>'a', 'ç'=>'c',
                'è'=>'e', 'é'=>'e', 'ê'=>'e', 'ë'=>'e', 'ì'=>'i', 'í'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'o', 'ñ'=>'n', 'ò'=>'o', 'ó'=>'o', 'ô'=>'o', 'õ'=>'o',
                'ö'=>'o', 'ø'=>'o', 'ù'=>'u', 'ú'=>'u', 'û'=>'u', 'ý'=>'y', 'þ'=>'b', 'ÿ'=>'y' );
            $player_data['name'] = strtr( $player_data['name'], $unwanted_array );
            $player_data['name'] = trim(preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']));

            $player_data['brls_per_pa'] = floatval(trim($player['18']));

            if (isset(self::playersToSkip[$player_data['name']]) && in_array('id_'.trim($player['2'],"\""), self::playersToSkip[$player_data['name']])) {
                continue;
            }

            if (!empty($player_data)) {
                $data[strtolower($player_data['name'])] = [
                    'name' => $player_data['name'],
                    'brls_per_pa' => $player_data['brls_per_pa']
                ];
            }
        }

        return $data;
    }

    public function getHittersSprintSpeedData(&$hitters)
    {
        $data = file_get_contents($this->hittersSprintSpeedURL);

        $rows = explode("\n", $data);
        $data_parsed = [];
        foreach($rows as $row){
            $data_parsed[] = ( str_getcsv( $row, ",", "'") );
        }

        $this->parseSprintSpeedData($hitters, $data_parsed);
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

    public function getreliefpitchersXwobaData()
    {
        hQuery::$cache_expires = 0;

        $doc = hQuery::fromUrl(str_replace('position=', 'position=RP', $this->pitchersXwobaURL), [
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

    public function getHittersBrlPAData()
    {
        $data = file_get_contents($this->hittersBrlsPerPaURL);

        $rows = explode("\n", $data);
        $data_parsed = [];
        foreach($rows as $row){
            $data_parsed[] = ( str_getcsv( $row, ",", "'") );
        }

        return $this->parseBrlsPerPaData($data_parsed);
    }

    public function getHittersXstatsData(&$hitters)
    {
        $data = file_get_contents($this->hittersXstatsURL);

        $rows = explode("\n", $data);
        $data_parsed = [];
        foreach($rows as $row){
            $data_parsed[] = ( str_getcsv( $row, ",", "'") );
        }

        $this->parseXstatsData($hitters, $data_parsed);
    }

    public function getHittersHomeRunData(&$hitters)
    {
        $data = file_get_contents($this->hittersHomeRunURL);

        $rows = explode("\n", $data);
        $data_parsed = [];
        foreach($rows as $row){
            $data_parsed[] = ( str_getcsv( $row, ",", "'") );
        }

        $this->parseHomeRunData($hitters, $data_parsed);
    }

    public function getHittersPullFlyballData(&$hitters)
    {
        hQuery::$cache_expires = 0;

        $doc = hQuery::fromUrl($this->hittersPullFlyballURL, [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            //'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $players = $doc->find('#search_results tbody tr.search_row');

        if ($players) {
            $this->parsePullFlyballData($hitters, $players);
        }
    }

    public function getReliefPitchersXwobaData2ndHalf()
    {
        hQuery::$cache_expires = 0;
        $doc = hQuery::fromUrl(str_replace('position=', 'position=RP',$this->pitchersXwoba2ndHalfURL), [
            'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
            'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
        ]);

        $players = $doc->find('#search_results tbody tr.search_row');
        $data = $this->parseXwobaData($players);
        return $data;
    }
}
