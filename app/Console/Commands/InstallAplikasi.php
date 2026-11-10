<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class InstallAplikasi extends Command
{
    protected $signature = 'aplikasi:install
                            {--admin-nama= : Nama lengkap admin}
                            {--admin-email= : Email admin}
                            {--admin-password= : Password admin}
                            {--kaprodi-nama= : Nama lengkap kaprodi}
                            {--kaprodi-email= : Email kaprodi}
                            {--kaprodi-password= : Password kaprodi}
                            {--force : Skip konfirmasi jika sudah pernah install}';

    protected $description = 'Instalasi awal aplikasi — buat akun admin dan kaprodi';

    public function handle(): int
    {
        // ── Banner ────────────────────────────────────────────────────────────
        $this->line('');
        $this->line('╔══════════════════════════════════════════════════════════════╗');
        $this->line('║   SISTEM ADMINISTRASI DIGITAL ILMU KOMUNIKASI               ║');
        $this->line('║   Installer — Instalasi Pertama                             ║');
        $this->line('╚══════════════════════════════════════════════════════════════╝');
        $this->line('');

        // ── Step 1: Cek sudah install ─────────────────────────────────────────
        if (Schema::hasTable('users') && User::exists() && ! $this->option('force')) {
            $this->warn('Aplikasi sepertinya sudah pernah diinstall.');
            if (! $this->confirm('Lanjutkan instalasi ulang? Data akun lama tidak akan dihapus.')) {
                return 0;
            }
            $this->line('');
        }

        // ── Step 2: Migrasi database ──────────────────────────────────────────
        $this->info('[1/5] Menjalankan migrasi database...');
        $this->call('migrate', ['--force' => true]);
        $this->info('✓ Migrasi berhasil');
        $this->line('');

        // ── Step 3: Seed pengaturan ───────────────────────────────────────────
        $this->info('[2/5] Mengisi pengaturan awal...');
        $this->call('db:seed', ['--class' => 'PengaturanSeeder', '--force' => true]);
        $this->info('✓ Pengaturan awal berhasil dibuat');
        $this->line('');

        // ── Step 4: Generate template surat ──────────────────────────────────
        $this->info('[3/5] Membuat template surat...');
        $this->call('surat:buat-template', ['jenis' => 'semua']);
        $this->info('✓ Template surat berhasil dibuat');
        $this->line('');

        // ── Step 5: Buat akun Admin ───────────────────────────────────────────
        $this->info('[4/5] Buat Akun Administrator');
        $emailAdmin = $this->buatAkun('admin');
        $this->info('✓ Akun Admin berhasil dibuat');
        $this->line('');

        // ── Step 6: Buat akun Kaprodi ─────────────────────────────────────────
        $this->info('[5/5] Buat Akun Kepala Program Studi');
        $emailKaprodi = $this->buatAkun('kaprodi', $emailAdmin);
        $this->info('✓ Akun Kaprodi berhasil dibuat');
        $this->line('');

        // ── Step 7: Selesai ───────────────────────────────────────────────────
        $this->line('════════════════════════════════════════════════════════════');
        $this->info('✓ Instalasi selesai!');
        $this->line('');
        $this->line("  Admin   : {$emailAdmin}");
        $this->line("  Kaprodi : {$emailKaprodi}");
        $this->line('');
        $this->line('  Langkah selanjutnya:');
        $this->line('  1. Login sebagai Admin dan isi Pengaturan Sistem');
        $this->line('     (nama prodi, nama kaprodi, kode institusi, dll)');
        $this->line('  2. Mahasiswa dan Dosen dapat diimport via menu Master Data');
        $this->line('════════════════════════════════════════════════════════════');
        $this->line('');

        return self::SUCCESS;
    }

    /**
     * Buat akun user — pakai option jika tersedia, fallback ke prompt interaktif.
     */
    private function buatAkun(string $role, ?string $emailLain = null): string
    {
        $namaOpt = $this->option("{$role}-nama");
        $emailOpt = $this->option("{$role}-email");
        $passwordOpt = $this->option("{$role}-password");

        // ── Mode non-interaktif (semua option tersedia) ───────────────────────
        if ($namaOpt && $emailOpt && $passwordOpt) {
            $this->validasiEmail($emailOpt, $emailLain, true);
            $this->validasiPassword($passwordOpt, true);

            User::create([
                'name' => $namaOpt,
                'email' => $emailOpt,
                'password' => Hash::make($passwordOpt),
                'role' => $role,
                'is_active' => true,
            ]);

            return $emailOpt;
        }

        // ── Mode interaktif (prompt) ──────────────────────────────────────────
        $label = $role === 'admin' ? 'admin' : 'kaprodi';

        while (true) {
            $nama = $namaOpt ?? $this->ask("Nama lengkap {$label}");
            $email = $emailOpt ?? $this->ask("Email {$label}");

            if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $this->error('✗ Format email tidak valid.');
                $namaOpt = null;
                $emailOpt = null;

                continue;
            }

            if ($emailLain !== null && $email === $emailLain) {
                $this->error('✗ Email tidak boleh sama dengan email Admin.');
                $emailOpt = null;

                continue;
            }

            if (User::where('email', $email)->exists()) {
                $this->error('✗ Email sudah terdaftar.');
                $emailOpt = null;

                continue;
            }

            $password = $passwordOpt ?? $this->secret('Password (min. 8 karakter)');
            $konfirmasi = $passwordOpt ?? $this->secret('Konfirmasi password');

            if (strlen($password) < 8) {
                $this->error('✗ Password minimal 8 karakter.');
                $passwordOpt = null;

                continue;
            }

            if ($password !== $konfirmasi) {
                $this->error('✗ Password dan konfirmasi tidak cocok.');
                $passwordOpt = null;

                continue;
            }

            User::create([
                'name' => $nama,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => $role,
                'is_active' => true,
            ]);

            return $email;
        }
    }

    /**
     * Validasi email untuk mode non-interaktif — throw error jika tidak valid.
     */
    private function validasiEmail(string $email, ?string $emailLain, bool $nonInteraktif): void
    {
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error("✗ Format email tidak valid: {$email}");
            exit(1);
        }

        if ($emailLain !== null && $email === $emailLain) {
            $this->error('✗ Email kaprodi tidak boleh sama dengan email admin.');
            exit(1);
        }

        if (User::where('email', $email)->exists()) {
            $this->error("✗ Email sudah terdaftar: {$email}");
            exit(1);
        }
    }

    /**
     * Validasi password untuk mode non-interaktif.
     */
    private function validasiPassword(string $password, bool $nonInteraktif): void
    {
        if (strlen($password) < 8) {
            $this->error('✗ Password minimal 8 karakter.');
            exit(1);
        }
    }
}
