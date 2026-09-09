<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom baru ke pengajuan_judul:
 *   pendekatan_penelitian : teks bebas (kualitatif, kuantitatif, dll)
 *   nama_dosen_wali       : nama dosen wali mahasiswa
 *   nama_dosen_spup       : nama dosen SPUP yang menandatangani form
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_judul', function (Blueprint $table): void {
            $table->string('pendekatan_penelitian', 255)->nullable()->after('ringkasan');
            $table->string('nama_dosen_wali', 255)->nullable()->after('pendekatan_penelitian');
            $table->string('nama_dosen_spup', 255)->nullable()->after('nama_dosen_wali');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_judul', function (Blueprint $table): void {
            $table->dropColumn(['pendekatan_penelitian', 'nama_dosen_wali', 'nama_dosen_spup']);
        });
    }
};
