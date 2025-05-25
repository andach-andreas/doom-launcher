<?php

namespace App\Http\Controllers;

use App\Models\Wad;
use Illuminate\Http\Request;

class WadController extends Controller
{
    public function downloadAndExtract($id)
    {
        $wad = Wad::find($id);
        $wad->downloadAndExtract();

        return redirect()->route('wad.show', $id);
    }

    public function index()
    {
        $args = [];
        $args['wads'] = Wad::all();

        return view('main.wad.index', $args);
    }

    public function insertIntoDatabase($id)
    {
        $wad = Wad::find($id);
        $wad->insertIntoDatabase();

        return redirect()->route('wad.show', $id);
    }

    public function show($id)
    {
        $args = [];
        $args['wad'] = Wad::find($id);

        return view('main.wad.show', $args);
    }

    public function text($id)
    {
        $args = [];
        $args['wad'] = Wad::find($id);

        return view('main.wad.text', $args);
    }
}
