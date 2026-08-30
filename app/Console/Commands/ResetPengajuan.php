<?php

namespace App\Console\Commands;

use App\Models\BerkasPengajuan;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Models\StatusHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Reset semua data pengajuan — hapus mahasiswa dan pengajuan, tapi
 * pertahankan: pengaturan, dosen, akun admin & kaprodi, template surat.
 *
 * Usage: php artisan app:reset-pengajuan
 * Konfirmasi diperlukan di production.
 */
class ResetPengajuan extends Command
{
    protected $signature = 'app:reset-pengajuan {--force : Skip confirmation prompt}';

    protected $description = 'Reset semua data pengajuan mahasiswa. Pengaturan, dosen, dan akun admin/kaprodi TIDAK dihapus.';

    public function handle(): int
    {
        $this->warn('==================================================');
        $this->warn('  RESET DATA PENGAJUAN');
        $this->warn('==================================================');
        $this->line('Yang akan DIHAPUS:');
        $this->line('  - Semua data pengajuan judul, seminar, sidang, surat');
        $this->line('  - Semua berkas yang diupload (storage/app/private)');
        $this->line('  - Semua akun mahasiswa dan data profilnya');
        $this->line('  - Riwayat status (status_histories)');
        $this->line('  - Counter nomor surat');
        $this->line('');
        $this->line('Yang akan DIPERTAHANKAN:');
        $this->line('  - Pengaturan sistem (nama prodi, kaprodi, dll)');
        $this->line('  - Data dosen (nama, NIP, kapasitas)');
        $this->line('  - Akun admin dan kaprodi');
        $this->line('  - Template surat');
        $this->line('');

        if (! $this->option('force') && ! $this->confirm('Lanjutkan reset? Tindakan ini tidak bisa dibatalkan.', false)) {
            $this->info('Reset dibatalkan.');

            return self::SUCCESS;
        }

        $this->info('Memulai reset...');

        DB::transaction(function () {
            // 1. Hapus semua berkas (path di storage) sebelum hapus record
            $this->hapusFileStorage();

            // 2. Hapus berkas pengajuan (polymorphic)
            $this->line('  Menghapus berkas_pengajuan...');
            BerkasPengajuan::truncate();

            // 3. Hapus status histories pengajuan
            $this->line('  Menghapus status_histories...');
            StatusHistory::truncate();

            // 4. Hapus pengajuan surat
            $this->line('  Menghapus pengajuan_surat...');
            PengajuanSurat::truncate();

            // 5. Hapus pengajuan judul
            $this->line('  Menghapus pengajuan_judul...');
            PengajuanJudul::truncate();

            // 6. Hapus data mahasiswa
            $this->line('  Menghapus mahasiswas...');
            DB::table('mahasiswas')->truncate();

            // 7. Hapus akun mahasiswa (role = mahasiswa)
            $this->line('  Menghapus users mahasiswa...');
            DB::table('users')->where('role', 'mahasiswa')->delete();

            // 8. Reset counter nomor surat
            $this->line('  Reset nomor_surat_counters...');
            DB::table('nomor_surat_counters')->truncate();

            // 9. Reset sessions dan cache terkait
            DB::table('sessions')->truncate();
            DB::table('cache')->truncate();
        });

        // 10. Bersihkan file storage di luar transaksi
        $this->bersihkanFolderStorage();

        $this->newLine();
        $this->info('✓ Reset selesai!');
        $this->info('  Pengaturan, dosen, admin, kaprodi, dan template surat dipertahankan.');
        $this->info('  Silakan login kembali dan mulai dari awal.');

        return self::SUCCESS;
    }

    private function hapusFileStorage(): void
    {
        $this->line('  Menghapus file dari storage...');

        // Hapus file yang tercatat di database
        $paths = collect([
            PengajuanSurat::pluck('file_docx'),
            PengajuanSurat::pluck('file_pdf'),
            PengajuanSurat::pluck('file_scan'),
            PengajuanSurat::pluck('file_absensi_seminar'),
            BerkasPengajuan::pluck('path_file'),
        ])->flatten()->filter()->unique();

        $deleted = 0;
        foreach ($paths as $path) {
            if (Storage::disk('private')->exists($path)) {
                Storage::disk('private')->delete($path);
                $deleted++;
            }
        }

        $this->line("    → {$deleted} file dihapus dari database record");
    }

    private function bersihkanFolderStorage(): void
    {
        $this->line('  Membersihkan folder storage...');

        // Folder yang dibersihkan (hapus semua isi, bukan foldernya)
        $folders = ['surat', 'undangan', 'berkas', 'scan', 'absensi', 'pendukung'];

        foreach ($folders as $folder) {
            if (Storage::disk('private')->exists($folder)) {
                // Hapus semua direktori di dalam folder (per NIM/ID)
                $subdirs = Storage::disk('private')->directories($folder);
                foreach ($subdirs as $subdir) {
                    Storage::disk('private')->deleteDirectory($subdir);
                }
                // Hapus file langsung di root folder jika ada
                $files = Storage::disk('private')->files($folder);
                foreach ($files as $file) {
                    Storage::disk('private')->delete($file);
                }
                $this->line("    → {$folder}/ dibersihkan");
            }
        }

        // Bersihkan livewire-tmp
        if (Storage::disk('private')->exists('livewire-tmp')) {
            foreach (Storage::disk('private')->files('livewire-tmp') as $f) {
                Storage::disk('private')->delete($f);
            }
        }
    }
}
