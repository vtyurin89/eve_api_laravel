<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\CreateEntries\CreateEntries;

class CreateEntriesCommands extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'create:entries';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create entries (constellations, regions, stargates, stations, systems) using the Eve online API';

    /**
     * Execute the console command.
     */
    public function handle(CreateEntries $createEntries)
    {
        $createEntries->createConstellations();
    }
}
