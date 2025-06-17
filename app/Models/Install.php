<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RecursiveIteratorIterator;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;

class Install extends Model
{
    protected $fillable = ['port_id', 'version', 'path', 'download_url',
        'downloaded_at', 'extracted_at'];

    public function port(): BelongsTo
    {
        return $this->belongsTo(Port::class);
    }

    public function wads(): BelongsToMany
    {
        return $this->belongsToMany(Wad::class, 'link_installs_wads')
            ->withPivot(['is_compatible', 'notes'])
            ->withTimestamps();
    }

    public function buildLaunchCommand(Wad $wad, array $options = [], Demo $demo = null): string
    {
        $cmd = [
            'start ""',
            '"' . $this->executable_path . '"',
            '-iwad "' . $wad->iwad_path . '"',
            '-file "' . $wad->wad_path . '"',
        ];

        if ($demo) {
            $lmpPath = Storage::disk('demos')->path($demo->lmp_file);
            $cmd[] = '-playdemo "' . $lmpPath . '"';
        } else {
            if (isset($options['skill'])) {
                $cmd[] = '-skill ' . (int)$options['skill'];
            }
            if (isset($options['complevel'])) {
                $cmd[] = '-complevel ' . (int)$options['complevel'];
            }
            if (!empty($options['warp'])) {
                $cmd[] = '-warp ' . $options['warp'];
            }
            if (!empty($options['record'])) {
                $cmd[] = '-record "' . $options['record'] . '"';
            }
            if (!empty($options['runflag']))
            {
                $cmd[] = '-' . $options['runflag'];
            }
        }

        return implode(' ', $cmd);
    }


    public function download(): bool
    {
        if (!$this->download_url) {
            return false;
        }

        $tmpPath = $this->zip_path;
        $response = Http::get($this->download_url);

        if (!$response->ok()) {
            return false;
        }

        Storage::disk('installs')->put($tmpPath, $response->body());

        $this->downloaded_at = now();
        $this->save();

        return true;
    }

    public function downloadAndExtract(): bool
    {
        if (!$this->download()) {
            return false;
        }

        return $this->extract();
    }

    public function extract(): bool
    {
        $tmpPath = storage_path('installs/' . $this->zip_path);
        $extractPath = storage_path('installs/' . $this->folder_path);

        if (!file_exists($tmpPath)) {
            return false;
        }

        $zip = new \ZipArchive();
        if ($zip->open($tmpPath) === true) {
            $zip->extractTo($extractPath);
            $zip->close();

            $this->extracted_at = now();
            $this->save();
            return true;
        }

        return false;
    }

    public function executablePath(): Attribute
    {
        return Attribute::get(function () {
            $installRoot = Storage::disk('installs')->path("{$this->port->slug}/{$this->version}");

            if (!is_dir($installRoot)) {
                return null;
            }

            $iterator = new RecursiveIteratorIterator(
                new RecursiveDirectoryIterator($installRoot, RecursiveDirectoryIterator::SKIP_DOTS)
            );

            foreach ($iterator as $file) {
                if ($file->isFile() && str_ends_with(strtolower($file->getFilename()), '.exe')) {
                    return $file->getPathname();
                }
            }

            return null;
        });
    }

    public function getFolderPathAttribute(): string
    {
        return $this->port->slug . '/' . $this->version;
    }

    public function getZipPathAttribute(): string
    {
        return 'zip/' . $this->port->slug . '/' . $this->version . '.zip';
    }

    public function hasValidExecutable(): bool
    {
        return file_exists($this->executable_path);
    }

    public function run(Wad $wad, array $options, Demo $demo = null)
    {
        $command = $this->buildLaunchCommand($wad, $options, $demo);
        Log::info('Launching demo playback: ' . $command);
        pclose(popen("cmd /c $command", "r"));

        return response()->json(['status' => 'demo_playback_launched', 'command' => $command]);
    }

    public function scopeInstalled($query)
    {
        return $query->whereNotNull('extracted_at');
    }
}
