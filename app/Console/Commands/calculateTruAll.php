<?php

namespace App\Console\Commands;

use App\League;
use Illuminate\Console\Command;
use App\Stat;
use Illuminate\Support\Facades\DB;

class calculateTruAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'z:truall';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate all years rankings';

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
        $years = League::all()->toArray();
        foreach ($years as $year) {;
            $this->call('z:tru', ['year' => $year['year']]);
        }

        return 1;
    }
}
