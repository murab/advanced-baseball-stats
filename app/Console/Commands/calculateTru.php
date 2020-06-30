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

        DB::statement('update stats set tru = null, secondhalf_tru = null where year = ?',[$year]);

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
            $Stat->save();
        }

        foreach ($relievers as $reliever) {
            $Stat = Stat::find($reliever['id']);
            $Stat->tru = $reliever['tru'];
            $Stat->secondhalf_tru = $relievers2ndHalf[$reliever['id']]['tru'] ?? null;
            $Stat->tru_rank = $reliever['tru_rank'];
            $Stat->secondhalf_tru_rank = $relievers2ndHalf[$reliever['id']]['tru_rank'] ?? null;
            $Stat->adjusted_xwoba = $reliever['adjusted_xwoba'] ?? null;
            $Stat->save();
        }
        return 1;
    }
}
