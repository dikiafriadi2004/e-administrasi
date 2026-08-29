<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $now = now();

        $rows = [
            [
                'key' => 'semester_aktif',
                'value' => 'Genap',
                'label' => 'Semester Aktif (Ganjil / Genap)',
                'grup' => 'akademik',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'tahun_akademik',
                'value' => '2025/2026',
                'label' => 'Tahun Akademik (contoh: 2025/2026)',
                'grup' => 'akademik',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ];

        foreach ($rows as $row) {
            DB::table('pengaturan')->upsert($row, ['key'], ['value', 'label', 'grup', 'updated_at']);
        }
    }

    public function down(): void
    {
        DB::table('pengaturan')->whereIn('key', ['semester_aktif', 'tahun_akademik'])->delete();
    }
};
