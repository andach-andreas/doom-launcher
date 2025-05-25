<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class UnzipWads extends Command
{
    protected $signature = 'wads:unzip';
    protected $description = 'Unzip WAD files into appropriate directories';

    public function handle()
    {
        $basePath = storage_path('levels');
        $targetBasePath = storage_path('wads'); // Set the correct path for unzipped files
        $mainDirs = ['doom', 'doom2'];
        $subDirs = ['0-9', 'a-c', 'd-f', 'g-i', 'j-l', 'm-o', 'p-r', 's-u', 'v-z'];

        foreach ($mainDirs as $main) {
            foreach ($subDirs as $sub) {
                $dirPath = "$basePath/$main/$sub";
                if (!is_dir($dirPath)) {
                    $this->warn("Directory does not exist: $dirPath");
                    continue;
                }

                $files = scandir($dirPath);
                foreach ($files as $file) {
                    if ($this->isValidZip($file, $dirPath)) {
                        $this->unzipFile($file, $dirPath, $main, $sub, $targetBasePath);
                    } else {
                        $this->info("Skipping invalid or already extracted file: $file");
                    }
                }
            }
        }

        $this->info('All zip files processed.');
    }

    /**
     * Check if a file is a valid ZIP file that hasn't been unzipped yet.
     *
     * @param string $file
     * @param string $dirPath
     * @return bool
     */
    protected function isValidZip(string $file, string $dirPath): bool
    {
        // Log the files being checked
        $this->info("Checking file: $file");

        if (preg_match('/\.zip$/i', $file)) {
            $unzipDirectory = "$dirPath/" . pathinfo($file, PATHINFO_FILENAME);
            if (!is_dir($unzipDirectory)) {
                $this->info("Valid zip file: $file (No directory exists for extraction)");
                return true;
            } else {
                $this->info("Skipping zip file: $file (Directory already exists)");
            }
        }

        return false;
    }

    /**
     * Unzip the file to its respective directory.
     *
     * @param string $file
     * @param string $dirPath
     * @param string $main
     * @param string $sub
     * @param string $targetBasePath
     * @return void
     */
    protected function unzipFile(string $file, string $dirPath, string $main, string $sub, string $targetBasePath): void
    {
        $zipFilePath = "$dirPath/$file";
        $unzipDirectory = "$targetBasePath/$main/$sub/" . pathinfo($file, PATHINFO_FILENAME);

        $this->info("Unzipping file: $zipFilePath to $unzipDirectory");

        if (!is_dir($unzipDirectory)) {
            mkdir($unzipDirectory, 0777, true);
        }

        $zip = new ZipArchive();

        if ($zip->open($zipFilePath) === true) {
            $this->info("Unzipping: $zipFilePath");

            $zip->extractTo($unzipDirectory);
            $zip->close();

            $this->info("Successfully unzipped: $zipFilePath");
        } else {
            $this->warn("Failed to open zip: $zipFilePath");
        }
    }
}
