<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tambah kolom bidang_kajian dan mata_kuliah ke tabel dosens.
 *
 * bidang_kajian : JSON array bidang kajian dosen (bisa banyak)
 *                 contoh: ["Rekayasa Perangkat Lunak", "Basis Data"]
 * mata_kuliah   : JSON array mata kuliah yang diampu (bisa banyak)
 *                 contoh: ["Pemrograman Web", "Algoritma & Pemrograman"]
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dosens', function (Blueprint $table): void {
            $table->json('bidang_kajian')->nullable()->after('kapasitas_maksimal');
            $table->json('mata_kuliah')->nullable()->after('bidang_kajian');
        });
    }

    public function down(): void
    {
        Schema::table('dosens', function (Blueprint $table): void {
            $table->dropColumn(['bidang_kajian', 'mata_kuliah']);
        });
    }
};
