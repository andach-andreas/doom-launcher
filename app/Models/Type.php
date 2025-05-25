<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Type extends Model
{
    protected $fillable = [
        'type',
        'wad_id',
        'name',
        'category',
        'port',
        'spawn_health',
    ];

    public function wad()
    {
        return $this->belongsTo(Wad::class);
    }
}
