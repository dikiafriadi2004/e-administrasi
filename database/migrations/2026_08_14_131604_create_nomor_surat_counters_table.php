<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('nomor_surat_counters', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_surat', [
                'aktif_kuliah',
                'seminar_proposal',
                'sidang_skripsi',
                'undangan_penguji',
            ]);
            $table->year('tahun');
            $table->unsignedInteger('counter')->default(0);
            $table->unique(['jenis_surat', 'tahun']);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nomor_surat_counters');
    }
};
