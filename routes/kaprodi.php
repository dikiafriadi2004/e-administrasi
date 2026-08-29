<?php

use App\Http\Controllers\Kaprodi\AntrianAkademikController;
use App\Http\Controllers\Kaprodi\DashboardController;
use App\Http\Controllers\Kaprodi\UploadScanController;
use App\Http\Controllers\ProfilController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/rasio', [DashboardController::class, 'rasio'])->name('dashboard.rasio');

// Profil
Route::get('/profil', [ProfilController::class, 'show'])->name('profil.show');

// Upload scan (opsional — kaprodi bisa upload scan selain admin)
Route::post('/surat/{surat}/upload-scan', [UploadScanController::class, 'store'])->name('surat.upload-scan');

// Download berkas syarat mahasiswa
Route::get('/berkas/{berkas}/download', [AntrianAkademikController::class, 'downloadBerkas'])->name('berkas.download');

// Antrian Akademik (Judul / Seminar / Sidang) — langsung dari mahasiswa ke kaprodi
Route::prefix('akademik')->name('akademik.')->group(function () {
    Route::get('/', [AntrianAkademikController::class, 'index'])->name('index');

    // Judul Skripsi
    Route::get('/judul/{pengajuan}', [AntrianAkademikController::class, 'showJudul'])->name('judul.show');
    Route::post('/judul/{pengajuan}/setujui', [AntrianAkademikController::class, 'setujuiJudul'])->name('judul.setujui');
    Route::post('/judul/{pengajuan}/tolak', [AntrianAkademikController::class, 'tolakJudul'])->name('judul.tolak');

    // Seminar Proposal
    Route::get('/seminar/{pengajuan}', [AntrianAkademikController::class, 'showSeminar'])->name('seminar.show');
    Route::post('/seminar/{pengajuan}/setujui', [AntrianAkademikController::class, 'setujuiSeminar'])->name('seminar.setujui');
    Route::post('/seminar/{pengajuan}/tolak', [AntrianAkademikController::class, 'tolakSeminar'])->name('seminar.tolak');

    // Sidang Skripsi
    Route::get('/sidang/{pengajuan}', [AntrianAkademikController::class, 'showSidang'])->name('sidang.show');
    Route::post('/sidang/{pengajuan}/setujui', [AntrianAkademikController::class, 'setujuiSidang'])->name('sidang.setujui');
    Route::post('/sidang/{pengajuan}/tolak', [AntrianAkademikController::class, 'tolakSidang'])->name('sidang.tolak');
});
