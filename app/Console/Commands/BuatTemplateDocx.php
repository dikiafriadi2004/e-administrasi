<?php

namespace App\Console\Commands;

use App\Models\TemplateSurat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

/**
 * Copy template .docx dari folder doc/ ke storage dan daftarkan ke database.
 *
 * Jenis yang tersedia:
 *   aktif_kuliah         — Surat Keterangan Aktif Kuliah (dari pengajuan mahasiswa)
 *   seminar_proposal     — Surat Pengantar Seminar Proposal (dari pengajuan mahasiswa)
 *   sidang_skripsi       — Surat Pengantar Sidang Skripsi (dari pengajuan mahasiswa)
 *   undangan_penguji     — Surat Undangan Dosen Penguji (dibuat langsung oleh admin)
 *   izin_magang          — Surat Izin Magang/PKL (dari pengajuan mahasiswa)
 *   rekomendasi_magang   — Surat Rekomendasi Magang (dari pengajuan mahasiswa)
 *   izin_penelitian      — Surat Izin Penelitian (dari pengajuan mahasiswa)
 *
 * Dijalankan: php artisan surat:buat-template [jenis|semua]
 */
class BuatTemplateDocx extends Command
{
    protected $signature = 'surat:buat-template {jenis? : aktif_kuliah|seminar_proposal|sidang_skripsi|undangan_penguji|izin_magang|rekomendasi_magang|izin_penelitian|semua}';

    protected $description = 'Copy file template dari doc/ ke storage dan daftarkan ke tabel templates_surat';

    private const JENIS_TERSEDIA = [
        'aktif_kuliah',
        'seminar_proposal',
        'sidang_skripsi',
        'undangan_penguji',
        'izin_magang',
        'rekomendasi_magang',
        'izin_penelitian',
    ];

    /**
     * Mapping dari jenis surat ke nama file di folder doc/
     * Prioritas: file *_ready.docx (sudah di-inject placeholder) > file asli > fallback _v1.*
     */
    private const DOC_MAPPING = [
        'aktif_kuliah'      => ['aktif_kuliah_ready.docx',      'Surat Aktif Kuliah.docx',                        'aktif_kuliah_v1.docx'],
        'seminar_proposal'  => ['seminar_proposal_ready.docx',  'Undangan Seminar.docx',      'seminar_proposal_v1.doc'],
        'sidang_skripsi'    => ['sidang_skripsi_ready.docx',    'undangan sidang prodi 2025.docx', 'sidang_skripsi_v1.doc'],
        'undangan_penguji'  => ['undangan_penguji_ready.docx',  'undangan sidang prodi 2025.docx', 'undangan_penguji_v1.doc'],
        'izin_magang'       => ['izin_magang_ready.docx',       'Surat izin magang.docx',         'izin_magang_v1.doc'],
        'rekomendasi_magang'=> ['rekomendasi_magang_ready.docx','Surat Rekomendasi Magang.docx',  'rekomendasi_magang_v1.doc'],
        'izin_penelitian'   => ['izin_penelitian_ready.docx',   'Izin penelitian Mahasiswa.docx', 'izin_penelitian_v1.doc'],
    ];

    public function handle(): int
    {
        $jenis = $this->argument('jenis') ?? 'semua';
        $daftar = $jenis === 'semua' ? self::JENIS_TERSEDIA : [$jenis];

        foreach ($daftar as $j) {
            if (! in_array($j, self::JENIS_TERSEDIA)) {
                $this->error("Jenis surat '{$j}' tidak dikenal.");

                continue;
            }

            $this->info("Menyalin template: {$j}...");

            try {
                $path = $this->copyTemplate($j);
                $this->daftarkanTemplate($j, $path);
                $this->line("  ✓ Tersimpan: {$path}");
            } catch (\Exception $e) {
                $this->error("  ✗ Gagal: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('Selesai. Semua template terdaftar di tabel templates_surat.');

        return self::SUCCESS;
    }

    /**
     * Copy template dari folder doc/ ke storage/app/private/templates/
     */
    private function copyTemplate(string $jenis): string
    {
        $sourceFileNames = self::DOC_MAPPING[$jenis] ?? null;

        if (! $sourceFileNames) {
            throw new \Exception("Tidak ada mapping file untuk jenis: {$jenis}");
        }

        // Cari file yang ada (prioritas pertama yang ditemukan)
        $sourcePath = null;
        $sourceFileName = null;

        foreach ($sourceFileNames as $fileName) {
            $path = base_path("doc/{$fileName}");
            if (File::exists($path)) {
                $sourcePath = $path;
                $sourceFileName = $fileName;
                break;
            }
        }

        if (! $sourcePath) {
            throw new \Exception("File tidak ditemukan untuk jenis {$jenis}. Dicoba: ".implode(', ', $sourceFileNames));
        }

        // Path tujuan di storage/app/private/templates/
        // Simpan dengan extension asli
        $extension = pathinfo($sourceFileName, PATHINFO_EXTENSION);
        $storagePath = "templates/{$jenis}_v1.{$extension}";

        // Pastikan folder templates ada
        $storageDir = Storage::disk('private')->path('templates');
        if (! File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        // Hapus file lama dengan extension berbeda (untuk bersihkan duplikasi)
        $this->cleanupOldTemplates($jenis, $extension);

        // Copy file
        $destinationPath = Storage::disk('private')->path($storagePath);
        File::copy($sourcePath, $destinationPath);

        return $storagePath;
    }

    /**
     * Hapus template lama dengan extension berbeda
     */
    private function cleanupOldTemplates(string $jenis, string $keepExtension): void
    {
        $extensions = ['doc', 'docx'];
        foreach ($extensions as $ext) {
            if ($ext === $keepExtension) {
                continue;
            }

            $oldPath = Storage::disk('private')->path("templates/{$jenis}_v1.{$ext}");
            if (File::exists($oldPath)) {
                File::delete($oldPath);
            }
        }
    }

    /**
     * Daftarkan template ke database
     */
    private function daftarkanTemplate(string $jenis, string $pathFile): void
    {
        TemplateSurat::updateOrCreate(
            ['jenis_surat' => $jenis],
            [
                'path_file' => $pathFile,
                'versi' => 1,
                'is_aktif' => true,
            ]
        );
    }
}
