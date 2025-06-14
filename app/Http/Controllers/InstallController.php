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
use Illuminate\Validation\Rule;
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
            'category' => ['nullable', 'string', Rule::in(config('globals.demo_categories'))],
        ]);

        $install = Install::findOrFail($validated['install_id']);
        $wad = Wad::findOrFail($validated['wad_id']);

        if (!$install->hasValidExecutable() || !$wad->hasAllFiles()) {
            return response()->json(['status' => 'error', 'message' => 'Required files not found'], 404);
        }

        // Handle demo playback
        if (!empty($validated['demo_id'])) {
            $demo = Demo::findOrFail($validated['demo_id']);
            $lmpPath = Storage::disk('demos')->path($demo->lmp_file);

            if (!file_exists($lmpPath)) {
                return response()->json(['status' => 'error', 'message' => 'LMP file not found'], 404);
            }

            $install->run($wad, [], $demo);

            session()->flash('success', 'Demo Playback Launched');

            return redirect()->route('wad.show', $wad->id);
        }

        $options = [
            'skill' => $validated['skill'] ?? 4,
            'complevel' => $validated['complevel'] ?? $wad->getComplevel(),
        ];

        [$extraOptions, $map] = $this->prepareOptionsAndAttempt($validated, $wad);
        $options = array_merge([
            'skill' => $validated['skill'] ?? 4,
            'complevel' => $validated['complevel'] ?? $wad->getComplevel(),
        ], $extraOptions);

        $install->run($wad, $options);

        session()->flash('success', 'Attempt Launched');

        return redirect()->route('map.show', $map->id);
    }

    public function show($id)
    {
        $args = [];
        $args['install'] = Install::find($id);

        return view('main.install.show', $args);
    }

    public function viddump(Request $request)
    {
        $validated = $request->validate([
            'install_id' => 'required|integer|exists:installs,id',
            'demo_id' => 'required|integer|exists:demos,id',
        ]);

        $demo = Demo::findOrFail($validated['demo_id']);

        return $demo->makeViddump($validated['install_id']);
    }

    private function prepareOptionsAndAttempt(array $validated, Wad $wad): array
    {
        $options = [];
        $attemptInsert = ['wad_id' => $wad->id];
        $map = null;

        // Map & Warp
        if (!empty($validated['map_id'])) {
            $map = Map::find($validated['map_id']);
            if ($map && $map->warp_command) {
                $options['warp'] = $map->warp_command;
            }
            $attemptInsert['map_id'] = $map?->id;
        }

        // Recording
        if (!empty($validated['record'])) {
            $folder = $wad->foldername;
            $filename = now()->format('Y-m-d_H-i-s');
            Storage::disk('attempts')->makeDirectory($folder);

            $attemptInsert['lmp_file'] = "{$folder}/{$filename}.lmp";
            $path = Storage::disk('attempts')->path("{$folder}/{$filename}.lmp");

            Attempt::create($attemptInsert);
            $options['record'] = $path;
        }

        return [$options, $map];
    }


}
