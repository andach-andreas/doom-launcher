<?php

namespace App\Http\Controllers;

use App\Models\Demo;
use App\Models\Wad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

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
