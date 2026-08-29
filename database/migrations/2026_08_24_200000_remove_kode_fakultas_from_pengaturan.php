<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Hapus kode_fakultas dari pengaturan — sudah tidak dipakai di format nomor surat.
        // Format baru: {urutan}/{kode_institusi}/{kode_prodi}/{bulan_angka}/{tahun}
        DB::table('pengaturan')->where('key', 'kode_fakultas')->delete();
    }

    public function down(): void
    {
        DB::table('pengaturan')->insertOrIgnore([
            'key' => 'kode_fakultas',
            'label' => 'Kode Fakultas (untuk nomor surat)',
            'grup' => 'penomoran',
            'value' => '',
        ]);
    }
};
