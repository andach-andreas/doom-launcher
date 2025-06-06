<?php

namespace App\Http\Controllers;

use App\Models\Map;
use App\Models\Wad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class WadController extends Controller
{
    public function downloadAndExtract(Request $request)
    {
        $wadName = $request->post('wadName');
        $response = Http::get("https://doomwads.andach.co.uk/api/v1/wad/{$wadName}");
        if (!$response->successful()) {
            abort(404, "Wad not found or API request failed.");
        }

        $json = $response->json();

        if ($json['status'] !== 'success' || !isset($json['data']['wad'])) {
            abort(404, "Invalid API response.");
        }

        $wadData = $json['data']['wad'];

        // Find or create Wad model by id or filename (depending on your logic)
        $wad = Wad::updateOrCreate(
            ['id' => $wadData['id']],
            $wadData
        );

        // Import maps if present
        if (isset($wadData['maps']) && is_array($wadData['maps'])) {
            foreach ($wadData['maps'] as $mapData) {
                $mapData['wad_id'] = $wad->id;
                Map::create($mapData);
            }
        }

        // Prepare paths
        $zipUrl = "https://doomwads.andach.co.uk/zips/{$wadData['idgames_path']}.zip";
        $zipStoragePath = "zips/{$wadData['idgames_path']}.zip";
        $extractPath = "wads/{$wadData['idgames_path']}";

        // Download ZIP to storage disk 'zips'
        $zipContent = Http::get($zipUrl)->body();
        Storage::disk('zips')->put("{$wadData['idgames_path']}.zip", $zipContent);

        // Unzip to storage disk 'wads'
        $zipFullPath = Storage::disk('zips')->path("{$wadData['idgames_path']}.zip");

        $zip = new ZipArchive;

        if ($zip->open($zipFullPath) === TRUE) {
            $extractFullPath = Storage::disk('wads')->path($wadData['idgames_path']);

            if (!is_dir($extractFullPath)) {
                $success = mkdir($extractFullPath, 0755, true);
            }

            $zip->extractTo($extractFullPath);

            $zip->close();
        } else {
            // Debug: zip open failure
            dd('Failed to open ZIP file.', ['zipFullPath' => $zipFullPath]);
        }

        session()->flash('success', 'Wad downloaded, saved, and extracted successfully.');

        return redirect()->route('wad.index');
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
        $args['skills'] = [
            1 => "I'm too young to die",
            2 => "Hey, not too rough",
            3 => "Hurt me plenty",
            4 => "Ultra-Violence",
            5 => "Nightmare!"
        ];
        $args['maps'] = $args['wad']->maps()->orderBy('internal_name', 'asc')->get();
        $args['demos'] = $args['wad']->demosLink()
            ->join('maps', 'demos.map_id', '=', 'maps.id')
            ->orderBy('maps.internal_name', 'asc')
            ->orderBy('demos.category', 'asc')
            ->orderByRaw("
                    (CAST(substr(demos.time, 1, instr(demos.time, ':') - 1) AS INTEGER) * 60) +
                    CAST(substr(demos.time, instr(demos.time, ':') + 1) AS REAL)
                ")
            ->select('demos.*')
            ->get();
        $args['attempts'] = $args['wad']->attempts()->get();

        return view('main.wad.show', $args);
    }

    public function text($id)
    {
        $args = [];
        $args['wad'] = Wad::find($id);

        return view('main.wad.text', $args);
    }

    public function viddumpAll($wadId)
    {
        $wad = Wad::with('demosLink')->findOrFail($wadId);
        $results = [];

        foreach ($wad->demosLink as $demo) {
            try {
                $output = $demo->makeViddump(68); // waits until complete
                $results[] = ['id' => $demo->id, 'status' => 'success', 'output' => $output];
            } catch (\Exception $e) {
                $results[] = ['id' => $demo->id, 'status' => 'error', 'message' => $e->getMessage()];
            }
        }

        return response()->json([
            'status' => 'all_viddumps_processed',
            'wad_id' => $wadId,
            'results' => $results,
        ]);
    }

}
