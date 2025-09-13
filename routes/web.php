<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\EditingLockController;
use App\Models\Audio;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\DashboardAdminController;
use App\Http\Controllers\Editor\EditorDashboardController;
use App\Http\Controllers\PublicAudioController;
use App\Http\Controllers\Admin\UserManagementController;

// ---------- Página raíz ----------
Route::get('/', function () {
    return redirect()->route('login'); // lleva al login por defecto
});

// ---------- Redirección de dashboard según rol ----------
Route::get('/dashboard', function () {
    if (auth()->check()) {
        if (auth()->user()->role === 'admin') {
            return redirect()->route('admin.dashboard');
        }
        if (auth()->user()->role === 'editor') {
            return redirect()->route('editor.dashboard');
        }
    }
    return redirect()->route('login');
})->name('dashboard')->middleware('web');

// ---------- Perfil (requiere auth) ----------
Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// ---------- Rutas públicas ----------
Route::get('/audios', [PublicAudioController::class, 'index'])->name('public.audios');
Route::get('/public', [PublicAudioController::class, 'view'])->name('public.view');
Route::get('/play-audio/{audio}', function (Audio $audio) {
    if (!$audio->archivo || !Storage::disk('public')->exists($audio->archivo)) {
        abort(404, 'Audio file not found or path is invalid.');
    }

    $extension = pathinfo($audio->archivo, PATHINFO_EXTENSION);
    $contentType = match (strtolower($extension)) {
        'mp3' => 'audio/mpeg',
        'wav' => 'audio/wav',
        'aac' => 'audio/aac',
        'm4a' => 'audio/mp4',
        'ogg' => 'audio/ogg',
        default => 'application/octet-stream',
    };

    // Usar path absoluto con respuesta de archivo para soportar Range y mejor compatibilidad
    $absolute = Storage::disk('public')->path($audio->archivo);
    return response()->file($absolute, [
        'Content-Type' => $contentType,
        'Accept-Ranges' => 'bytes',
        'Cache-Control' => 'public, max-age=31536000',
    ]);
})->name('public.audios.play');
Route::get('/download-audio/{audio}', [PublicAudioController::class, 'download'])->name('public.download_audio');

// ---------- Admin ----------
Route::middleware(['auth', 'verified', 'can:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/', [DashboardAdminController::class, 'index'])->name('dashboard');

        Route::resource('autores', App\Http\Controllers\Admin\AutorAdminController::class)
            ->parameters(['autores' => 'autor']);

        Route::resource('series', App\Http\Controllers\Admin\SerieAdminController::class)
            ->parameters(['series' => 'serie']);

        Route::post('audios/upload-temp', [App\Http\Controllers\Admin\AudioAdminController::class, 'uploadTemp'])->name('audios.uploadTemp');
        Route::get('audios/check-date', [App\Http\Controllers\Admin\AudioAdminController::class, 'checkDate'])->name('audios.checkDate');
        Route::resource('audios', App\Http\Controllers\Admin\AudioAdminController::class)
            ->parameters(['audios' => 'audio']);

        Route::get('logs', [DashboardAdminController::class, 'logs'])->name('logs');

        Route::resource('users', UserManagementController::class)->only(['index', 'create', 'store', 'destroy']);
    });

// ---------- Editor ----------
Route::middleware(['auth', 'verified', 'can:editor'])
    ->prefix('editor')
    ->name('editor.')
    ->group(function () {
        Route::get('/', [EditorDashboardController::class, 'index'])->name('dashboard');

        // Reutilizamos los controladores de Admin pero limitando acciones
        Route::resource('autores', App\Http\Controllers\Admin\AutorAdminController::class)
            ->only(['index','create','store','edit','update','destroy'])
            ->parameters(['autores' => 'autor']);

        Route::resource('series', App\Http\Controllers\Admin\SerieAdminController::class)
            ->only(['index','create','store','edit','update','destroy'])
            ->parameters(['series' => 'serie']);

        Route::post('audios/upload-temp', [App\Http\Controllers\Admin\AudioAdminController::class, 'uploadTemp'])->name('audios.uploadTemp');
        Route::get('audios/check-date', [App\Http\Controllers\Admin\AudioAdminController::class, 'checkDate'])->name('audios.checkDate');
        Route::resource('audios', App\Http\Controllers\Admin\AudioAdminController::class)
            ->only(['index','create','store','edit','update','destroy'])
            ->parameters(['audios' => 'audio']);
    });

// ---------- Rutas de autenticación (Breeze/Fortify) ----------
require __DIR__.'/auth.php';

// --- Editing locks endpoints (admin/editor authenticated) ---
Route::middleware(['auth'])->group(function () {
    Route::post('/locks/acquire', [EditingLockController::class, 'acquire'])->name('locks.acquire');
    Route::post('/locks/heartbeat', [EditingLockController::class, 'heartbeat'])->name('locks.heartbeat');
    Route::delete('/locks/release', [EditingLockController::class, 'release'])->name('locks.release');
    Route::get('/locks/status', [EditingLockController::class, 'status'])->name('locks.status');
});
