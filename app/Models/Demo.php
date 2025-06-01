<?php

namespace App\Models;

use Andach\DoomWadAnalysis\Demo as ApiDemo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Http;
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

}
