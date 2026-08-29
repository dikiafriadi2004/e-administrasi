<?php

use App\Http\Controllers\PreviewSuratController;
use Illuminate\Support\Facades\Route;

// Root: redirect ke dashboard role masing-masing atau ke login
Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route(auth()->user()->role.'.dashboard');
    }

    return redirect()->route('login');
});

// Preview surat — bisa diakses semua role yang sudah login
Route::middleware('auth')
    ->get('/preview-surat', PreviewSuratController::class)
    ->name('preview-surat');

// Mahasiswa routes
Route::middleware(['auth', 'mahasiswa'])
    ->prefix('mahasiswa')
    ->name('mahasiswa.')
    ->group(base_path('routes/mahasiswa.php'));

// Admin routes
Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin.php'));

// Kaprodi routes
Route::middleware(['auth', 'kaprodi'])
    ->prefix('kaprodi')
    ->name('kaprodi.')
    ->group(base_path('routes/kaprodi.php'));

require __DIR__.'/auth.php';
