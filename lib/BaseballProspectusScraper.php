<?php

require_once __DIR__ . '/../vendor/autoload.php';

use duzun\hQuery;

class BaseballProspectusScraper
{
    // 1500 pitchers sorted by oppRPA+
    const oppRPAplusURL = "https://legacy.baseballprospectus.com/sortable/index.php?mystatslist=LVL,NAME,TEAM,LG,YEAR,AGE,G,GS,IP,PA,AB,AVG,OBP,SLG,OPP_QUAL_AVG,OPP_QUAL_OBP,OPP_QUAL_SLG,OPP_QUAL_TAV,OPP_QUAL_RPA_PLUS,OPP_QUAL_OPS,PPF,PVORP&category=pitcher_team_year&tablename=dyna_pitcher_team_year&stage=data&year=2019&group_TEAM=*&group_LVL=MLB&group_LG=*&minimum=0&sort1column=OPP_QUAL_RPA_PLUS&sort1order=DESC&page_limit=1500&glossary_terms=*&viewdata=View%20Data&start_num=0";

    private $data;

    public function __construct()
    {
        hQuery::$cache_expires = 0;
        try {
            $doc = hQuery::fromUrl(self::oppRPAplusURL, [
                'Accept'     => 'text/html,application/xhtml+xml;q=0.9,*/*;q=0.8',
                //'User-Agent' => 'Mozilla/5.0 (Macintosh; Intel Mac OS X 10_14_5) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/74.0.3729.169 Safari/537.36',
            ]);
        } catch (Exception $e) {
            $this->data = [];
            return;
        }

        $players = $doc->find('tr.TTdata,tr.TTdata_ltgrey');

        $i = 0;
        $player_data = [];
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
    }

    public function getData()
    {
        return $this->data;
    }
}
