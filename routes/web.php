<?php

use App\Http\Controllers\GeneratorController;
use Illuminate\Support\Facades\Route;

Route::get('/', [GeneratorController::class, 'index'])->name('generator.index');
Route::post('/generate', [GeneratorController::class, 'generate'])->name('generator.generate');
Route::post('/download-monitors', [GeneratorController::class, 'downloadMonitors'])->name('generator.download-monitors');
