<?php

namespace App\Http\Controllers;

use App\Models\Port;
use Illuminate\Http\Request;

class PortController extends Controller
{
    public function index()
    {
        $args = [];
        $args['ports'] = Port::all();

        return view('main.port.index', $args);
    }

    public function show($id)
    {
        $args = [];
        $args['port'] = Port::find($id);

        return view('main.port.show', $args);
    }
}
