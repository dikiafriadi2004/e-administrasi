<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // SQLite (test) tidak support MODIFY COLUMN ENUM — skip, kolom TEXT bebas diisi.
        // MySQL pakai DB::statement agar tidak butuh doctrine/dbal.
        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            return;
        }

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
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            return;
        }

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
