<?php

namespace App\Console\Commands;

use App\Models\Port;
use Illuminate\Console\Command;

class SyncPortReleases extends Command
{
    protected $signature = 'port:sync';
    protected $description = 'Sync available releases from all known ports';

    public function handle()
    {
        $ports = Port::all();

        foreach ($ports as $port) {
            $this->info("Syncing releases for {$port->name}...");
            $port->syncReleases();
        }

        $this->info('All ports synced.');
        return 0;
    }
}
