<?php

use iEducar\Packages\BuritiLagunaAmbiente\Http\Controllers\AmbienteController;
use iEducar\Packages\BuritiLagunaAmbiente\Http\Controllers\ModulosConfigController;
use iEducar\Packages\BuritiLagunaAmbiente\Http\Middleware\ExigeAdmin;
use Illuminate\Support\Facades\Route;

Route::prefix('laguna-ambiente')->middleware([ExigeAdmin::class])->group(function (): void {
    Route::get('/', [AmbienteController::class, 'index']);
    Route::get('/check', [AmbienteController::class, 'check']);
    Route::post('/check/aprovacao', [AmbienteController::class, 'aprovarModulo']);
    Route::post('/check/testar', [AmbienteController::class, 'testarModulos']);
    Route::get('/setup', [AmbienteController::class, 'setupForm']);
    Route::post('/setup', [AmbienteController::class, 'setupRun']);
    Route::get('/simulacao', [AmbienteController::class, 'simulacaoForm']);
    Route::post('/simulacao', [AmbienteController::class, 'simulacaoRun']);
    Route::get('/tema', [AmbienteController::class, 'temaForm']);
    Route::post('/tema', [AmbienteController::class, 'temaSalvar']);
    Route::get('/usuarios', [AmbienteController::class, 'usuarios']);
    Route::post('/usuarios/senha', [AmbienteController::class, 'politicaSenha']);
});

Route::prefix('configuracoes')->middleware([ExigeAdmin::class])->group(function (): void {
    Route::get('/modulos', [ModulosConfigController::class, 'index']);
    Route::post('/modulos/{id}', [ModulosConfigController::class, 'salvar'])->where('id', '[a-z0-9\-]+');
});

Route::redirect('/laguna-ambiente/modulos', '/configuracoes/modulos');
