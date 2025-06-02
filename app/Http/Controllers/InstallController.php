<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Demo;
use App\Models\Install;
use App\Models\Map;
use App\Models\Wad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
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

    public function play(Request $request)
    {
        $validated = $request->validate([
            'install_id' => 'required|integer|exists:installs,id',
            'wad_id' => 'required|integer|exists:wads,id',
            'map_id' => 'nullable|integer|exists:maps,id',
            'complevel' => 'nullable|integer',
            'skill' => 'nullable|integer|min:1|max:5',
            'record' => 'nullable|boolean',
            'demo_id' => 'nullable|integer|exists:demos,id',
        ]);

        $install = Install::findOrFail($validated['install_id']);
        $wad = Wad::findOrFail($validated['wad_id']);
        $complevel = $validated['complevel'] ?? $wad->complevel;
        $skill = $validated['skill'] ?? 4;
        $record = $validated['record'] ?? false;

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

        if (!empty($validated['demo_id'])) {
            $demo = Demo::findOrFail($validated['demo_id']);
            $lmpPath = Storage::disk('demos')->path($demo->lmp_file);

            if (!file_exists($lmpPath)) {
                return response()->json(['status' => 'error', 'message' => 'LMP file not found'], 404);
            }

            $command = sprintf(
                'start "" "%s" -iwad "%s" -file "%s" -playdemo "%s"',
                $exe,
                $iwad,
                $wadFile,
                $lmpPath
            );

            Log::info('Launching demo playback with command: ' . $command);

            pclose(popen("cmd /c $command", "r"));

            return response()->json([
                'status' => 'demo_playback_launched',
                'command' => $command,
            ]);
        }

        $warp = '';
        $attemptInsert = ['wad_id' => $validated['wad_id']];
        if (!empty($validated['map_id'])) {
            $map = Map::find($validated['map_id']);
            if ($map && $map->warp_command) {
                $warp = $map->warp_command;
            }
            $attemptInsert['map_id'] = $validated['map_id'];
        }

        $recordCmd = '';
        if ($record) {
            $folder = $wad->filename;
            $filename = now()->format('Y-m-d_H-i-s');
            Storage::disk('attempts')->makeDirectory($folder);
            $attemptInsert['lmp_file'] = "{$folder}/{$filename}.lmp";
            $path = Storage::disk('attempts')->path("{$folder}/{$filename}.lmp");

            Attempt::create($attemptInsert);

            $recordCmd = '-record "' . $path . '"';
        }

        $command = sprintf(
            'start "" "%s" -iwad "%s" -file "%s" -skill %d -complevel %d %s %s',
            $exe,
            $iwad,
            $wadFile,
            $skill,
            $complevel,
            $warp,
            $recordCmd
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
