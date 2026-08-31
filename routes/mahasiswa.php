<?php

use App\Http\Controllers\Mahasiswa\DashboardController;
use App\Http\Controllers\Mahasiswa\PengajuanJudulController;
use App\Http\Controllers\Mahasiswa\PengajuanSuratController;
use App\Http\Controllers\Mahasiswa\RiwayatController;
use App\Http\Controllers\ProfilController;
use Illuminate\Support\Facades\Route;

// Dashboard
Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Profil
Route::get('/profil', [ProfilController::class, 'show'])->name('profil.show');

// Riwayat
Route::get('/riwayat', [RiwayatController::class, 'index'])->name('riwayat.index');

// Pengajuan Judul Skripsi
Route::get('/pengajuan/judul/create', [PengajuanJudulController::class, 'create'])->name('pengajuan.judul.create');
Route::get('/pengajuan/judul/{pengajuanJudul}/edit', [PengajuanJudulController::class, 'edit'])->name('pengajuan.judul.edit');
Route::get('/pengajuan/judul/{pengajuanJudul}/download-bukti', [PengajuanJudulController::class, 'downloadBukti'])->name('pengajuan.judul.download-bukti');
Route::get('/pengajuan/judul/{pengajuanJudul}', [PengajuanJudulController::class, 'show'])->name('pengajuan.judul.show');

// Pengajuan Surat — Aktif Kuliah
Route::get('/pengajuan/aktif-kuliah/create', [PengajuanSuratController::class, 'createAktifKuliah'])->name('pengajuan.aktif-kuliah.create');

// Pengajuan Surat — Izin Magang
Route::get('/pengajuan/izin-magang/create', [PengajuanSuratController::class, 'createIzinMagang'])->name('pengajuan.izin-magang.create');

// Pengajuan Surat — Rekomendasi Magang
Route::get('/pengajuan/rekomendasi-magang/create', [PengajuanSuratController::class, 'createRekomendasiMagang'])->name('pengajuan.rekomendasi-magang.create');

// Pengajuan Surat — Izin Penelitian
Route::get('/pengajuan/izin-penelitian/create', [PengajuanSuratController::class, 'createIzinPenelitian'])->name('pengajuan.izin-penelitian.create');

// Pengajuan Surat — Seminar Proposal
Route::get('/pengajuan/seminar/create', [PengajuanSuratController::class, 'createSeminar'])->name('pengajuan.seminar.create');

// Pengajuan Surat — Sidang Skripsi
Route::get('/pengajuan/sidang/create', [PengajuanSuratController::class, 'createSidang'])->name('pengajuan.sidang.create');

// Detail & Download Surat
Route::get('/surat/{pengajuanSurat}', [PengajuanSuratController::class, 'show'])->name('surat.show');
Route::get('/surat/{pengajuanSurat}/download/{tipe}', [PengajuanSuratController::class, 'download'])->name('surat.download');

// Download absensi seminar (untuk izin penelitian)
Route::get('/seminar/{pengajuanSurat}/download-absensi', [PengajuanSuratController::class, 'downloadAbsensi'])->name('seminar.download-absensi');

// Download berkas syarat
Route::get('/berkas/{berkas}/download', [PengajuanSuratController::class, 'downloadBerkas'])->name('berkas.download');
