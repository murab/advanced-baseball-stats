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

        require_once __DIR__ . '/../../../lib/CustomStats.php';
        require_once __DIR__ . '/../../../lib/Formatter.php';

        $players_of_interest = json_decode(file_get_contents(__DIR__ . '/../../../players_of_interest.json'), true);

        $custom_lists = array_keys($players_of_interest);
        $custom_players = [];

        $starters = Stat::where(['year' => $year, 'position' => 'SP', ['tru', '<>', null]])->with('player')->orderBy('tru_rank', 'asc')->get()->toArray();
        $relievers = Stat::where(['year' => $year, 'position' => 'RP', ['tru', '<>', null]])->with('player')->orderBy('tru_rank', 'asc')->get()->toArray();

        $league = Stat::leagueAverageStats($year);

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
//            usort($custom_players[$list], function ($a, $b) {
//                return $b['rank_k_minus_adj_xwoba'] <=> $a['rank_k_minus_adj_xwoba'];
//            });
            if (!empty($custom_players[$list])) {
                echo "\n{$list}\n";
                foreach ($custom_players[$list] as $player) {
                    echo \Formatter::pitcherOutput($player);
                }
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

        Storage::disk('public')->put("{$year}.txt", $output);
        Storage::disk('public')->put($year.'/pitchers-' . date('Y-m-d') . '.txt', $output);

    }
}
