<?php

use iEducar\Packages\BuritiLagunaAmbiente\Http\Controllers\AmbienteController;
use iEducar\Packages\BuritiLagunaAmbiente\Http\Middleware\ExigeAdmin;
use Illuminate\Support\Facades\Route;

Route::prefix('laguna-ambiente')->middleware([ExigeAdmin::class])->group(function (): void {
    Route::get('/', [AmbienteController::class, 'index']);
    Route::get('/check', [AmbienteController::class, 'check']);
    Route::get('/setup', [AmbienteController::class, 'setupForm']);
    Route::post('/setup', [AmbienteController::class, 'setupRun']);
});
