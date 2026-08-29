<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('templates_surat', function (Blueprint $table) {
            $table->id();
            $table->enum('jenis_surat', [
                'aktif_kuliah',
                'seminar_proposal',
                'sidang_skripsi',
                'undangan_penguji',
            ]);
            $table->string('path_file', 500);
            $table->unsignedSmallInteger('versi')->default(1);
            $table->boolean('is_aktif')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('templates_surat');
    }
};
