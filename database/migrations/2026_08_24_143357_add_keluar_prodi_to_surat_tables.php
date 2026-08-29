<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Tambahkan keluar_prodi ke enum jenis_surat di kedua tabel
        DB::statement("ALTER TABLE `pengajuan_surat` MODIFY `jenis_surat` ENUM(
            'aktif_kuliah',
            'seminar_proposal',
            'sidang_skripsi',
            'undangan_penguji',
            'izin_magang',
            'rekomendasi_magang',
            'izin_penelitian',
            'keluar_prodi'
        ) NOT NULL");

        DB::statement("ALTER TABLE `templates_surat` MODIFY `jenis_surat` ENUM(
            'aktif_kuliah',
            'seminar_proposal',
            'sidang_skripsi',
            'undangan_penguji',
            'izin_magang',
            'rekomendasi_magang',
            'izin_penelitian',
            'keluar_prodi'
        ) NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE `pengajuan_surat` MODIFY `jenis_surat` ENUM(
            'aktif_kuliah',
            'seminar_proposal',
            'sidang_skripsi',
            'undangan_penguji',
            'izin_magang',
            'rekomendasi_magang',
            'izin_penelitian'
        ) NOT NULL");

        DB::statement("ALTER TABLE `templates_surat` MODIFY `jenis_surat` ENUM(
            'aktif_kuliah',
            'seminar_proposal',
            'sidang_skripsi',
            'undangan_penguji',
            'izin_magang',
            'rekomendasi_magang',
            'izin_penelitian'
        ) NOT NULL");
    }
};
