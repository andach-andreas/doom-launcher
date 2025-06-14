<?php

namespace App\Models;

use App\Models\Map;
use App\Models\Wad;
use Illuminate\Database\Eloquent\Model;
use Symfony\Component\Process\Process;
use Illuminate\Support\Facades\Storage;

class Attempt extends Model
{
    protected $fillable = [
        'id',
        'map_id',
        'map_completed_id',
        'wad_id',
        'category',
        'time',
        'lmp_file',
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

    public function mapCompleted()
    {
        return $this->belongsTo(Map::class, 'map_completed_id');
    }

    public function wad()
    {
        return $this->belongsTo(Wad::class);
    }

    public function descriptionFileContent()
    {
        return file_get_contents($this->descriptionFileFullPath());
    }

    public function descriptionFileFullPath()
    {
        return Storage::disk('attempts')->path($this->descriptionFilePath());
    }

    public function descriptionFilePath()
    {
        return preg_replace('/\.lmp$/i', '_description.txt', $this->lmp_file);
    }

    public function descriptionFileUpdate(string $content)
    {
        file_put_contents($this->descriptionFileFullPath(), $content);
    }

    public function determineCategory(): string
    {
        $data = $this->parseAnalysisData();

        if ($data['stroller'] ?? false) {
            return 'Stroller';
        }

        if ($data['nomonsters'] ?? false) {
            if ($data['100s'] ?? false) {
                return 'NoMo 100S';
            }

            return 'NoMo';
        }

        if (($data['skill'] ?? false) == 5)
        {
            if ($data['100s'] ?? false) {
                return 'NM 100S';
            }

            return 'NM Speed';
        }

        if (($data['skill'] ?? false) == 4)
        {
            if ($data['pacifist'] ?? false) {
                return 'Pacifist';
            }

            if ($data['fast'] ?? false) {
                return 'UV Fast';
            }

            if ($data['Respawn'] ?? false) {
                return 'UV Respawn';
            }

            if ($data['tyson'] ?? false) {
                return 'UV Tyson';
            }

            if (($data['100k'] ?? false) && ($data['100s'] ?? false)) {
                return 'UV Max';
            }

            return 'UV Speed';
        }

        return 'Other';
    }

    public function extractAnalaysisAndLevelstat($wad): void
    {
        $install = Install::find(68);
        $fullPath = Storage::disk('attempts')->path($this->lmp_file);

        $exe = $install->executable_path;
        $iwad = $wad->iwad_path;
        $wadFile = $wad->wad_path;

        // Set working directory to LMP file's directory
        $workingDir = dirname($fullPath);
        $baseName = pathinfo($fullPath, PATHINFO_FILENAME);

        $analysisFile = $workingDir . DIRECTORY_SEPARATOR . 'analysis.txt';
        $exportTextFile = $workingDir . DIRECTORY_SEPARATOR . $baseName . '.txt';
        $levelstatFile = $workingDir . DIRECTORY_SEPARATOR . 'levelstat.txt';

        $renamedAnalysis = $workingDir . DIRECTORY_SEPARATOR . $baseName . '_analysis.txt';
        $renamedExportText = $workingDir . DIRECTORY_SEPARATOR . $baseName . '_description.txt';
        $renamedLevelstat = $workingDir . DIRECTORY_SEPARATOR . $baseName . '_levelstat.txt';

        if (file_exists($renamedAnalysis) && file_exists($renamedExportText)) {
            $this->updateCategoryFromAnalysis();
            $this->updateMapFromLevelstat();

            return;
        }

        // Build DSDA-Doom command
        $command = [
            $exe,
            '-iwad', $iwad,
            '-file', $wadFile,
            '-fastdemo', $fullPath,
            '-nosound',
            '-nomusic',
            '-export_text_file',
            '-analysis',
            '-levelstat',
        ];

        $process = new Process($command, $workingDir);
        $process->run();

        // Rename output files if they exist

        if (file_exists($analysisFile)) {
            rename($analysisFile, $renamedAnalysis);
        }

        if (file_exists($exportTextFile)) {
            rename($exportTextFile, $renamedExportText);
        }

        if (file_exists($levelstatFile)) {
            rename($levelstatFile, $renamedLevelstat);
        }

        $this->updateCategoryFromAnalysis();
        $this->updateMapFromLevelstat();
    }

    public function parseAnalysisData(): ?array
    {
        $fullPath = Storage::disk('attempts')->path($this->lmp_file);
        $dir = dirname($fullPath);
        $baseName = pathinfo($fullPath, PATHINFO_FILENAME);
        $analysisPath = $dir . DIRECTORY_SEPARATOR . $baseName . '_analysis.txt';

        if (!file_exists($analysisPath)) {
            return null;
        }

        $lines = file($analysisPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        $data = [];

        foreach ($lines as $line) {
            if (preg_match('/^([a-zA-Z0-9_]+)\s+(.*)$/', trim($line), $matches)) {
                $key = $matches[1];
                $value = is_numeric($matches[2]) ? +$matches[2] : $matches[2];
                $data[$key] = $value;
            }
        }

        return $data;
    }

    public function updateCategoryFromAnalysis(): void
    {
        $this->category = $this->determineCategory();
        $this->save();
    }

    public function updateMapFromLevelstat()
    {
        $fullPath = Storage::disk('attempts')->path($this->lmp_file);
        $dir = dirname($fullPath);
        $baseName = pathinfo($fullPath, PATHINFO_FILENAME);
        $levelstatPath = $dir . DIRECTORY_SEPARATOR . $baseName . '_levelstat.txt';

        if (!file_exists($levelstatPath)) {
            return null;
        }

        $lines = file($levelstatPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        $levelLines = array_filter($lines, fn($line) =>
        preg_match('/^(E\dM\d|MAP\d\d)\s+-/', $line)
        );

        if (count($levelLines) === 1) {
            preg_match('/^(E\dM\d|MAP\d\d)/', reset($levelLines), $matches);
            $mapInternalName = $matches[1];

            $map = Map::where('internal_name', $mapInternalName)
                ->where('wad_id', $this->wad_id)
                ->first();

            if ($map) {
                $this->map_completed_id = $map->id;
                $this->save();
            }
        }
    }

}
