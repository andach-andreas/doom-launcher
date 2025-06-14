<?php

namespace App\Models;

use Andach\DoomWadAnalysis\Demo as ApiDemo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class Demo extends Model
{
    protected $table = 'demos';

    protected $primaryKey = 'id';
    public $incrementing = false; // Since the primary key is not auto-incrementing
    protected $keyType = 'int';

    protected $fillable = [
        'id',
        'map_id',
        'wad_id',
        'category',
        'player',
        'engine',
        'note',
        'time',
        'lmp_file',
        'lmp_url_zip',
        'youtube_id',
        'youtube_link',
        'comment',
        'version',
        'skill_number',
        'mode_number',
        'respawn',
        'fast',
        'nomonsters',
        'number_of_players',
        'tics',
        'seconds',
    ];

    public function map()
    {
        return $this->belongsTo(Map::class);
    }

    public function wad()
    {
        return $this->belongsTo(Wad::class);
    }

    public function getViddumpFullPathAttribute(): string
    {
        if (!$this->viddump_path) {
            return '';
        }

        return str_replace('/', '\\', storage_path(ltrim($this->viddump_path, '/storage/')));
    }

    public function getViddumpPathAttribute(): string
    {
        if (!$this->lmp_file) {
            return '';
        }

        $mp4Path = str_replace('.lmp', '.mp4', $this->lmp_file);

        return Storage::disk('demos')->exists($mp4Path)
            ? Storage::disk('demos')->url($mp4Path)
            : '';
    }

    public function makeViddump(int $installID): string
    {
        if ($this->viddump_path)
        {
            return $this->viddump_path;
        }

        $install = Install::findOrFail($installID);
        $wad = $this->wad;

        $exe = $install->executable_path;
        $iwad = $wad->iwad_path;
        $wadFile = $wad->wad_path;
        $lmpPath = Storage::disk('demos')->path($this->lmp_file);
        $vidPath = str_replace('.lmp', '.mp4', $lmpPath);

        if (!file_exists($exe) || !file_exists($iwad) || !file_exists($wadFile) || !file_exists($lmpPath)) {
            throw new \Exception("Required file(s) missing for demo ID {$this->id}");
        }

        $complevel = $this->wad->complevel
            ?? match ($this->wad->iwad) {
                'doom' => 2,
                'doom2' => 4,
                default => null,
            };


        $command = sprintf(
            'start /min "" "%s" -iwad "%s" -file "%s" -timedemo "%s" -viddump "%s" -complevel %d',
            $exe,
            $iwad,
            $wadFile,
            $lmpPath,
            $vidPath,
            $complevel
        );

        // Run the process synchronously and capture output
        exec($command, $output, $exitCode);

        if ($exitCode !== 0) {
            throw new \Exception("Viddump failed for demo ID {$this->id} with exit code $exitCode");
        }

        return $vidPath;
    }

    public function timeToSeconds(): float
    {
        if (preg_match('/^(\d+):(\d+(?:\.\d+)?)/', $this->time, $m)) {
            return ((int) $m[1]) * 60 + (float) $m[2];
        }

        return (float) $this->time;
    }


}
