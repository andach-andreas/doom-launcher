<?php

use App\Http\Controllers\AttemptController;
use App\Http\Controllers\DSDAController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InstallController;
use App\Http\Controllers\MapController;
use App\Http\Controllers\PortController;
use App\Http\Controllers\WadController;
use Illuminate\Support\Facades\Route;

Route::get('/', [HomeController::class, 'home'])->name('home');

Route::get('/attempt/wad/{wadID}', [AttemptController::class, 'sync'])->name('attempt.sync');
Route::post('/attempt/', [AttemptController::class, 'update'])->name('attempt.update');
Route::get('/attempt/{id}', [AttemptController::class, 'show'])->name('attempt.show');

Route::get('/dsda/{wadID}', [DSDAController::class, 'sync'])->name('dsda.sync');

Route::post('/install/play/', [InstallController::class, 'play'])->name('install.play');
Route::post('/install/viddump/', [InstallController::class, 'viddump'])->name('install.viddump');
Route::get('/install/{id}', [InstallController::class, 'show'])->name('install.show');
Route::get('/install/{id}/extract', [InstallController::class, 'extract'])->name('install.extract');

Route::get('/map/{id}', [MapController::class, 'show'])->name('map.show');
Route::get('/map/{id}/image', [MapController::class, 'render']);

Route::get('/port', [PortController::class, 'index'])->name('port.index');
Route::get('/port/sync', [PortController::class, 'sync'])->name('port.sync');
Route::get('/port/{id}', [PortController::class, 'show'])->name('port.show');

Route::get('/wad', [WadController::class, 'index'])->name('wad.index');
Route::post('/wad/download-and-extract', [WadController::class, 'downloadAndExtract'])->name('wad.download-and-extract');
Route::get('/wad/{id}/insert-into-database', [WadController::class, 'insertIntoDatabase'])->name('wad.insert-into-database');;
Route::get('/wad/{id}/text', [WadController::class, 'text'])->name('wad.text');;
Route::get('/wad/{id}/viddump-all', [WadController::class, 'viddumpAll'])->name('wad.viddump-all');;
Route::get('/wad/{id}', [WadController::class, 'show'])->name('wad.show');
