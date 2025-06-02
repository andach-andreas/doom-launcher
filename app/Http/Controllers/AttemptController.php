<?php

namespace App\Http\Controllers;

use Andach\DoomWadAnalysis\Demo as ApiDemo;
use App\Models\Attempt;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use App\Models\Wad;
use Illuminate\Support\Str;

class AttemptController extends Controller
{
    public function sync(int $wadID)
    {
        $wad = Wad::findOrFail($wadID);
        $directory = $wad->filename;

        if (!Storage::disk('attempts')->exists($directory)) {
            return response()->json(['status' => 'error', 'message' => 'Directory not found'], 404);
        }

        $files = collect(Storage::disk('attempts')->allFiles($directory))
            ->filter(fn($file) => Str::endsWith($file, '.lmp'))
            ->values()
            ->all();

        foreach ($files as $file) {
            $fullPath = Storage::disk('attempts')->path($file);
            $analysis = new ApiDemo($fullPath);
            $analysis->lmpStats();

            $attempt = Attempt::updateOrCreate(
                ['lmp_file' => $file],
                [
                    'wad_id' => $wadID,
                    'category' => '?',
                    'time' => $analysis->stats['seconds'],
                    'version' => $analysis->stats['version'],
                    'skill_number' => $analysis->stats['skill_number'],
                    'mode_number' => $analysis->stats['mode_number'],
                    'respawn' => $analysis->stats['respawn'],
                    'fast' => $analysis->stats['fast'],
                    'nomonsters' => $analysis->stats['nomonsters'],
                    'number_of_players' => $analysis->stats['number_of_players'],
                    'tics' => $analysis->stats['tics'],
                    'seconds' => $analysis->stats['seconds'],
                ]
            );

            $attempt->extractAnalaysisAndLevelstat($wad);
        }

        return redirect()->route('wad.show', $wad->id);
    }

}
