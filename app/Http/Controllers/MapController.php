<?php

namespace App\Http\Controllers;

use App\Models\Map;
use Illuminate\Http\Request;

class MapController extends Controller
{
    public function render(int $id)
    {
        $map = Map::findOrFail($id);
        $path = storage_path("app/public/map_{$id}.png");

        if (!file_exists($path)) {
            $map->renderTopDownMapImage($path);
        }

        return response()->file($path, [
            'Content-Type' => 'image/png'
        ]);
    }

    public function show($id)
    {
        $args = [];
        $args['map'] = Map::find($id);

        return view('main.map.show', $args);
    }
}
