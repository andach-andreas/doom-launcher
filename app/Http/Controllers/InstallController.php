<?php

namespace App\Http\Controllers;

use App\Models\Install;
use App\Models\Map;
use App\Models\Wad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Process;

class InstallController extends Controller
{
    public function extract($id)
    {
        $install = Install::findOrFail($id);

        if (!$install->downloaded_at) {
            if (!$install->download()) {
                session()->flash('error', 'Download failed.');
            }
        }

        if ($install->extract()) {
            session()->flash('success', 'Extraction successful.');
        }

        return redirect()->route('install.show', $id);
    }

    public function play(int $id, int $wadID, int $mapID = 0)
    {
        $install = Install::findOrFail($id);
        $wad = Wad::findOrFail($wadID);

        $exe = $install->executable_path;
        $iwad = $wad->iwad_path;
        $wadFile = $wad->wad_path;

        if (!file_exists($exe)) {
            return response()->json(['status' => 'error', 'message' => 'Executable not found'], 404);
        }

        if (!file_exists($iwad)) {
            return response()->json(['status' => 'error', 'message' => 'IWAD file not found'], 404);
        }

        if (!file_exists($wadFile)) {
            return response()->json(['status' => 'error', 'message' => 'WAD file not found'], 404);
        }

        $warp = '';

        if ($mapID > 0) {
            $map = Map::find($mapID);
            if ($map && $map->internal_name) {
                $warp = '-warp ' . preg_replace('/^(MAP|E)(\d)(M?)(\d)?$/i', '$2 $4', $map->internal_name);
            }
        }

        $command = sprintf(
            'start "" "%s" -iwad "%s" -file "%s" %s',
            $exe,
            $iwad,
            $wadFile,
            trim($warp)
        );

        Log::info('Launching WAD with command: ' . $command);

        pclose(popen("cmd /c $command", "r"));

        return response()->json([
            'status' => 'launched',
            'command' => $command,
        ]);
    }

    public function show($id)
    {
        $args = [];
        $args['install'] = Install::find($id);

        return view('main.install.show', $args);
    }
}
