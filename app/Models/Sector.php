<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sector extends Model
{
    protected $fillable = [
        'internal_id', 'map_id', 'floor_height', 'ceiling_height',
        'floor_texture', 'ceiling_texture', 'light_level',
        'type', 'tag'
    ];

    public function map()
    {
        return $this->belongsTo(Map::class);
    }

    public function isSecret(): bool
    {
        return in_array($this->type, [9, 11]);
    }

    public function scopeSecret($query)
    {
        return $query->whereIn('type', [9, 11]);
    }
}
