<?php

namespace App\Http\Controllers;

use App\Models\Demo;
use App\Models\Wad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class DSDAController extends Controller
{
    public function sync(int $wadID)
    {
        $wad = Wad::find($wadID);

        foreach ($wad->maps as $map)
        {
            $url = "https://doomwads.andach.co.uk/api/v1/map/{$wad->filename}/{$map->internal_name}";
            $response = Http::get($url);
            if (!$response->successful()) {
                dd($url, $response);
            }

            $json = $response->json();
            $data = $json['data'] ?? [];

            if (!empty($data['demos'])) {
                foreach ($data['demos'] as $demoData) {
                    // Make sure 'id' is present
                    if (!isset($demoData['id'])) {
                        Log::warning('Demo entry missing ID, skipping.', $demoData);
                        continue;
                    }

                    $demoData['wad_id'] = $wad->id;
                    $demoData['map_id'] = $map->id;

                    if (!empty($demoData['lmp_file'])) {
                        $remoteUrl = 'https://doomwads.andach.co.uk/demos/' . trim($demoData['lmp_file'], '/');
                        $relativePath = str_replace(['\\', '/'], DIRECTORY_SEPARATOR, $demoData['lmp_file']);
                        $fullPath = Storage::disk('demos')->path($relativePath);

                        // Ensure the folder exists
                        $folder = dirname($relativePath);
                        Storage::disk('demos')->makeDirectory($folder);

                        try {
                            $response = Http::get($remoteUrl);
                            if ($response->successful()) {
                                Storage::disk('demos')->put($relativePath, $response->body());
                            } else {
                                Log::error("Failed to download LMP file: {$remoteUrl}", ['status' => $response->status()]);
                            }
                        } catch (\Exception $e) {
                            Log::error("Exception downloading LMP file: {$remoteUrl}", ['error' => $e->getMessage()]);
                        }
                    }

                    // Upsert the demo record using ID
                    Demo::updateOrCreate(
                        ['id' => $demoData['id']],
                        $demoData
                    );
                }
            }
        }

        session()->flash('success', 'Demos Synchronised');

        return redirect()->route('wad.show', $wad->id);
    }
}
