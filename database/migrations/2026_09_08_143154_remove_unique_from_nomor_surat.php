<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Hapus constraint UNIQUE dari nomor_surat.
 *
 * Alasan: Admin hanya menyimpan angka urutan (mis. "001"), bukan nomor penuh.
 * Dua pengajuan berbeda bisa saja diberi angka urutan yang sama (nomor terpisah
 * per jenis surat), sehingga UNIQUE constraint menyebabkan crash.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_surat', function (Blueprint $table): void {
            $table->dropUnique(['nomor_surat']);
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_surat', function (Blueprint $table): void {
            $table->unique('nomor_surat');
        });
    }
};
