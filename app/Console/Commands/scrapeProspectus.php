<?php

namespace App\Console\Commands;

use App\Player;
use App\Stat;
use Illuminate\Console\Command;
use duzun\hQuery;
use Illuminate\Support\Str;

class scrapeProspectus extends Command
{
    const RAWoppRPAplusURL = "https://legacy.baseballprospectus.com/sortable/index.php?mystatslist=LVL,NAME,TEAM,LG,YEAR,AGE,G,GS,IP,PA,AB,AVG,OBP,SLG,OPP_QUAL_AVG,OPP_QUAL_OBP,OPP_QUAL_SLG,OPP_QUAL_TAV,OPP_QUAL_RPA_PLUS,OPP_QUAL_OPS,PPF,PVORP&category=pitcher_team_year&tablename=dyna_pitcher_team_year&stage=data&year=2019&group_TEAM=*&group_LVL=MLB&group_LG=*&minimum=0&sort1column=OPP_QUAL_RPA_PLUS&sort1order=DESC&page_limit=1500&glossary_terms=*&viewdata=View%20Data&start_num=0";
    const urls = [
        '2019' => 'https://legacy.baseballprospectus.com/sortable/index.php?cid=4253798',
    ];

    private $oppRPAplusURL;
    private $data;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:prospectus {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape Baseball Prospectus';

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

        $this->setUrl($year);

        hQuery::$cache_expires = 0;

        try {
            $doc = hQuery::fromUrl($this->oppRPAplusURL, [
                'Host' => 'legacy.baseballprospectus.com',
                'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10.14; rv:77.0) Gecko/20100101 Firefox/77.0',
                'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,*/*;q=0.8',
                'Accept-Language' => 'en-US,en;q=0.5',
                'Accept-Encoding' => 'gzip, deflate, br',
                'Connection' => 'keep-alive',
                'Cookie' => 'sessiontoken=1063826253; PHPSESSID=48hq668vlvk560cqehr5vgugp5',
                'Upgrade-Insecure-Requests' => '1',
                'Pragma' => 'no-cache',
                'Cache-Control' => 'no-cache',
            ]);
            if (!$doc) {
                return 0;
            }
        } catch (\Exception $e) {
            $this->data = [];
            return 1;
        }

        $players = $doc->find('tr.TTdata,tr.TTdata_ltgrey');

        if (!$players) {
            $this->data = [];
            return 1;
        }

        foreach ($players as $player) {

            $vals = $player->find('td');

            $i = 0;
            $player_data = [];
            foreach ($vals as $val) {
                if ($i == 2) {
                    // Name
                    $player_data['name'] = hQuery::fromHTML($val->innerHTML)->find('a')->innerHTML;
                    $player_data['name'] = preg_replace("/[^A-Za-z0-9\- ]/", '', $player_data['name']);
                } elseif ($i == 19) {
                    // oppRPA+
                    $player_data['opprpa'] = (int) $val->innerHTML;
                } elseif ($i == 20) {
                    // oppOPS
                    $player_data['oppops'] = (float) $val->innerHTML;
                }

                $i++;
            }

            $this->data[strtolower($player_data['name'])] = [
                'name' => $player_data['name'],
                'opprpa' => $player_data['opprpa'],
                'oppops' => $player_data['oppops']
            ];
        }

        foreach ($this->data as $player) {
            $Player = Player::firstOrCreate([
                'slug' => Str::slug($player['name'])
            ], [
                'name' => $player['name'],
            ]);

            $stats = Stat::firstOrNew([
                'player_id' => $Player->id,
                'year' => $year,
            ]);

            $stats->opprpa = $player['opprpa'];
            $stats->oppops = $player['oppops'];

            $stats->save();
        }

        return 1;
    }

    private function setUrl(int $year)
    {
        $this->oppRPAplusURL = str_replace('2019',$year,self::RAWoppRPAplusURL);
        //$this->oppRPAplusURL = self::urls[$year];
    }
}
