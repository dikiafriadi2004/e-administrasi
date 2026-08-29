<?php

namespace App\Console\Commands;

use App\Exceptions\SuratGenerationException;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Models\TemplateSurat;
use App\Services\SuratGeneratorService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\TemplateProcessor;

class TestGenerateSurat extends Command
{
    protected $signature = 'surat:test-generate
                            {jenis=aktif_kuliah : aktif_kuliah|seminar_proposal|sidang_skripsi|undangan_penguji}
                            {--panjang : Gunakan data dummy terpanjang (nama 50 karakter, judul 2 baris)}';

    protected $description = 'Uji generate surat end-to-end dengan data dummy (tidak disimpan ke DB production)';

    public function handle(SuratGeneratorService $generator): int
    {
        $jenis = $this->argument('jenis');
        $panjang = $this->option('panjang');

        $jenisValid = ['aktif_kuliah', 'seminar_proposal', 'sidang_skripsi', 'undangan_penguji'];
        if (! in_array($jenis, $jenisValid)) {
            $this->error("Jenis '{$jenis}' tidak valid. Pilih: ".implode(', ', $jenisValid));

            return self::FAILURE;
        }

        // Cek template aktif tersedia
        $template = TemplateSurat::aktif()->jenis($jenis)->first();
        if (! $template) {
            $this->error("Template aktif untuk '{$jenis}' tidak ditemukan. Jalankan: php artisan surat:buat-template {$jenis}");

            return self::FAILURE;
        }

        $this->info("Jenis surat  : {$jenis}");
        $this->info("Template     : {$template->path_file} (v{$template->versi})");
        $this->newLine();

        // ── Siapkan data dummy ────────────────────────────────────────────────
        $mahasiswa = Mahasiswa::with('user')->first();
        if (! $mahasiswa) {
            $this->error('Tidak ada data mahasiswa di database. Jalankan: php artisan db:seed');

            return self::FAILURE;
        }

        $namaDummy = $panjang
            ? 'Muhammad Abdurrahman Wahyudi Putra Prasetya'
            : $mahasiswa->user->name;

        $judulDummy = $panjang
            ? 'Implementasi Algoritma Machine Learning untuk Prediksi Kinerja Akademik Mahasiswa Berbasis Data Historis dan Faktor Sosial Ekonomi'
            : 'Sistem Informasi Akademik Berbasis Web';

        $dosen = Dosen::first();
        $penguji = Dosen::skip(1)->first() ?? $dosen;

        // Buat PengajuanJudul dummy (in-memory, tidak disimpan)
        $pengajuanJudul = new PengajuanJudul([
            'mahasiswa_id' => $mahasiswa->id,
            'judul' => $judulDummy,
            'bidang_kajian' => 'Rekayasa Perangkat Lunak',
            'dosen_pembimbing_id' => $dosen?->id,
            'status' => 'disetujui',
        ]);
        $pengajuanJudul->id = 9999;
        $pengajuanJudul->setRelation('dosenPembimbing', $dosen);

        // Data form sesuai jenis
        $dataForm = match ($jenis) {
            'aktif_kuliah' => [
                'keperluan' => 'pengajuan beasiswa berprestasi',
                'tujuan_instansi' => 'Badan Pengembangan Sumber Daya Manusia',
            ],
            'seminar_proposal', 'sidang_skripsi', 'undangan_penguji' => [
                'tanggal_rencana' => now()->addDays(7)->format('d F Y'),
                'waktu_rencana' => '09.00 WIB',
                'tempat' => 'Ruang Seminar Gedung A Lt. 3',
            ],
        };

        // Override nama jika panjang (user read-only saat di-set via relation)
        $mahasiswaDummy = clone $mahasiswa;
        if ($panjang) {
            $userDummy = clone $mahasiswa->user;
            $userDummy->name = $namaDummy;
            $mahasiswaDummy->setRelation('user', $userDummy);
        }

        // Buat PengajuanSurat dummy dengan ID unik agar folder tidak tabrakan
        $pengajuanSurat = new PengajuanSurat([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => $jenis,
            'data_form' => $dataForm,
            'status' => 'diajukan',
        ]);
        $pengajuanSurat->id = 99999;

        // Set relasi manual (tanpa DB)
        $pengajuanSurat->setRelation('mahasiswa', $mahasiswaDummy);
        $pengajuanSurat->setRelation('pengajuanJudul', $pengajuanJudul);
        $pengajuanSurat->setRelation('dosenPenguji', $penguji);

        // ── Generate ──────────────────────────────────────────────────────────
        $this->info('Memulai generate...');

        try {
            // Nomor surat dummy untuk testing
            $nomorDummy = '999/TEST/'.date('Y');
            $pengajuanSurat->nomor_surat = $nomorDummy;

            // Ambil template langsung
            $templateAbsPath = Storage::disk('private')->path($template->path_file);
            $outputDir = 'surat/test/'.$jenis;
            Storage::disk('private')->makeDirectory($outputDir);
            $outputDirAbs = Storage::disk('private')->path($outputDir);

            // Salin template
            $docxFilename = $jenis.'_test_'.date('Ymd_His').'.docx';
            $docxAbsPath = $outputDirAbs.DIRECTORY_SEPARATOR.$docxFilename;
            copy($templateAbsPath, $docxAbsPath);

            // Isi placeholder
            $placeholders = $generator->buildPlaceholders($pengajuanSurat);
            $processor = new TemplateProcessor($docxAbsPath);
            foreach ($placeholders as $key => $value) {
                try {
                    $processor->setValue($key, htmlspecialchars((string) $value));
                } catch (\Throwable) {
                    // placeholder tidak ada di template — skip
                }
            }
            $processor->saveAs($docxAbsPath);

            $this->line('  ✓ DOCX: '.$outputDir.'/'.$docxFilename);

            // Konversi PDF
            $this->line('  Mengkonversi ke PDF via LibreOffice...');
            $pdfAbsPath = $generator->convertToPdf($docxAbsPath);
            $pdfFilename = pathinfo($pdfAbsPath, PATHINFO_BASENAME);
            $this->line('  ✓ PDF : '.$outputDir.'/'.$pdfFilename);

        } catch (SuratGenerationException $e) {
            $this->error('Generate gagal: '.$e->getMessage());
            $this->warn('Pastikan LibreOffice terinstall. Di Linux VPS: sudo apt install libreoffice --no-install-recommends');
            $this->warn('Di Windows: isi path LibreOffice di menu Admin → Pengaturan Sistem.');

            return self::FAILURE;
        }

        // ── Ringkasan placeholder ─────────────────────────────────────────────
        $this->newLine();
        $this->info('Placeholder yang diisi:');
        $rows = [];
        foreach ($placeholders as $k => $v) {
            if ($v !== '') {
                $rows[] = ["\${$k}", mb_strimwidth($v, 0, 60, '...')];
            }
        }
        $this->table(['Placeholder', 'Nilai'], $rows);

        $this->newLine();
        $this->info('✅ Test generate selesai. Buka file di storage/app/private/'.$outputDir.'/');
        $this->line('   '.str_replace('/', DIRECTORY_SEPARATOR, storage_path('app/private/'.$outputDir)));

        return self::SUCCESS;
    }
}
