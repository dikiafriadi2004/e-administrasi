<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom untuk berkas post-sidang yang diupload Admin:
 *   file_absensi_sidang   : absensi kehadiran dosen penguji & pembimbing
 *   file_lembar_penilaian : lembar penilaian sidang (sudah TTD dosen)
 *   file_berita_acara     : berita acara sidang (sudah TTD dosen)
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_surat', function (Blueprint $table): void {
            $table->string('file_absensi_sidang', 500)->nullable()->after('file_absensi_seminar');
            $table->string('file_lembar_penilaian', 500)->nullable()->after('file_absensi_sidang');
            $table->string('file_berita_acara', 500)->nullable()->after('file_lembar_penilaian');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_surat', function (Blueprint $table): void {
            $table->dropColumn(['file_absensi_sidang', 'file_lembar_penilaian', 'file_berita_acara']);
        });
    }
};
