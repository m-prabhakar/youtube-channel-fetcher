<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\YouTubeController;

Route::get('/', [YouTubeController::class, 'index']);
Route::post('/fetch-channels', [YouTubeController::class, 'fetchChannels'])->name('fetch.channels');
Route::post('/export-channels', [YouTubeController::class, 'exportChannels'])->name('export.channels');
