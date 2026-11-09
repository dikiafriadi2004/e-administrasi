<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * ProductionSeeder — TIDAK LAGI DIGUNAKAN SECARA AKTIF
 * =====================================================
 *
 * Instalasi aplikasi kini dilakukan melalui perintah Artisan interaktif:
 *
 *   php artisan aplikasi:install
 *
 * Perintah tersebut akan:
 *   1. Menjalankan migrasi database (migrate --force)
 *   2. Mengisi pengaturan awal via PengaturanSeeder
 *   3. Membuat template surat (surat:buat-template semua)
 *   4. Meminta input nama, email, dan password untuk akun Admin
 *   5. Meminta input nama, email, dan password untuk akun Kaprodi
 *
 * Dengan pendekatan ini, kredensial tidak pernah di-hardcode di seeder
 * sehingga lebih aman untuk deployment ke lingkungan produksi.
 *
 * File ini dipertahankan agar referensi lama (dokumentasi, CI lama) tidak
 * langsung error, tetapi method run() sengaja dikosongkan.
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // Kosong — gunakan: php artisan aplikasi:install
    }
}
