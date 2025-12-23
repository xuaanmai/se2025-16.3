<?php

namespace App\Console\Commands;

use App\Models\Epic;
use Illuminate\Console\Command;

class EpicsRecalculateProgress extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'epics:recalculate-progress';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Recalculate the progress for all epics based on their tickets';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Recalculating progress for all epics...');

        $epics = Epic::all();
        $bar = $this->output->createProgressBar($epics->count());
        $bar->start();

        foreach ($epics as $epic) {
            $epic->recalculateProgress();
            $bar->advance();
        }

        $bar->finish();
        $this->info(PHP_EOL);
        $this->info('Successfully recalculated progress for all epics.');

        return Command::SUCCESS;
    }
}
