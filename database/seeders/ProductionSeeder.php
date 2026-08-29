<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder untuk PRODUCTION — hanya akun admin dan kaprodi.
 * Tidak ada data dummy mahasiswa/dosen.
 *
 * Jalankan: php artisan db:seed --class=ProductionSeeder
 * Atau via DatabaseSeeder: php artisan db:seed
 *
 * Setelah deploy:
 * 1. Ganti password admin dan kaprodi lewat halaman Profil
 * 2. Isi Pengaturan (nama prodi, kaprodi, dll) lewat menu Pengaturan
 * 3. Generate template surat: php artisan surat:buat-template semua
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // Seed pengaturan default (semua kosong, diisi admin lewat UI)
        $this->call(PengaturanSeeder::class);

        // ── Akun Admin ────────────────────────────────────────────────────────
        // PENTING: Ganti password lewat halaman Profil setelah login pertama kali
        User::firstOrCreate(
            ['email' => 'admin@prodi.ac.id'],
            [
                'name' => 'Administrator',
                'password' => Hash::make('Admin@2025!'),
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        // ── Akun Kaprodi ──────────────────────────────────────────────────────
        // PENTING: Ganti nama dan password sesuai nama Kaprodi yang sesungguhnya
        User::firstOrCreate(
            ['email' => 'kaprodi@prodi.ac.id'],
            [
                'name' => 'Kepala Program Studi',
                'password' => Hash::make('Kaprodi@2025!'),
                'role' => 'kaprodi',
                'is_active' => true,
            ]
        );

        $this->command->info('✓ Akun admin dan kaprodi berhasil dibuat.');
        
        // ── Auto-generate Template Surat ──────────────────────────────────────
        $this->command->info('Generating template surat...');
        \Illuminate\Support\Facades\Artisan::call('surat:buat-template', ['jenis' => 'semua']);
        $this->command->info('✓ Template surat berhasil digenerate.');
        
        $this->command->newLine();
        $this->command->warn('  → Segera ganti password lewat halaman Profil!');
        $this->command->warn('  → Isi Pengaturan (nama prodi, kaprodi, dll) lewat menu Pengaturan.');
    }
}
