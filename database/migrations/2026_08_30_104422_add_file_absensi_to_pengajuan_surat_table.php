<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom file_absensi_seminar ke pengajuan_surat.
 *
 * Dipakai oleh admin untuk upload absensi hasil seminar proposal.
 * Mahasiswa bisa lihat/download file ini sebagai syarat pengajuan izin penelitian.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_surat', function (Blueprint $table): void {
            // Path file absensi seminar (diupload admin setelah seminar selesai)
            $table->string('file_absensi_seminar', 500)->nullable()->after('file_scan');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_surat', function (Blueprint $table): void {
            $table->dropColumn('file_absensi_seminar');
        });
    }
};
