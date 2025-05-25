<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Linedef extends Model
{
    protected $fillable = [
        'internal_id', 'map_id', 'start_vertex_id', 'end_vertex_id',
        'flags', 'special', 'tag', 'front_sidedef_id', 'back_sidedef_id'
    ];

    public function backSidedef()
    {
        return $this->belongsTo(Sidedef::class, 'back_sidedef_id');
    }

    public function endVertex()
    {
        return $this->belongsTo(Vertex::class, 'end_vertex_id');
    }

    public function frontSidedef()
    {
        return $this->belongsTo(Sidedef::class, 'front_sidedef_id');
    }

    public function map()
    {
        return $this->belongsTo(Map::class);
    }

    public function startVertex()
    {
        return $this->belongsTo(Vertex::class, 'start_vertex_id');
    }
}
