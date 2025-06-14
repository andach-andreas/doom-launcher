<?php

namespace App\Http\Controllers;

use App\Models\Attempt;
use App\Models\Demo;
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
        $args['attempts'] = $args['map']->bestAttemptTimes();
        $args['demos'] = $args['map']->bestDemoTimes();
        $args['combined'] = [];

        foreach (config('globals.demo_categories') as $category) {
            $demo = $args['demos'][$category] ?? null;
            $attempt = $args['attempts'][$category] ?? null;

            $demoSeconds = $this->timeToSeconds($demo);
            $attemptSeconds = $attempt !== null ? (float)$attempt : null;

            $args['combined'][$category] = [
                'demo' => $demo,
                'attempt' => $attempt,
                'faster' => $attemptSeconds !== null && ($demoSeconds === null || $attemptSeconds < $demoSeconds),
            ];
        }

        return view('main.map.show', $args);
    }

    private function timeToSeconds($time)
    {
        if (!$time) return null;
        if (str_contains($time, ':')) {
            [$min, $sec] = explode(':', $time);
            return ((int) $min) * 60 + (float) $sec;
        }
        return (float) $time;
    }
}
