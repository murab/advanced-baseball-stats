<?php

namespace App\Console\Commands;

use App\Stat;
use App\League;
use Illuminate\Support\Facades\Storage;
use Illuminate\Console\Command;

class generateTextFile extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'z:text {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate TRU text file';

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

        require_once __DIR__ . '/../../../../lib/CustomStats.php';
        require_once __DIR__ . '/../../../../lib/Formatter.php';

        $players_of_interest = json_decode(file_get_contents(__DIR__ . '/../../../../players_of_interest.json'), true);

        $custom_lists = array_keys($players_of_interest);
        $custom_players = [];

        $a = new \CustomStats();

//        $fg = new FangraphsScraper();
//        $bs = new BaseballSavantScraper();
//        $prosp = new BaseballProspectusScraper();

//        $a->setFangraphsScraper($fg);
//        $a->setBaseballSavantScraper($bs);
//        $a->setBaseballProspectusScraper($prosp);

        /**
         *             $stats->secondhalf_velo = $data_2nd[$lowername]['velo'] ?? null;
        $stats->secondhalf_k_percentage = $data_2nd[$lowername]['k_percentage'] ?? null;
        $stats->secondhalf_bb_percentage = $data_2nd[$lowername]['bb_percentage'] ?? null;
        $stats->secondhalf_swstr_percentage = $data_2nd[$lowername]['swstr_percentage'] ?? null;
        $stats->secondhalf_k_percentage_plus = $data_2nd[$lowername]['k_percentage_plus'] ?? null;
        $stats->secondhalf_g = $data_2nd[$lowername]['g'] ?? null;
        $stats->secondhalf_gs = $data_2nd[$lowername]['gs'] ?? null;
        $stats->secondhalf_k = $data_2nd[$lowername]['k'] ?? null;
        $stats->secondhalf_k_per_game = $data_2nd[$lowername]['k_per_game'] ?? null;
        $stats->secondhalf_ip = $data_2nd[$lowername]['ip'] ?? null;
         */

        $data = Stat::where('year', $year)->get();
//        foreach ($data as $player) {
//            $data2ndHalf[$player['name']] = [
//                'year' => $player['year'],
//                'velo' => $player['secondhalf_velo'],
//                'k_percentage' => $player['secondhalf_k_percentage'],
//                'bb_percentage' => $player['secondhalf_bb_percentage'],
//                'swstr_percentage' => $player['secondhalf_']
//            ];
//        }

        //$data = $a->mergeSourceData($a->fgPitcherData, $a->bsData, $a->prospectusData);
        //$dataLast30 = $a->mergeSourceData($a->fgPitcherDataLast30Days, $a->bsDataLast30Days, $a->prospectusData);

        $filtered_data_last_30 = $a->filterPitcherData(Stat::secondHalf($year), 7, 3.25);
        $filtered_data = $a->filterPitcherData($data);

        $starters = Stat::where(['year' => $year, 'position' => 'SP', ['tru_rank', '<>', null]])->with('player')->orderBy('tru_rank', 'asc')->get()->toArray();
        $relievers = Stat::where(['year' => $year, 'position' => 'RP', ['tru_rank', '<>', null]])->with('player')->orderBy('tru_rank', 'asc')->get()->toArray();

        //die(var_dump($starters));

//        $starters = Stat::startingPitcherStats($year);
        //$startersLast30 = Stat::startingPitcherStats($year,true);
//        $relievers = Stat::reliefPitcherStats($year);
        //$relieversLast30 = Stat::reliefPitcherStats($year,true);
        $league = Stat::leagueAverageStats($year);

//        $startersLast30 = Stat::computeKperGameMinusAdjustedXwoba($year,'SP',true);
//        $starters = Stat::computeKperGameMinusAdjustedXwoba($year, 'SP');
//        $relieversLast30 = Stat::computeKperGameMinusAdjustedXwoba($year,'RP',true);
//        $relievers = Stat::computeKperGameMinusAdjustedXwoba($year,'RP');

        ob_start();

        echo \Formatter::leagueAveragePitcher($league);

        foreach ($starters as $key => $player) {

            $player_formatted_data = \Formatter::pitcher($player);

            foreach ($custom_lists as $list) {
                if (in_array($player['player']['name'], $players_of_interest[$list])) {
                    $custom_players[$list][] = $player_formatted_data;
                }
            }
        }

        foreach ($relievers as $key => $player) {

            $player_formatted_data = \Formatter::pitcher($player);

            foreach ($custom_lists as $list) {
                if (in_array($player['player']['name'], $players_of_interest[$list])) {
                    $custom_players[$list][] = $player_formatted_data;
                }
            }
        }

        foreach ($custom_lists as $list) {
            usort($custom_players[$list], function ($a, $b) {
                return $b['rank_k_minus_adj_xwoba'] <=> $a['rank_k_minus_adj_xwoba'];
            });
            echo "\n{$list}\n";
            foreach ($custom_players[$list] as $player) {
                echo \Formatter::pitcherOutput($player);
            }
        }

        echo "\nAll Starters\n";
        foreach ($starters as $key => $player) {

            $player_formatted_data = \Formatter::pitcher($player);

            echo \Formatter::pitcherOutput($player_formatted_data);

            foreach ($custom_lists as $list) {
                if (in_array($player['player']['name'], $players_of_interest[$list])) {
                    $custom_players[$list][] = $player_formatted_data;
                }
            }
        }

        echo "\nAll Relievers\n";
        foreach ($relievers as $key => $player) {

            $player_formatted_data = \Formatter::pitcher($player);

            echo \Formatter::pitcherOutput($player_formatted_data);

            foreach ($custom_lists as $list) {
                if (in_array($player['player']['name'], $players_of_interest[$list])) {
                    $custom_players[$list][] = $player_formatted_data;
                }
            }
        }

        echo "\n\n";

        $output = ob_get_contents();
        ob_end_clean();
//
//        if (!file_exists('path/to/directory')) {
//            mkdir('path/to/directory', 0777, true);
//        }

        Storage::disk('public')->put("{$year}.txt", $output);
        Storage::disk('public')->put($year.'/pitchers-' . date('Y-m-d') . '.txt', $output);

    }
}
