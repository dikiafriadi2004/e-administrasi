<?php

use App\Models\Dosen;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pengajuan_judul', function (Blueprint $table): void {
            $table->foreignId('dosen_pembimbing_2_id')
                ->nullable()
                ->after('dosen_pembimbing_id')
                ->constrained('dosens')
                ->nullOnDelete();
        });

        Schema::table('pengajuan_surat', function (Blueprint $table): void {
            $table->foreignId('dosen_penguji_2_id')
                ->nullable()
                ->after('dosen_penguji_id')
                ->constrained('dosens')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('pengajuan_judul', function (Blueprint $table): void {
            $table->dropForeignIdFor(Dosen::class, 'dosen_pembimbing_2_id');
            $table->dropColumn('dosen_pembimbing_2_id');
        });

        Schema::table('pengajuan_surat', function (Blueprint $table): void {
            $table->dropForeignIdFor(Dosen::class, 'dosen_penguji_2_id');
            $table->dropColumn('dosen_penguji_2_id');
        });
    }
};
