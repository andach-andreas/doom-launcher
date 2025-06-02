<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class Wad extends Model
{
    public string $type;
    public int $numLumps;
    public int $dirOffset;

    public array $parsedLumps = [];
    public string $path;
    public array $skippedLumps = [];

    protected $fillable = [
        'filename',
        'filename_with_extension',
        'idgames_path',
        'complevel',
        'levels_count',
        'linedefs_count',
        'sidedefs_count',
        'things_count',
        'sectors_count',
        'vertexes_count',
        'iwad',

        'archive_maintainer',
        'update_to',
        'advanced_engine_needed',
        'primary_purpose',
        'title',
        'release_date',
        'author',
        'email_address',
        'other_files_by_author',
        'misc_author_info',
        'description',
        'credits',
        'new_levels',
        'sounds',
        'music',
        'graphics',
        'dehacked_patch',
        'demos',
        'other',
        'other_files_required',
        'game',
        'map',
        'single_player',
        'coop',
        'deathmatch',
        'other_game_styles',
        'difficulty_settings',
        'base',
        'build_time',
        'editors_used',
        'known_bugs',
        'may_not_run_with',
        'tested_with',
        'where_to_get_web',
        'where_to_get_ftp',
    ];

    public function attempts()
    {
        return $this->hasMany(Attempt::class);
    }

    public function compLevel()
    {
        return $this->belongsTo(CompLevel::class);
    }

    public function demosLink()
    {
        return $this->hasMany(Demo::class, 'wad_id');
    }

    public function installs(): BelongsToMany
    {
        return $this->belongsToMany(Install::class, 'link_installs_wads')
            ->withPivot(['is_compatible', 'notes'])
            ->withTimestamps();
    }

    public function lumps()
    {
        return $this->hasMany(Lump::class);
    }

    public function maps()
    {
        return $this->hasMany(Map::class, 'wad_id');
    }

    public function ports(): BelongsToMany
    {
        return $this->belongsToMany(Port::class, 'link_ports_wads');
    }

    public function types()
    {
        return $this->hasMany(Type::class);
    }

    public function allTypes()
    {
        return Type::whereNull('wad_id')
            ->orWhere('wad_id', $this->id)
            ->orderByRaw('wad_id IS NULL') // So overrides appear after for possible resolution
            ->get()
            ->unique('type'); // Keep overridden ones
    }

    public function download(): bool
    {
        $url = $this->buildDownloadUrl();
        $zipPath = $this->zip_path;

        if (File::exists($zipPath)) {
            return true;
        }

        File::makeDirectory(dirname($zipPath), 0755, true, true);

        try {
            $response = Http::timeout(30)->get($url);
            if (!$response->successful()) {
                return false;
            }

            File::put($zipPath, $response->body());

            $this->downloaded_at = now();
            $this->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
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
        $zipPath = $this->zip_path;
        $folderPath = $this->folder_path;

        if (!File::exists($zipPath)) {
            return false;
        }

        if (File::exists($folderPath)) {
            return true;
        }

        File::makeDirectory($folderPath, 0755, true, true);

        $zip = new \ZipArchive();
        if ($zip->open($zipPath) === true) {
            $zip->extractTo($folderPath);
            $zip->close();

            $this->extracted_at = now();
            $this->save();

            $this->insertIntoDatabase();

            return true;
        }

        return false;
    }

    public function getFolderPathAttribute()
    {
        return storage_path('/wads/'. $this->idgames_folder.'/'. $this->filename);
    }

    public function getIdgamesFolderAttribute(): string
    {
        $firstChar = strtolower(substr($this->filename, 0, 1));
        if (is_numeric($firstChar)) return '0-9';

        if ($firstChar >= 'a' && $firstChar <= 'c') return 'a-c';
        if ($firstChar >= 'd' && $firstChar <= 'f') return 'd-f';
        if ($firstChar >= 'g' && $firstChar <= 'i') return 'g-i';
        if ($firstChar >= 'j' && $firstChar <= 'l') return 'j-l';
        if ($firstChar >= 'm' && $firstChar <= 'o') return 'm-o';
        if ($firstChar >= 'p' && $firstChar <= 'r') return 'p-r';
        if ($firstChar >= 's' && $firstChar <= 'u') return 's-u';
        if ($firstChar >= 'v' && $firstChar <= 'z') return 'v-z';

        return '_'; // fallback
    }

    public function getIwadPathAttribute()
    {
        return Storage::disk('iwads')->path("{$this->iwad}.wad");
    }

    public function getTextFileContentsAttribute(): string
    {
        $folder = Storage::disk('wads')->path($this->idgames_path);
        $wadName = $this->filename;
        $primaryPath = $folder . DIRECTORY_SEPARATOR . $wadName . '.txt';

        if (file_exists($primaryPath)) {
            return file_get_contents($primaryPath) ?: '';
        }

        $txtFiles = glob($folder . DIRECTORY_SEPARATOR . '*.txt');

        if (!empty($txtFiles)) {
            return file_get_contents($txtFiles[0]) ?: '';
        }

        return '';
    }


    public function getWadPathAttribute()
    {
        return storage_path('wads/'. $this->idgames_path.'/'.$this->filename.'.wad');
    }

    public function getZipPathAttribute()
    {
        return storage_path('zips/'. $this->idgames_folder.'/'. $this->filename.'.zip');
    }

    public function insertIntoDatabase()
    {
        $this->path = $this->wad_path;
        $this->save();

        $this->insertIntoDatabaseLumps();
        $this->insertIntoDatabaseMaps();

        $this->load('maps');
        foreach ($this->maps as $map)
        {
            $map->insertIntoDatabase();
        }
    }

    protected function buildDownloadUrl(): string
    {
        $base = 'https://youfailit.net/pub/idgames/levels/';
        $folder = $this->iwad; // 'doom' or 'doom2'
        $sub = $this->idgames_folder;
        $file = $this->filename . '.zip';

        return "$base$folder/$sub/$file";
    }

    private function insertIntoDatabaseLumps(): void
    {
        $handle = fopen($this->path, 'rb');
        if (!$handle) {
            throw new \Exception("Unable to open WAD file: {$this->path}");
        }

        // Read the header to get the number of lumps and the directory offset
        $header = fread($handle, 12);
        [$this->type, $this->numLumps, $this->dirOffset] = array_values(unpack('a4type/VnumLumps/VdirOffset', $header));

        // Move to the directory offset to read the lump entries
        fseek($handle, $this->dirOffset);

        // Loop through each lump entry
        for ($i = 0; $i < $this->numLumps; $i++) {
            $entry = fread($handle, 16);
            if (strlen($entry) < 16) {
                $this->skippedLumps[] = $i;
                continue;
            }
            [$offset, $size, $name] = array_values(unpack('Voffset/Vsize/a8name', $entry));

            $data = null;
            if ($size > 0) {
                $curPos = ftell($handle);
                fseek($handle, $offset);
                $data = fread($handle, $size);
                fseek($handle, $curPos); // return to directory read position
            }

            $this->lumps()->create([
                'name' => $name,
                'offset' => $offset,
                'size' => $size,
                'data' => $data,
            ]);
        }

        fclose($handle);
    }

    private function insertIntoDatabaseMaps()
    {
        $validLumps = [
            'THINGS',
            'LINEDEFS',
            'SIDEDEFS',
            'VERTEXES',
            'SEGS',
            'SSECTORS',
            'NODES',
            'SECTORS',
        ];

        $isProcessingMap = false;
        $map = null;

        foreach ($this->lumps as $lump) {
            if ($lump->isMap()) {
                $map = Map::create([
                    'wad_id' => $this->id,
                    'name' => $lump->name,
                ]);
                $map->lumps()->attach($lump->id, ['is_header' => true]);

                $isProcessingMap = true;

                continue;
            }

            if ($isProcessingMap) {
                if (in_array($lump->name, $validLumps)) {
                    $map->lumps()->attach($lump->id, ['is_header' => false]);
                } else {
                    $isProcessingMap = false;
                }
            }
        }
    }
}
