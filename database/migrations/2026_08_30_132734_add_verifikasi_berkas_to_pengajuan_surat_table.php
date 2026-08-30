<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom untuk alur verifikasi berkas sidang skripsi oleh Admin.
 *
 * catatan_admin     : komentar admin jika berkas kurang/tidak sesuai
 * berkas_diverifikasi : true jika admin sudah verifikasi berkas dan OK
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_surat', function (Blueprint $table): void {
            $table->text('catatan_admin')->nullable()->after('catatan_kaprodi');
            $table->boolean('berkas_diverifikasi')->default(false)->after('catatan_admin');
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_surat', function (Blueprint $table): void {
            $table->dropColumn(['catatan_admin', 'berkas_diverifikasi']);
        });
    }
};
