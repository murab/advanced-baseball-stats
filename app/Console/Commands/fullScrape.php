<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class fullScrape extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'z:full {year?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Full scrape and file generation';

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

        if ($year == 'all') {
            $this->info('Starting full scrape of all years since 2015...');
            for ($year = 2015; $year <= date('Y'); $year++) {
                $this->info('Scraping Fangraphs');
                $this->call('scrape:fangraphs', ['year' => $year]);

                $this->info('Scraping Savant');
                $this->call('scrape:savant', ['year' => $year]);

                $this->info('Computing and storing true skill ratings');
                $this->call('z:tru', ['year' => $year]);

                $this->info('Tabulating data and generating output files');
                $this->call('z:text', ['year' => $year]);
            }
        } else {
            $this->info('Scraping Fangraphs');
            $this->call('scrape:fangraphs', ['year' => $year]);

            $this->info('Scraping Savant');
            $this->call('scrape:savant', ['year' => $year]);

            $this->info('Computing and storing true skill ratings');
            $this->call('z:tru', ['year' => $year]);

            $this->info('Tabulating data and generating output files');
            $this->call('z:text', ['year' => $year]);
        }

        return 1;
    }
}
