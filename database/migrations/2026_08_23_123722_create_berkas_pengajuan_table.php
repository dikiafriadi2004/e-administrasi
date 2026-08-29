<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('berkas_pengajuan', function (Blueprint $table): void {
            $table->id();
            $table->morphs('pengajuan'); // pengajuan_type + pengajuan_id
            $table->string('label', 100)->default('Dokumen Pendukung');
            $table->string('path_file', 500);
            $table->string('nama_asli', 255);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('berkas_pengajuan');
    }
};
