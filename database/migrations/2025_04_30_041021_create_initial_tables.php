<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attempts', function (Blueprint $table) {
            $table->integer('id')->primary();
            $table->integer('map_id')->nullable();
            $table->integer('map_completed_id')->nullable();
            $table->integer('wad_id');
            $table->string('category')->nullable();
            $table->string('time')->nullable();
            $table->string('lmp_file')->nullable();

            $table->string('version')->nullable();
            $table->integer('skill_number')->nullable();
            $table->integer('mode_number')->nullable();
            $table->boolean('respawn')->nullable();
            $table->boolean('fast')->nullable();
            $table->boolean('nomonsters')->nullable();
            $table->boolean('number_of_players')->nullable();
            $table->integer('tics')->nullable();
            $table->integer('seconds')->nullable();

            $table->timestamps();
        });

        Schema::create('comp_levels', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('level');
            $table->timestamps();
        });

        Schema::create('demos', function (Blueprint $table) {
            $table->integer('id')->primary(); // Using third-party demo ID as primary key
            $table->integer('map_id')->nullable();
            $table->integer('wad_id');
            $table->string('category');
            $table->string('player');
            $table->string('engine');
            $table->string('note')->nullable();
            $table->string('time');
            $table->string('lmp_file')->nullable();
            $table->string('lmp_url_zip');
            $table->text('lmp_text_file')->nullable();
            $table->string('youtube_id')->nullable();
            $table->string('youtube_link')->nullable();
            $table->string('comment')->nullable();

            $table->string('version')->nullable();
            $table->integer('skill_number')->nullable();
            $table->integer('mode_number')->nullable();
            $table->boolean('respawn')->nullable();
            $table->boolean('fast')->nullable();
            $table->boolean('nomonsters')->nullable();
            $table->boolean('number_of_players')->nullable();
            $table->integer('tics')->nullable();
            $table->integer('seconds')->nullable();

            $table->timestamps();
        });

        Schema::create('installs', function (Blueprint $table) {
            $table->id();
            $table->integer('port_id');
            $table->string('version');
            $table->string('download_url')->nullable();
            $table->timestamp('downloaded_at')->nullable();
            $table->timestamp('extracted_at')->nullable();
            $table->boolean('available')->default(true);
            $table->timestamps();

            $table->unique(['port_id', 'version']);
        });

        Schema::create('linedefs', function (Blueprint $table) {
            $table->id();
            $table->integer('internal_id');
            $table->integer('map_id');
            $table->integer('start_vertex_id');
            $table->integer('end_vertex_id');
            $table->integer('flags');
            $table->integer('special');
            $table->integer('tag');
            $table->integer('front_sidedef_id');
            $table->integer('back_sidedef_id')->nullable();
            $table->timestamps();

            $table->unique(['map_id', 'internal_id']);
        });

        Schema::create('lumps', function (Blueprint $table) {
            $table->id();
            $table->integer('wad_id');
            $table->string('name', 8);
            $table->integer('offset');
            $table->integer('size');
            $table->longText('data')->charset('binary')->nullable();
            $table->timestamps();
        });

        Schema::create('maps', function (Blueprint $table) {
            $table->id();
            $table->integer('wad_id');
            $table->string('internal_name'); // e.g. E1M1, MAP01
            $table->string('name')->nullable(); // Human-readable map name
            $table->string('image_path')->nullable();
            $table->unsignedInteger('count_things')->default(0);
            $table->unsignedInteger('count_linedefs')->default(0);
            $table->unsignedInteger('count_sidedefs')->default(0);
            $table->unsignedInteger('count_vertexes')->default(0);
            $table->unsignedInteger('count_sectors')->default(0);
            $table->timestamps();
        });

        Schema::create('ports', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('github_url')->nullable();
            $table->string('slug')->nullable();
            $table->timestamps();
        });

        Schema::create('sectors', function (Blueprint $table) {
            $table->id();
            $table->integer('internal_id');
            $table->integer('map_id');
            $table->integer('floor_height');
            $table->integer('ceiling_height');
            $table->string('floor_texture');
            $table->string('ceiling_texture');
            $table->integer('light_level');
            $table->integer('type');
            $table->integer('tag');
            $table->timestamps();

            $table->unique(['map_id', 'internal_id']);
        });

        Schema::create('sidedefs', function (Blueprint $table) {
            $table->id();
            $table->integer('internal_id');
            $table->integer('map_id');
            $table->integer('x_offset');
            $table->integer('y_offset');
            $table->string('upper_texture');
            $table->string('lower_texture');
            $table->string('middle_texture');
            $table->integer('sector_id');
            $table->timestamps();

            $table->unique(['map_id', 'internal_id']);
        });

        Schema::create('things', function (Blueprint $table) {
            $table->id();
            $table->integer('map_id');
            $table->integer('thing_type_id');
            $table->integer('wad_id');
            $table->integer('angle');
            $table->integer('flags');
            $table->integer('type');
            $table->integer('x');
            $table->integer('y');
            $table->boolean('flag_ambush')->default(false);
            $table->boolean('flag_coop')->default(false);
            $table->boolean('flag_deathmatch')->default(false);
            $table->boolean('flag_friendly')->default(false);
            $table->boolean('flag_multiplayer')->default(false);
            $table->boolean('flag_single')->default(false);
            $table->boolean('flag_skill1')->default(false);
            $table->boolean('flag_skill2')->default(false);
            $table->boolean('flag_skill3')->default(false);
            $table->timestamps();
        });

        Schema::create('types', function (Blueprint $table) {
            $table->id();
            $table->integer('type');
            $table->integer('wad_id')->nullable();
            $table->string('name');
            $table->string('category');
            $table->string('port')->nullable();
            $table->integer('spawn_health')->default(0);
            $table->timestamps();

            $table->unique(['type', 'wad_id']);
        });

        Schema::create('vertices', function (Blueprint $table) {
            $table->id();
            $table->integer('internal_id');
            $table->integer('map_id');
            $table->integer('x');
            $table->integer('y');
            $table->timestamps();

            $table->unique(['map_id', 'internal_id']);
        });

        Schema::create('wads', function (Blueprint $table) {
            $table->id();

            $table->string('foldername');
            $table->string('filename');
            $table->string('filename_with_extension');
            $table->string('idgames_path');
            $table->integer('complevel')->nullable();
            $table->integer('maps_count')->nullable();
            $table->integer('linedefs_count')->nullable();
            $table->integer('sidedefs_count')->nullable();
            $table->integer('vertexes_count')->nullable();
            $table->integer('textures_count')->nullable();
            $table->integer('things_count')->nullable();
            $table->integer('sectors_count')->nullable();
            $table->string('iwad')->nullable();

            // From Text File
            $table->text('archive_maintainer')->nullable();
            $table->text('update_to')->nullable();
            $table->text('advanced_engine_needed')->nullable();
            $table->text('primary_purpose')->nullable();
            $table->text('title')->nullable();
            $table->text('release_date')->nullable();
            $table->text('author')->nullable();
            $table->text('email_address')->nullable();
            $table->text('other_files_by_author')->nullable();
            $table->text('misc_author_info')->nullable();
            $table->text('description')->nullable();
            $table->text('credits')->nullable();
            $table->text('new_levels')->nullable();
            $table->text('sounds')->nullable();
            $table->text('music')->nullable();
            $table->text('graphics')->nullable();
            $table->text('dehacked_patch')->nullable();
            $table->text('demos')->nullable();
            $table->text('other')->nullable();
            $table->text('other_files_required')->nullable();
            $table->text('game')->nullable();
            $table->text('map')->nullable();
            $table->text('single_player')->nullable();
            $table->text('coop')->nullable();
            $table->text('deathmatch')->nullable();
            $table->text('other_game_styles')->nullable();
            $table->text('difficulty_settings')->nullable();
            $table->text('base')->nullable();
            $table->text('build_time')->nullable();
            $table->text('editors_used')->nullable();
            $table->text('known_bugs')->nullable();
            $table->text('may_not_run_with')->nullable();
            $table->text('tested_with')->nullable();
            $table->text('where_to_get_web')->nullable();
            $table->text('where_to_get_ftp')->nullable();

            $table->timestamps();
        });

        Schema::create('link_installs_wads', function (Blueprint $table) {
            $table->id();
            $table->integer('install_id');
            $table->integer('wad_id');
            $table->boolean('is_compatible');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['install_id', 'wad_id']);
        });

        Schema::create('link_lumps_maps', function (Blueprint $table) {
            $table->id();
            $table->integer('map_id');  // Foreign key for maps
            $table->integer('lump_id'); // Foreign key for lumps
            $table->boolean('is_header')->default(false); // Indicates if this lump is the header
            $table->timestamps();
        });

        Schema::create('link_ports_wads', function (Blueprint $table) {
            $table->id();
            $table->integer('port_id');
            $table->integer('wad_id');
            $table->timestamps();

            $table->unique(['port_id', 'wad_id']);
        });

        DB::table('comp_levels')->insert([
            ['level' => -1,  'name' => 'Engine defaults (current version)'],
            ['level' => 0,   'name' => 'Doom v1.2'],
            ['level' => 1,   'name' => 'Doom v1.666'],
            ['level' => 2,   'name' => 'Doom v1.9'],
            ['level' => 3,   'name' => 'Ultimate Doom & Doom95'],
            ['level' => 4,   'name' => 'Final Doom'],
            ['level' => 5,   'name' => 'DOSDoom'],
            ['level' => 6,   'name' => 'TASDoom'],
            ['level' => 7,   'name' => "Boom's inaccurate vanilla compatibility mode"],
            ['level' => 8,   'name' => 'Boom v2.01'],
            ['level' => 9,   'name' => 'Boom v2.02'],
            ['level' => 10,  'name' => 'LxDoom'],
            ['level' => 11,  'name' => 'MBF'],
            ['level' => 12,  'name' => 'PrBoom v2.03beta'],
            ['level' => 13,  'name' => 'PrBoom v2.1.0'],
            ['level' => 14,  'name' => 'PrBoom v2.1.1 - 2.2.6'],
            ['level' => 15,  'name' => 'PrBoom v2.3.x'],
            ['level' => 16,  'name' => 'PrBoom v2.4.0'],
            ['level' => 17,  'name' => 'Engine defaults (current version)'],
            ['level' => 21,  'name' => 'MBF21'],
        ]);

        DB::table('ports')->insert([
            [
                'name' => 'Chocolate Doom',
                'slug' => 'chocolate-doom',
                'github_url' => 'https://github.com/chocolate-doom/chocolate-doom'
            ],
            [
                'name' => 'Crispy Doom',
                'slug' => 'crispy-doom',
                'github_url' => 'https://github.com/fabiangreffrath/crispy-doom'
            ],
            [
                'name' => 'DOOM Retro',
                'slug' => 'doom-retro',
                'github_url' => 'https://github.com/bradharding/doomretro'
            ],
            [
                'name' => 'DSDA-Doom',
                'slug' => 'dsda-doom',
                'github_url' => 'https://github.com/kraflab/dsda-doom'
            ],
            [
                'name' => 'Eternity Engine',
                'slug' => 'eternity',
                'github_url' => 'https://github.com/team-eternity/eternity'
            ],
            [
                'name' => 'GZDoom',
                'slug' => 'gzdoom',
                'github_url' => 'https://github.com/ZDoom/gzdoom'
            ],
            [
                'name' => 'PrBoom+',
                'slug' => 'prboom-plus',
                'github_url' => 'https://github.com/coelckers/prboom-plus'
            ],
//            [
//                'name' => 'Zandronum',
//                'slug' => 'zandronum',
//                'github_url' => 'https://github.com/drfrag666/zandronum'
//            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('link_levels_things');
        Schema::dropIfExists('wads');
        Schema::dropIfExists('things');
        Schema::dropIfExists('thing_types');
        Schema::dropIfExists('levels');
        Schema::dropIfExists('lumps');
        Schema::dropIfExists('comp_levels');
    }
};
