<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambahkan status 'disetujui' ke enum status di pengajuan_surat.
 *
 * Status ini dibutuhkan untuk pengajuan seminar_proposal dan sidang_skripsi
 * yang langsung disetujui Kaprodi (alur baru: diajukan → disetujui).
 *
 * MySQL: ALTER TABLE untuk modifikasi enum.
 * SQLite: recreate kolom (SQLite tidak support ALTER COLUMN untuk enum).
 */
return new class extends Migration
{
    public function up(): void
    {
        // SQLite (test) tidak support modifikasi enum via ALTER COLUMN
        // Gunakan raw SQL yang kompatibel dengan kedua engine
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            Schema::table('pengajuan_surat', function (Blueprint $table) {
                $table->enum('status', [
                    'diajukan',
                    'diverifikasi',
                    'disetujui',          // baru — untuk seminar/sidang
                    'menunggu_ttd',
                    'sudah_ditandatangani',
                    'selesai',
                    'ditolak',
                ])->default('diajukan')->change();
            });
        }
        // SQLite: tidak perlu migrasi — tidak ada CHECK constraint enum di SQLite
        // value bebas diisi string apapun
    }

    public function down(): void
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'mysql' || $driver === 'mariadb') {
            Schema::table('pengajuan_surat', function (Blueprint $table) {
                $table->enum('status', [
                    'diajukan',
                    'diverifikasi',
                    'menunggu_ttd',
                    'sudah_ditandatangani',
                    'selesai',
                    'ditolak',
                ])->default('diajukan')->change();
            });
        }
    }
};
