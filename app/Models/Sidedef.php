<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sidedef extends Model
{
    protected $fillable = [
        'internal_id', 'map_id', 'x_offset', 'y_offset',
        'upper_texture', 'lower_texture', 'middle_texture', 'sector_id'
    ];

    public function map()
    {
        return $this->belongsTo(Map::class);
    }

    public function sector()
    {
        return $this->belongsTo(Sector::class, 'sector_id');
    }
}
