<?php

namespace App\Console\Commands;

use App\Models\Wad;
use Illuminate\Console\Command;

class SyncWads extends Command
{
    protected $signature = 'wads:sync';
    protected $description = 'Sync WAD filenames from remote index without downloading them';

    public function handle()
    {
        $baseUrl = 'https://youfailit.net/pub/idgames/levels/';
        $mainDirs = ['doom', 'doom2'];
        $subDirs = ['0-9', 'a-c', 'd-f', 'g-i', 'j-l', 'm-o', 'p-r', 's-u', 'v-z'];

        $totalInserted = 0;

        foreach ($mainDirs as $main) {
            foreach ($subDirs as $sub) {
                $url = $baseUrl . "$main/$sub/";
                $this->info("Checking $url");

                $html = @file_get_contents($url);
                if (!$html) {
                    $this->warn("Failed to load $url");
                    continue;
                }

                libxml_use_internal_errors(true);
                $dom = new \DOMDocument();
                $dom->loadHTML($html);
                $links = $dom->getElementsByTagName('a');

                $toInsert = [];

                foreach ($links as $link) {
                    $href = $link->getAttribute('href');

                    if (str_ends_with(strtolower($href), '.zip')) {
                        $filename = basename($href, '.zip');
                        $toInsert[] = [
                            'filename' => $filename,
                            'iwad'     => $main,
                        ];
                    }
                }

                $existing = Wad::whereIn('filename', array_column($toInsert, 'filename'))
                    ->pluck('filename')
                    ->toArray();

                $new = array_filter($toInsert, fn($row) => !in_array($row['filename'], $existing));

                if (!empty($new)) {
                    Wad::upsert($new, ['filename'], ['iwad']);
                    $count = count($new);
                    $this->info("Inserted $count new WADs from $main/$sub");
                    $totalInserted += $count;
                } else {
                    $this->info("No new WADs from $main/$sub");
                }
            }
        }

        $this->info("Total new WADs inserted: $totalInserted");
    }
}
