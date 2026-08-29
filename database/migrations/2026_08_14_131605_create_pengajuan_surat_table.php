<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pengajuan_surat', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mahasiswa_id')->constrained('mahasiswas')->cascadeOnDelete();
            $table->enum('jenis_surat', [
                'aktif_kuliah',
                'seminar_proposal',
                'sidang_skripsi',
                'undangan_penguji',
            ]);
            $table->foreignId('pengajuan_judul_id')->nullable()->constrained('pengajuan_judul')->nullOnDelete();
            $table->json('data_form');
            $table->string('nomor_surat', 100)->nullable()->unique();
            $table->foreignId('dosen_penguji_id')->nullable()->constrained('dosens')->nullOnDelete();
            $table->enum('status', [
                'diajukan',
                'diverifikasi',
                'disetujui',            // untuk seminar_proposal & sidang_skripsi
                'menunggu_ttd',
                'sudah_ditandatangani',
                'selesai',
                'ditolak',
            ])->default('diajukan');
            $table->text('catatan_penolakan')->nullable();
            $table->string('file_docx', 500)->nullable();
            $table->string('file_pdf', 500)->nullable();
            $table->string('file_scan', 500)->nullable();
            $table->string('file_pendukung', 500)->nullable();
            $table->string('nama_file_pendukung')->nullable();
            $table->timestamp('generated_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pengajuan_surat');
    }
};
