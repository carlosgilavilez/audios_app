<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AudioController;
use App\Http\Controllers\Api\AutorController;
use App\Http\Controllers\Api\SerieController;

Route::get('/ping', function () {
    return response()->json(['message' => 'API funcionando 🚀']);
});

// Rutas para Audios
Route::get('/audios', [AudioController::class, 'index']);
Route::post('/audios', [AudioController::class, 'store']);

// Rutas para Autores y Series (Admin CRUD)
Route::apiResource('autores', AutorController::class)->parameters(['autores' => 'autor'])->middleware('auth:web');
Route::apiResource('series', SerieController::class)->middleware('auth:web');