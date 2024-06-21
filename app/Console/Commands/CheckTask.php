<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Console\Commands\Tasks\UpdateDangerRatings;

class CheckTask extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:check-task';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ckecking task in development';

    /**
     * Execute the console command.
     */
    public function handle(UpdateDangerRatings $updateDangerRatings)
    {
        $updateDangerRatings->execute();
    }
}
