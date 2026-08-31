<?php

use App\Http\Controllers\Admin\AntrianSuratController;
use App\Http\Controllers\Admin\ArsipSuratController;
use App\Http\Controllers\Admin\BuatSuratLangsungController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DosenController;
use App\Http\Controllers\Admin\GenerateSuratController;
use App\Http\Controllers\Admin\JadwalController;
use App\Http\Controllers\Admin\MahasiswaController;
use App\Http\Controllers\Admin\MahasiswaImportController;
use App\Http\Controllers\Admin\DosenImportController;
use App\Http\Controllers\Admin\PengaturanController;
use App\Http\Controllers\Admin\TemplateSuratController;
use App\Http\Controllers\ProfilController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
Route::get('/dashboard/rasio', [DashboardController::class, 'rasio'])->name('dashboard.rasio');

// Profil
Route::get('/profil', [ProfilController::class, 'show'])->name('profil.show');

// Mahasiswa
Route::resource('mahasiswa', MahasiswaController::class)->except(['show', 'destroy']);
Route::post('mahasiswa/{mahasiswa}/toggle-active', [MahasiswaController::class, 'toggleActive'])
    ->name('mahasiswa.toggle-active');
Route::get('mahasiswa/import', [MahasiswaImportController::class, 'create'])->name('mahasiswa.import.create');
Route::post('mahasiswa/import', [MahasiswaImportController::class, 'store'])->name('mahasiswa.import.store');
Route::get('mahasiswa/import/template', [MahasiswaImportController::class, 'template'])->name('mahasiswa.import.template');

Route::get('dosen/import', [DosenImportController::class, 'create'])->name('dosen.import.create');
Route::post('dosen/import', [DosenImportController::class, 'store'])->name('dosen.import.store');
Route::get('dosen/import/template', [DosenImportController::class, 'template'])->name('dosen.import.template');

// Dosen
Route::resource('dosen', DosenController::class)->except(['show', 'destroy']);

// Template Surat
Route::get('template-surat', [TemplateSuratController::class, 'index'])->name('template-surat.index');
Route::get('template-surat/{jenis}/upload', [TemplateSuratController::class, 'upload'])->name('template-surat.upload');
Route::get('template-surat/{jenis}/download', [TemplateSuratController::class, 'download'])->name('template-surat.download');
Route::post('template-surat/{jenis}', [TemplateSuratController::class, 'store'])->name('template-surat.store');

// ── Antrian Surat (dari mahasiswa: aktif kuliah, dll) ──────────────────────
Route::prefix('surat-masuk')->name('surat.')->group(function () {
    Route::get('/', [AntrianSuratController::class, 'index'])->name('index');
    Route::get('/{surat}', [AntrianSuratController::class, 'show'])->name('show');
    Route::post('/{surat}/tolak', [AntrianSuratController::class, 'tolak'])->name('tolak');
    Route::post('/{surat}/selesaikan', [AntrianSuratController::class, 'selesaikan'])->name('selesaikan');
});

// ── Jadwal Seminar & Sidang ────────────────────────────────────────────────
Route::prefix('jadwal')->name('jadwal.')->group(function () {
    Route::get('/', [JadwalController::class, 'index'])->name('index');
    Route::get('/{pengajuan}', [JadwalController::class, 'show'])->name('show');
    Route::post('/{pengajuan}/tetapkan-jadwal', [JadwalController::class, 'tetapkanJadwal'])->name('tetapkan-jadwal');
    Route::post('/{pengajuan}/verifikasi-berkas', [JadwalController::class, 'verifikasiBerkas'])->name('verifikasi-berkas');
    Route::post('/{pengajuan}/generate-undangan', [JadwalController::class, 'generateUndangan'])->name('generate-undangan');
    Route::post('/{pengajuan}/upload-undangan', [JadwalController::class, 'uploadUndangan'])->name('upload-undangan');
    Route::post('/{pengajuan}/upload-absensi', [JadwalController::class, 'uploadAbsensi'])->name('upload-absensi');
    Route::get('/{pengajuan}/download-absensi', [JadwalController::class, 'downloadAbsensi'])->name('download-absensi');
    Route::get('/{pengajuan}/download-undangan', [JadwalController::class, 'downloadUndangan'])->name('download-undangan');
});

// ── Download berkas syarat mahasiswa ──────────────────────────────────────
Route::get('/berkas/{berkas}/download', [JadwalController::class, 'downloadBerkas'])->name('berkas.download');

// ── Generate, Download & Upload Scan ──────────────────────────────────────
Route::post('surat/{surat}/generate', [GenerateSuratController::class, 'generate'])->name('surat.generate');
Route::get('surat/{surat}/download/{tipe}', [GenerateSuratController::class, 'download'])->name('surat.download');
Route::post('surat/{surat}/upload-scan', [GenerateSuratController::class, 'uploadScan'])->name('surat.upload-scan');

// ── Buat Surat Langsung (undangan penguji, dll — admin inisiatif) ──────────
Route::get('buat-surat/create', [BuatSuratLangsungController::class, 'create'])->name('buat-surat.create');
Route::post('buat-surat', [BuatSuratLangsungController::class, 'store'])->name('buat-surat.store');

// Arsip Semua Surat
Route::get('arsip', [ArsipSuratController::class, 'index'])->name('arsip.index');
Route::get('arsip/export', [ArsipSuratController::class, 'export'])->name('arsip.export');

// Pengaturan Sistem
Route::get('pengaturan', [PengaturanController::class, 'index'])->name('pengaturan.index');
Route::put('pengaturan', [PengaturanController::class, 'update'])->name('pengaturan.update');
