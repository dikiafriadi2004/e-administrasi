<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        // SQLite (test) tidak support MODIFY COLUMN ENUM — skip, kolom TEXT bebas diisi.
        // MySQL pakai DB::statement agar tidak butuh doctrine/dbal.
        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            return;
        }

        // Tambahkan 3 jenis surat baru ke enum pengajuan_surat
        DB::statement("ALTER TABLE pengajuan_surat MODIFY COLUMN jenis_surat ENUM(
            'aktif_kuliah',
            'seminar_proposal',
            'sidang_skripsi',
            'undangan_penguji',
            'izin_magang',
            'rekomendasi_magang',
            'izin_penelitian'
        ) NOT NULL");

        // Tambahkan 3 jenis surat baru ke enum templates_surat
        DB::statement("ALTER TABLE templates_surat MODIFY COLUMN jenis_surat ENUM(
            'aktif_kuliah',
            'seminar_proposal',
            'sidang_skripsi',
            'undangan_penguji',
            'izin_magang',
            'rekomendasi_magang',
            'izin_penelitian'
        ) NOT NULL");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            return;
        }

        // Hapus 3 jenis surat baru dari enum pengajuan_surat
        DB::statement("ALTER TABLE pengajuan_surat MODIFY COLUMN jenis_surat ENUM(
            'aktif_kuliah',
            'seminar_proposal',
            'sidang_skripsi',
            'undangan_penguji'
        ) NOT NULL");

        // Hapus 3 jenis surat baru dari enum templates_surat
        DB::statement("ALTER TABLE templates_surat MODIFY COLUMN jenis_surat ENUM(
            'aktif_kuliah',
            'seminar_proposal',
            'sidang_skripsi',
            'undangan_penguji'
        ) NOT NULL");
    }
};
