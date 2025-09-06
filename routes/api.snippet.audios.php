<?php
// Pega este bloque dentro de routes/api.php (debajo de la línea que define el router).

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AudioController;

Route::get('/audios', [AudioController::class, 'index']);   // público (solo estado Normal)
Route::post('/audios', [AudioController::class, 'store']);  // admin/editor (añade auth si corresponde)
// Para proteger POST, cuando configures Sanctum: Route::middleware('auth:sanctum')->post('/audios', [AudioController::class, 'store']);


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AudioController;

Route::get('/audios', [AudioController::class, 'index']);   // público (solo estado Normal)
Route::post('/audios', [AudioController::class, 'store']);  // admin/editor (añade auth si corresponde)
// Para proteger POST, cuando configures Sanctum: 
// Route::middleware('auth:sanctum')->post('/audios', [AudioController::class, 'store']);
