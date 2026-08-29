<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

/**
 * Command untuk auto-replace teks statis dengan placeholder di template
 */
class AutoReplacePlaceholder extends Command
{
    protected $signature = 'template:auto-replace {jenis : aktif_kuliah|izin_magang|rekomendasi_magang|izin_penelitian|seminar_proposal|sidang_skripsi}';

    protected $description = 'Auto replace teks statis dengan placeholder di template';

    /**
     * Mapping dari jenis surat ke file template dan replacement rules
     */
    private const TEMPLATE_MAPPING = [
        'aktif_kuliah' => [
            'file' => 'aktif_kuliah_v1.docx',
            'replacements' => [
                // Header/Kop (opsional, bisa dikomentari jika sudah benar)
                'UNIVERSITAS SYIAH KUALA' => '${nama_universitas}',
                'FAKULTAS ILMU SOSIAL DAN ILMU POLITIK' => '${nama_fakultas}',
                'Darussalam, Banda Aceh 23111' => '${alamat_prodi}',
                'Telepon : (0651) 3617196, 7555267, 7555270' => 'Telepon : ${telepon_prodi}',
                'Laman : www.fisip.usk.ac.id, Surel : fisip@usk.ac.id' => 'Laman : ${email_prodi}',

                // Nomor surat
                '5176/UN11.F9 /PK.01.06/2025AL' => '${nomor_surat}',

                // Data mahasiswa (PENTING!)
                'Dhea Aldita Salsabila' => '${nama_mahasiswa}',
                '1910102010019' => '${nim}',
                'Ilmu Komunikasi' => '${nama_prodi}',
                'Desa Tambun Baroeh, Aceh Utara' => '${alamat_mahasiswa}',

                // Semester & tahun akademik
                'semester Ganjil Tahun Akademik 2025/2026' => 'semester ${semester_aktif} Tahun Akademik ${tahun_akademik}',

                // Keperluan
                'Beasiswa Sinergi' => '${keperluan}',

                // Tanggal & penanda tangan
                'Banda Aceh, 01 Oktober 2025' => '${kota_prodi}, ${tanggal_surat}',
                'Dr. Effendi Hasan, M.A' => '${nama_kaprodi}',
                '197510012009121005' => '${nip_kaprodi}',

                // Jabatan (opsional, tergantung struktur)
                'a.n. Dekan' => '',
                'Wakil Dekan Akademik,' => 'Kepala Program Studi,',
            ],
        ],
    ];

    public function handle(): int
    {
        $jenis = $this->argument('jenis');

        if (! isset(self::TEMPLATE_MAPPING[$jenis])) {
            $this->error("Jenis '{$jenis}' belum dikonfigurasi.");

            return self::FAILURE;
        }

        $config = self::TEMPLATE_MAPPING[$jenis];
        $fileName = $config['file'];
        $filePath = base_path("doc/{$fileName}");

        if (! File::exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");

            return self::FAILURE;
        }

        $this->info("Memproses: {$fileName}");

        try {
            Settings::setTempDir(sys_get_temp_dir());

            $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            if ($extension !== 'docx') {
                $this->error('Hanya support .docx. Convert dulu .doc ke .docx menggunakan Word.');

                return self::FAILURE;
            }

            // Backup original
            $backupPath = str_replace('.docx', '.backup.docx', $filePath);
            if (! File::exists($backupPath)) {
                File::copy($filePath, $backupPath);
                $this->line("✓ Backup dibuat: {$backupPath}");
            }

            // Load dengan TemplateProcessor (lebih stabil untuk find & replace)
            $templateProcessor = new \PhpOffice\PhpWord\TemplateProcessor($filePath);
            $replacements = $config['replacements'];

            $this->line('Melakukan penggantian...');

            // Lakukan replacement
            foreach ($replacements as $search => $replace) {
                // Escape special chars untuk pattern matching
                $searchPattern = str_replace(['$', '{', '}'], ['\$', '\{', '\}'], $search);

                // Cek apakah ada di template
                $content = file_get_contents($filePath);
                if (str_contains($content, $search)) {
                    // PHPWord Template Processor butuh exact match
                    // Kita pakai regex untuk lebih fleksibel
                    $tempContent = file_get_contents($filePath);
                    $newContent = str_replace($search, $replace, $tempContent);

                    if ($tempContent !== $newContent) {
                        file_put_contents($filePath, $newContent);
                        $this->line("  ✓ '{$search}' → '{$replace}'");
                    }
                }
            }

            $this->newLine();
            $this->info('✓ Template berhasil diupdate dengan placeholder!');
            $this->line("  File: {$fileName}");
            $this->line("  Backup: {$backupPath}");
            $this->newLine();
            $this->line('Jalankan: php artisan surat:buat-template '.$jenis);

        } catch (\Exception $e) {
            $this->error('Error: '.$e->getMessage());
            $this->error($e->getTraceAsString());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function replaceInContainer($container, array $replacements): void
    {
        foreach ($container->getElements() as $element) {
            $this->replaceInElement($element, $replacements);
        }
    }

    private function replaceInElement($element, array $replacements): void
    {
        $type = get_class($element);

        // TextRun
        if (method_exists($element, 'getText')) {
            $originalText = $element->getText();

            if (! empty($originalText)) {
                $newText = $originalText;

                foreach ($replacements as $search => $replace) {
                    if (str_contains($newText, $search)) {
                        $newText = str_replace($search, $replace, $newText);
                        $this->line("  ✓ '{$search}' → '{$replace}'");
                    }
                }

                if ($newText !== $originalText && method_exists($element, 'setText')) {
                    $element->setText($newText);
                }
            }
        }

        // Container dengan elements
        if (method_exists($element, 'getElements')) {
            $this->replaceInContainer($element, $replacements);
        }

        // Table
        if (class_basename($type) === 'Table') {
            foreach ($element->getRows() as $row) {
                foreach ($row->getCells() as $cell) {
                    $this->replaceInContainer($cell, $replacements);
                }
            }
        }
    }
}
