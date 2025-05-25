<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Thing extends Model
{
    protected $fillable = [
        'map_id',
        'thing_type_id',
        'wad_id',
        'angle',
        'flags',
        'type',
        'x',
        'y',
        'flag_ambush',
        'flag_coop',
        'flag_deathmatch',
        'flag_friendly',
        'flag_multiplayer',
        'flag_single',
        'flag_skill1',
        'flag_skill2',
        'flag_skill3',
    ];

    public function map()
    {
        return $this->belongsTo(Map::class, 'level_id');
    }

    public function thingType()
    {
        return $this->belongsTo(ThingType::class);
    }

    public function wad()
    {
        return $this->belongsTo(Wad::class);
    }
}
