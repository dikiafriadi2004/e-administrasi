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

        // SQLite (test) tidak punya CHECK enum — skip, kolom TEXT bebas diisi.
        // MySQL pakai DB::statement agar tidak butuh doctrine/dbal.
        if ($driver !== 'mysql' && $driver !== 'mariadb') {
            return;
        }

        DB::statement("ALTER TABLE pengajuan_judul MODIFY COLUMN status ENUM('diajukan','diverifikasi','diverifikasi_admin','disetujui','ditolak') NOT NULL DEFAULT 'diajukan'");

        DB::statement("ALTER TABLE pengajuan_surat MODIFY COLUMN status ENUM('diajukan','diverifikasi','diverifikasi_admin','disetujui','menunggu_ttd','sudah_ditandatangani','selesai','ditolak') NOT NULL DEFAULT 'diajukan'");
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

        DB::statement("ALTER TABLE pengajuan_judul MODIFY COLUMN status ENUM('diajukan','diverifikasi','disetujui','ditolak') NOT NULL DEFAULT 'diajukan'");

        DB::statement("ALTER TABLE pengajuan_surat MODIFY COLUMN status ENUM('diajukan','diverifikasi','disetujui','menunggu_ttd','sudah_ditandatangani','selesai','ditolak') NOT NULL DEFAULT 'diajukan'");
    }
};
