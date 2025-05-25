<?php

namespace App\Console\Commands;

use App\Models\Port;
use Illuminate\Console\Command;

class DownloadPort extends Command
{
    protected $signature = 'port:download';
    protected $description = 'Download specific versions of a given port';

    public function handle()
    {
        $name = $this->ask('Enter the name of the port');
        $port = Port::where('name', $name)->first();

        if (!$port) {
            $this->error('Port not found.');
            return 1;
        }

        $this->info("Fetching releases for {$port->name}...");

        $port->syncReleases();

        $versions = $port->installs()
            ->orderByDesc('version')
            ->take(10)
            ->get();

        if ($versions->isEmpty()) {
            $this->warn('No versions found for this port.');
            return 0;
        }

        $choices = $versions->map(fn($i) =>
            $i->version . ($i->downloaded_at ? ' (downloaded)' : '')
        )->toArray();

        $selected = $this->choice(
            'Select version(s) to install (repeat to select more)',
            $choices,
            null,
            null,
            true
        );

        foreach ($selected as $entry) {
            $version = trim(str_replace(' (downloaded)', '', $entry));
            $install = $port->installs()->where('version', $version)->first();

            if (!$install) {
                $this->error("Install record not found for version {$version}");
                continue;
            }

            if ($install->downloaded_at) {
                $this->line("Version {$version} already downloaded.");
                continue;
            }

            $this->info("Downloading and extracting {$version}...");
            $install->downloadAndExtract();
            $this->info("Done.");
        }

        return 0;
    }
}
