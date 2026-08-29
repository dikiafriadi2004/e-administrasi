<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
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
