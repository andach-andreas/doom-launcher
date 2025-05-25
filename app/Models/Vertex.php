<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Vertex extends Model
{
    protected $fillable = [
        'internal_id', 'map_id', 'x', 'y'
    ];

    public function map()
    {
        return $this->belongsTo(Map::class);
    }
}
