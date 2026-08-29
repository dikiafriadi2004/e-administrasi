<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_judul', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->string('judul', 500);
            $table->string('bidang_kajian');
            $table->text('ringkasan');
            $table->foreignId('dosen_pembimbing_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->enum('status', ['diajukan', 'diverifikasi', 'disetujui', 'ditolak'])->default('diajukan');
            $table->text('catatan_penolakan')->nullable();
            $table->string('file_pendukung', 500)->nullable();
            $table->string('nama_file_pendukung')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_judul');
    }
};
