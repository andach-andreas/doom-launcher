<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class DownloadWads extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'wads:download';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $baseUrl = 'https://youfailit.net/pub/idgames/levels/';
        $mainDirs = ['doom', 'doom2'];
        $subDirs = ['0-9', 'a-c', 'd-f', 'g-i', 'j-l', 'm-o', 'p-r', 's-u', 'v-z'];

        foreach ($mainDirs as $main) {
            foreach ($subDirs as $sub) {
                $url = $baseUrl . "$main/$sub/";
                $this->info("Fetching from $url");
                $html = @file_get_contents($url);
                if (!$html) {
                    $this->warn("Failed to load $url");
                    continue;
                }

                libxml_use_internal_errors(true); // Suppress malformed HTML warnings
                $dom = new \DOMDocument();
                $dom->loadHTML($html);
                $links = $dom->getElementsByTagName('a');

                foreach ($links as $link) {
                    $href = $link->getAttribute('href');

                    if (preg_match('/\.(zip)$/i', $href)) {
                        $fileUrl = $url . $href;
                        $savePath = storage_path("zips/$main/$sub/$href");

                        if (!file_exists($savePath)) {
                            $this->info("Downloading $fileUrl");
                            @mkdir(dirname($savePath), 0777, true);
                            file_put_contents($savePath, file_get_contents($fileUrl));
                        }
                    }
                }
            }
        }

        $this->info("Download complete.");
    }
}
