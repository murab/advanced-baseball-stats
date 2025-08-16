<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Stat;
use Illuminate\Support\Facades\DB;

class calculateTru extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'z:tru {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Calculate and store pitcher true skill values';

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

        DB::statement('update stats set tru_rank = null, secondhalf_tru_rank = null, tru = null, secondhalf_tru = null, k_rank = null, xwoba_rank = null, whip_rank = null where year = ?',[$year]);

        $starters = Stat::computeKperGameMinusAdjustedXwoba($year, 'SP');
        $starters2ndHalf = Stat::computeKperGameMinusAdjustedXwoba($year, 'SP',true);
        $relievers = Stat::computeKperGameMinusAdjustedXwoba($year,'RP');
        $relievers2ndHalf = Stat::computeKperGameMinusAdjustedXwoba($year,'RP',true);
        foreach ($starters as $starter) {
            $Stat = Stat::find($starter['id']);
            $Stat->tru = $starter['tru'];
            $Stat->secondhalf_tru = $starters2ndHalf[$starter['id']]['tru'] ?? null;
            $Stat->tru_rank = $starter['tru_rank'];
            $Stat->secondhalf_tru_rank = $starters2ndHalf[$starter['id']]['tru_rank'] ?? null;
            $Stat->adjusted_xwoba = $starter['adjusted_xwoba'] ?? null;
            $Stat->k_rank = $starter['k_rank'] ?? null;
            $Stat->secondhalf_k_rank = $starters2ndHalf[$starter['id']]['k_rank'] ?? null;
            $Stat->ip_per_g_rank = $starter['ipg_rank'] ?? null;
            $Stat->secondhalf_ip_per_g_rank = $starters2ndHalf[$starter['id']]['ipg_rank'] ?? null;
            $Stat->xwoba_rank = $starter['xwoba_rank'] ?? null;
            $Stat->secondhalf_xwoba_rank = $starters2ndHalf[$starter['id']]['xwoba_rank'] ?? null;
            $Stat->whip_rank = $starter['whip_rank'] ?? null;
            $Stat->secondhalf_whip_rank = $starters2ndHalf[$starter['id']]['whip_rank'] ?? null;
            $Stat->save();
        }

        foreach ($relievers as $reliever) {
            $Stat = Stat::find($reliever['id']);
            $Stat->tru = $reliever['tru'];
            $Stat->secondhalf_tru = $relievers2ndHalf[$reliever['id']]['tru'] ?? null;
            $Stat->tru_rank = $reliever['tru_rank'];
            $Stat->secondhalf_tru_rank = $relievers2ndHalf[$reliever['id']]['tru_rank'] ?? null;
            $Stat->adjusted_xwoba = $reliever['adjusted_xwoba'] ?? null;
            $Stat->k_rank = $reliever['k_rank'] ?? null;
            $Stat->secondhalf_k_rank = $relievers2ndHalf[$reliever['id']]['k_rank'] ?? null;
            $Stat->ip_per_g_rank = $reliever['ipg_rank'] ?? null;
            $Stat->secondhalf_ip_per_g_rank = $relievers2ndHalf[$reliever['id']]['ipg_rank'] ?? null;
            $Stat->xwoba_rank = $reliever['xwoba_rank'] ?? null;
            $Stat->secondhalf_xwoba_rank = $relievers2ndHalf[$reliever['id']]['xwoba_rank'] ?? null;
            $Stat->save();
        }

        Stat::computeHitterRanks($year);

        return 1;
    }
}
