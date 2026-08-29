<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah jadwal + catatan ke pengajuan_surat (seminar & sidang)
        Schema::table('pengajuan_surat', function (Blueprint $table): void {
            $table->date('tanggal_jadwal')->nullable()->after('dosen_penguji_2_id');
            $table->string('waktu_jadwal', 50)->nullable()->after('tanggal_jadwal');
            $table->string('tempat_jadwal', 255)->nullable()->after('waktu_jadwal');
            $table->text('catatan_kaprodi')->nullable()->after('tempat_jadwal');
        });

        // Tambah catatan ke pengajuan_judul
        Schema::table('pengajuan_judul', function (Blueprint $table): void {
            $table->text('catatan_kaprodi')->nullable()->after('catatan_penolakan');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_surat', function (Blueprint $table): void {
            $table->dropColumn(['tanggal_jadwal', 'waktu_jadwal', 'tempat_jadwal', 'catatan_kaprodi']);
        });

        Schema::table('pengajuan_judul', function (Blueprint $table): void {
            $table->dropColumn('catatan_kaprodi');
        });
    }
};
