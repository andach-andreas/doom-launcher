<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $path = storage_path('things-database.csv');
        if (!file_exists($path)) {
            throw new \Exception("CSV file not found at: $path");
        }

        $handle = fopen($path, 'r');
        $header = fgetcsv($handle);

        while (($row = fgetcsv($handle)) !== false) {
            $data = array_combine($header, $row);

            DB::table('types')->insert([
                'type'          => (int) $data['type'],
                'name'          => $data['name'],
                'category'      => $data['category'],
                'spawn_health'  => (int) $data['spawnhealth'],
                'wad_id'        => !empty($data['wad_id']) ? (int) $data['wad_id'] : null,
                'port'          => $data['port'],
            ]);
        }

        fclose($handle);
    }
}
