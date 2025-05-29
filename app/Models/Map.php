<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Map extends Model
{
    public array $enemies = [];
    public int $enemiesCount = 0;
    public int $lumpIndex;
    private Lump $lumpLinedefs;
    private Lump $lumpSectors;
    private Lump $lumpSidedefs;
    private Lump $lumpThings;
    private Lump $lumpVertices;
    public array $weapons = [];
    public array $weaponsAvailable = [];
    public int $weaponsCount = 0;

    protected $fillable = [
        'id',
        'wad_id',
        'internal_name',
        'name',
        'image_path',
        'count_things',
        'count_linedefs',
        'count_sidedefs',
        'count_vertexes',
        'count_sectors',
    ];

    public function linedefs()
    {
        return $this->hasMany(Linedef::class);
    }

    public function lumps()
    {
        return $this->belongsToMany(Lump::class, 'link_lumps_maps')
            ->withPivot('is_header') // Include the is_header flag in the pivot table
            ->withTimestamps();
    }

    public function music()
    {
        return $this->belongsTo(Lump::class, 'music_lump_id');
    }

    public function nextLevel()
    {
        return $this->belongsTo(Map::class, 'next_level_map_id');
    }

    public function nextSecretLevel()
    {
        return $this->belongsTo(Map::class, 'next_secret_level_map_id');
    }

    public function sectors()
    {
        return $this->hasMany(Sector::class);
    }

    public function sidedefs()
    {
        return $this->hasMany(Sidedef::class);
    }

    public function things()
    {
        return $this->hasMany(Thing::class);
    }

    public function vertices()
    {
        return $this->hasMany(Vertex::class);
    }

    public function wad()
    {
        return $this->belongsTo(Wad::class);
    }

    public function getMapImageUrlAttribute(): string
    {
        $relativePath = $this->renderTopDownMapImage();
        return $relativePath ? asset("storage/$relativePath") : '';
    }

    public function getWarpCommandAttribute()
    {
        if (!$this->internal_name) {
            return '';
        }

        // Handle Doom 1 style: ExMx (e.g., E1M3)
        if (preg_match('/^(E)(\d)(M?)(\d)?$/i', $this->internal_name, $matches)) {
            $episode = $matches[2];
            $map = $matches[4] ?? '1'; // default to 1 if not present
            return "-warp $episode $map";
        }

        // Handle Doom 2 style: MAPxx (e.g., MAP02)
        if (preg_match('/^MAP(\d{2})$/i', $this->internal_name, $matches)) {
            $mapNumber = (int) $matches[1];
            return "-warp $mapNumber";
        }

        return '';
    }

    public function insertIntoDatabase()
    {
        $this->insertIntoDatabaseStats();
        $this->insertIntoDatabaseSectors($this->lumpSectors);
        $this->insertIntoDatabaseVertices($this->lumpVertices);
        $this->insertIntoDatabaseSidedefs($this->lumpSidedefs);
        $this->insertIntoDatabaseLinedefs($this->lumpLinedefs);
        $this->insertIntoDatabaseThings($this->lumpThings);
    }

    public function insertIntoDatabaseLinedefs(Lump $lump)
    {
        $data = $lump->data;
        $entrySize = 14;
        $num = intdiv(strlen($data), $entrySize);

        // Preload vertices and sidedefs into lookup arrays
        $vertices = $this->vertices()
            ->get(['id', 'internal_id'])
            ->keyBy('internal_id');

        $sidedefs = $this->sidedefs()
            ->get(['id', 'internal_id'])
            ->keyBy('internal_id');

        for ($i = 0; $i < $num; $i++) {
            $offset = $i * $entrySize;
            $entry = substr($data, $offset, $entrySize);

            $fields = unpack('vstart_vertex/vend_vertex/vflags/vspecial/vtag/vfront_sidedef/vback_sidedef', $entry);

            $this->linedefs()->create([
                'internal_id'       => $i,
                'start_vertex_id'   => $vertices[$fields['start_vertex']]->id ?? null,
                'end_vertex_id'     => $vertices[$fields['end_vertex']]->id ?? null,
                'flags'             => $fields['flags'],
                'special'           => $fields['special'],
                'tag'               => $fields['tag'],
                'front_sidedef_id'  => $sidedefs[$fields['front_sidedef']]->id ?? null,
                'back_sidedef_id'   => $sidedefs[$fields['back_sidedef']]->id ?? null,
            ]);
        }
    }

    public function insertIntoDatabaseSectors(Lump $lump)
    {
        $data = $lump->data;
        $entrySize = 26;
        $num = intdiv(strlen($data), $entrySize);

        for ($i = 0; $i < $num; $i++) {
            $offset = $i * $entrySize;
            $entry = substr($data, $offset, $entrySize);

            $fields = unpack('vfloor_height/vceiling_height/a8floor_texture/a8ceiling_texture/vlight_level/vtype/vtag', $entry);

            $this->sectors()->create([
                'internal_id'     => $i,
                'floor_height'    => $fields['floor_height'],
                'ceiling_height'  => $fields['ceiling_height'],
                'floor_texture'   => trim($fields['floor_texture']),
                'ceiling_texture' => trim($fields['ceiling_texture']),
                'light_level'     => $fields['light_level'],
                'type'            => $fields['type'],
                'tag'             => $fields['tag'],
            ]);
        }
    }

    public function insertIntoDatabaseSidedefs(Lump $lump)
    {
        $data = $lump->data;
        $entrySize = 30;
        $num = intdiv(strlen($data), $entrySize);

        // Preload sectors into lookup array
        $sectors = $this->sectors()
            ->get(['id', 'internal_id'])
            ->keyBy('internal_id');

        for ($i = 0; $i < $num; $i++) {
            $offset = $i * $entrySize;
            $entry = substr($data, $offset, $entrySize);

            $fields = unpack('vx_offset/vy_offset/a8upper_texture/a8lower_texture/a8middle_texture/vsector_internal_id', $entry);

            $this->sidedefs()->create([
                'internal_id'     => $i,
                'x_offset'        => $fields['x_offset'],
                'y_offset'        => $fields['y_offset'],
                'upper_texture'   => trim($fields['upper_texture']),
                'lower_texture'   => trim($fields['lower_texture']),
                'middle_texture'  => trim($fields['middle_texture']),
                'sector_id'       => $sectors[$fields['sector_internal_id']]->id ?? null,
            ]);
        }
    }

    public function insertIntoDatabaseStats()
    {
        foreach ($this->lumps as $lump) {
            switch ($lump->name) {
                case 'LINEDEFS':
                    $this->linedefs = $this->countLumpEntries($lump, 14);
                    $this->lumpLinedefs = $lump;
                    break;
                case 'SIDEDEFS':
                    $this->sidedefs = $this->countLumpEntries($lump, 30);
                    $this->lumpSidedefs = $lump;
                    break;
                case 'VERTEXES':
                    $this->vertexes = $this->countLumpEntries($lump, 4);
                    $this->lumpVertices = $lump;
                    break;
                case 'SEGS':
                    $this->segs = $this->countLumpEntries($lump, 12);
                    break;
                case 'SSECTORS':
                    $this->ssectors = $this->countLumpEntries($lump, 4);
                    break;
                case 'NODES':
                    $this->nodes = $this->countLumpEntries($lump, 28);
                    break;
                case 'SECTORS':
                    $this->sectors = $this->countLumpEntries($lump, 26);
                    $this->lumpSectors = $lump;
                    break;
                case 'THINGS' :
                    $this->things = $this->countLumpEntries($lump, 26);
                    $this->lumpThings = $lump;
                    break;
            }
        }

        $this->load('sectors');
        $this->secretSectors = $this->sectors()->secret()->count();

        $this->save();
    }

    protected function insertIntoDatabaseThings(Lump $lump): void
    {
        if (!$lump || !$lump->data || $lump->name !== 'THINGS') return;

        $data = $lump->data;
        $entrySize = 10;
        $numThings = intdiv(strlen($data), $entrySize);

        for ($i = 0; $i < $numThings; $i++) {
            $offset = $i * $entrySize;
            $thingData = substr($data, $offset, $entrySize);
            $unpacked = unpack('sx/sy/vangle/vtype/vflags', $thingData);

            Thing::create([
                'map_id'           => $this->id,
                'wad_id'           => $this->wad_id,
                'thing_type_id'    => 0,
                'x'                => $unpacked['x'],
                'y'                => $unpacked['y'],
                'angle'            => $unpacked['angle'],
                'type'             => $unpacked['type'],
                'flags'            => $unpacked['flags'],
                'flag_ambush'      => (bool)($unpacked['flags'] & 0x0004),
                'flag_single'      => (bool)($unpacked['flags'] & 0x0001),
                'flag_coop'        => (bool)($unpacked['flags'] & 0x0002),
                'flag_deathmatch'  => (bool)($unpacked['flags'] & 0x0008),
                'flag_multiplayer' => (bool)($unpacked['flags'] & 0x0010),
                'flag_friendly'    => (bool)($unpacked['flags'] & 0x0100),
                'flag_skill1'      => (bool)($unpacked['flags'] & 0x0001),
                'flag_skill2'      => (bool)($unpacked['flags'] & 0x0002),
                'flag_skill3'      => (bool)($unpacked['flags'] & 0x0004),
            ]);
        }
    }

    public function insertIntoDatabaseVertices(Lump $lump)
    {
        $data = $lump->data;
        $entrySize = 4;
        $num = intdiv(strlen($data), $entrySize);

        for ($i = 0; $i < $num; $i++) {
            $offset = $i * $entrySize;
            $entry = substr($data, $offset, $entrySize);

            $fields = unpack('sx/sy', $entry);

            $this->vertices()->create([
                'internal_id' => $i,
                'x' => $fields['x'],
                'y' => $fields['y'],
            ]);
        }
    }

    public function renderTopDownMapImage(): string
    {
        $subdir = $this->wad->idgames_folder; // e.g., 'a-c'
        $wadName = $this->wad->filename;
        $mapName = strtolower($this->name); // e.g., 'map01'

        $relativePath = "maps/$subdir/$wadName/{$mapName}.png";
        $outputPath = storage_path("app/public/$relativePath");

        if (file_exists($outputPath)) {
            return $relativePath;
        }

        $vertexLump = $this->lumps()->where('name', 'VERTEXES')->first();
        $linedefLump = $this->lumps()->where('name', 'LINEDEFS')->first();

        if (!$vertexLump || !$linedefLump) return '';

        $vertices = [];
        $entrySize = 4;
        $data = $vertexLump->data;
        $numEntries = intdiv(strlen($data), $entrySize);

        $minX = $minY = PHP_INT_MAX;
        $maxX = $maxY = PHP_INT_MIN;

        for ($i = 0; $i < $numEntries; $i++) {
            $offset = $i * $entrySize;
            $x = unpack('s', substr($data, $offset, 2))[1];
            $y = unpack('s', substr($data, $offset + 2, 2))[1];
            $vertices[$i] = ['x' => $x, 'y' => $y];
            $minX = min($minX, $x);
            $minY = min($minY, $y);
            $maxX = max($maxX, $x);
            $maxY = max($maxY, $y);
        }

        $width = 800;
        $height = 800;
        $scale = min($width / ($maxX - $minX + 1), $height / ($maxY - $minY + 1)) * 0.9;
        $offsetX = $width / 2 - ($minX + $maxX) * $scale / 2;
        $offsetY = $height / 2 + ($minY + $maxY) * $scale / 2;

        $img = imagecreatetruecolor($width, $height);
        $bg = imagecolorallocate($img, 0, 0, 0);
        $fg = imagecolorallocate($img, 255, 255, 255);
        imagefill($img, 0, 0, $bg);

        $entrySize = 14;
        $data = $linedefLump->data;
        $numEntries = intdiv(strlen($data), $entrySize);

        for ($i = 0; $i < $numEntries; $i++) {
            $offset = $i * $entrySize;
            $v1 = unpack('v', substr($data, $offset, 2))[1];
            $v2 = unpack('v', substr($data, $offset + 2, 2))[1];

            if (!isset($vertices[$v1]) || !isset($vertices[$v2])) continue;

            [$x1, $y1] = [$vertices[$v1]['x'], $vertices[$v1]['y']];
            [$x2, $y2] = [$vertices[$v2]['x'], $vertices[$v2]['y']];

            $sx1 = intval($x1 * $scale + $offsetX);
            $sy1 = intval(-$y1 * $scale + $offsetY);
            $sx2 = intval($x2 * $scale + $offsetX);
            $sy2 = intval(-$y2 * $scale + $offsetY);

            imageline($img, $sx1, $sy1, $sx2, $sy2, $fg);
        }

        @mkdir(dirname($outputPath), 0777, true);
        imagepng($img, $outputPath);
        imagedestroy($img);

        return $relativePath; // Return relative to public storage
    }

    protected function countLumpEntries(Lump $lump, int $entrySize): int
    {
        return intdiv(strlen($lump->data), $entrySize);
    }

    private function extractEnemies(): void
    {
        die('rewrite extractEnemies');
        foreach ($this->things as $thing) {
            $thingType = $this->thingsLookup[$thing->type]['type'] ?? '';

            if ($thingType === 'monster') {
                $this->enemies[] = $thing;
            }
        }

        $this->enemiesCount = count($this->enemies);
    }

    private function extractWeapons(): void
    {
        die('rewrite extractWeapons');
        foreach ($this->things as $thing) {
            $thingType = $this->thingsLookup[$thing->type]['type'] ?? '';

            if ($thingType === 'weapon') {
                $this->weapons[] = $thing;
            }
        }

        $this->weaponsCount = count($this->weapons);
    }

    private function extractWeaponsAvailable(): void
    {
        die('rewrite extractWeaponsAvailable');
        $weaponCount = [];

        foreach ($this->weapons as $weapon)
        {
            $weaponCount[$weapon->type] = $weaponCount[$weapon->type] ?? 0 + 1;
        }

        dd($this->name, $this->weapons);

        foreach ($this->things as $thingID => $thing) {
            $weaponName = $this->thingsLookup[$thing->type]['name'] ?? '';
            $thingType = $this->thingsLookup[$thing->type]['type'] ?? '';

            if ($thingType === 'weapon') {
                $this->weaponsAvailable[$weaponName] = $weaponCount[$thing->type] ?? 0;
            }
        }
    }

    public function parseAssociatedLumps(array $allLumps): void
    {
        // Lumps that define map data, in typical order
        $mapLumpNames = [
            'THINGS',
            'LINEDEFS',
            'SIDEDEFS',
            'VERTEXES',
            'SEGS',
            'SSECTORS',
            'NODES',
            'SECTORS',
            'REJECT',
            'BLOCKMAP',
            'BEHAVIOR', // for Hexen format maps
        ];

        $this->lumps = [];

        for ($i = $this->lumpIndex + 1; $i < count($allLumps); $i++) {
            $lump = $allLumps[$i];
            $name = strtoupper(trim($lump->name));

            if (in_array($name, $mapLumpNames, true)) {
                $this->lumps[$name] = $lump;
            } else {
                break;
            }
        }
    }
}
