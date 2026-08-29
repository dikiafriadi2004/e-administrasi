<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\Settings;

/**
 * Command untuk membaca template .doc/.docx dan menampilkan isinya
 * sehingga user bisa lihat struktur dan tahu dimana harus tambah placeholder
 */
class EditTemplatePlaceholder extends Command
{
    protected $signature = 'template:inspect {file : Nama file di folder doc/}';

    protected $description = 'Inspect isi template Word untuk melihat teks yang perlu diganti dengan placeholder';

    public function handle(): int
    {
        $fileName = $this->argument('file');
        $filePath = base_path("doc/{$fileName}");

        if (! File::exists($filePath)) {
            $this->error("File tidak ditemukan: {$filePath}");
            $this->line('File yang tersedia di folder doc/:');
            foreach (File::files(base_path('doc')) as $file) {
                $this->line("  - {$file->getFilename()}");
            }

            return self::FAILURE;
        }

        $this->info("Membaca file: {$fileName}");
        $this->newLine();

        try {
            // Set temp directory untuk PHPWord
            Settings::setTempDir(sys_get_temp_dir());

            $extension = pathinfo($filePath, PATHINFO_EXTENSION);

            if ($extension === 'docx') {
                $this->readDocx($filePath);
            } elseif ($extension === 'doc') {
                $this->warn('File .doc tidak bisa dibaca langsung oleh PHPWord.');
                $this->line('Solusi:');
                $this->line('1. Buka file dengan Microsoft Word');
                $this->line('2. Save As → pilih format .docx');
                $this->line('3. Jalankan command ini lagi dengan file .docx');
                $this->newLine();
                $this->line('Atau gunakan LibreOffice untuk convert:');
                $this->line("   soffice --headless --convert-to docx \"{$filePath}\" --outdir doc/");
            } else {
                $this->error('Format file tidak didukung. Hanya .docx yang bisa dibaca.');
            }
        } catch (\Exception $e) {
            $this->error('Error membaca file: '.$e->getMessage());

            return self::FAILURE;
        }

        return self::SUCCESS;
    }

    private function readDocx(string $filePath): void
    {
        $phpWord = IOFactory::load($filePath);

        $this->info('=== ISI DOKUMEN ===');
        $this->newLine();

        $sectionIndex = 0;
        foreach ($phpWord->getSections() as $section) {
            $sectionIndex++;
            $this->line("--- SECTION {$sectionIndex} ---");

            // Read header
            foreach ($section->getHeaders() as $headerIndex => $header) {
                $this->line("\n[HEADER {$headerIndex}]");
                $this->readElements($header->getElements());
            }

            // Read main content
            $this->line("\n[KONTEN UTAMA]");
            $this->readElements($section->getElements());

            // Read footer
            foreach ($section->getFooters() as $footerIndex => $footer) {
                $this->line("\n[FOOTER {$footerIndex}]");
                $this->readElements($footer->getElements());
            }

            $this->newLine();
        }

        $this->newLine();
        $this->info('=== SARAN PENGGANTIAN ===');
        $this->line('Berdasarkan isi di atas, ganti teks statis dengan placeholder:');
        $this->line('Contoh:');
        $this->line('  - Nama mahasiswa → ${nama_mahasiswa}');
        $this->line('  - NIM mahasiswa  → ${nim}');
        $this->line('  - Alamat         → ${alamat_mahasiswa}');
        $this->line('');
        $this->line('Lihat PANDUAN_EDIT_TEMPLATE.md untuk daftar lengkap placeholder.');
    }

    private function readElements(array $elements, int $depth = 0): void
    {
        $indent = str_repeat('  ', $depth);

        foreach ($elements as $element) {
            $type = get_class($element);
            $typeName = class_basename($type);

            if (method_exists($element, 'getText')) {
                $text = $element->getText();
                if (! empty($text)) {
                    $this->line("{$indent}[{$typeName}] {$text}");
                }
            } elseif (method_exists($element, 'getElements')) {
                $this->line("{$indent}[{$typeName}]");
                $this->readElements($element->getElements(), $depth + 1);
            } elseif ($typeName === 'Table') {
                $this->line("{$indent}[TABEL]");
                $this->readTable($element, $depth + 1);
            } else {
                $this->line("{$indent}[{$typeName}]");
            }
        }
    }

    private function readTable($table, int $depth): void
    {
        $indent = str_repeat('  ', $depth);

        foreach ($table->getRows() as $rowIndex => $row) {
            $this->line("{$indent}[ROW {$rowIndex}]");
            foreach ($row->getCells() as $cellIndex => $cell) {
                $this->line("{$indent}  [CELL {$cellIndex}]");
                $this->readElements($cell->getElements(), $depth + 2);
            }
        }
    }
}
