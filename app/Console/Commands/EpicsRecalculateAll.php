<?php

namespace App\Console\Commands;

use App\Models\Epic;
use Illuminate\Console\Command;

class EpicsRecalculateAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'epics:recalculate-all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate progress and dates for all epics based on their tickets';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Recalculating progress and dates for all epics...');

        $epics = Epic::all();
        $bar = $this->output->createProgressBar($epics->count());
        $bar->start();

        foreach ($epics as $epic) {
            $epic->recalculateProgress();
            $epic->recalculateDates();
            $bar->advance();
        }

        $bar->finish();
        $this->info(PHP_EOL);
        $this->info('Successfully recalculated data for all epics.');

        return Command::SUCCESS;
    }
}
