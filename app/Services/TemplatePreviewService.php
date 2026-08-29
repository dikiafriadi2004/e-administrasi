<?php

namespace App\Services;

use App\Models\Pengaturan;
use App\Models\TemplateSurat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\TemplateProcessor;

/**
 * TemplatePreviewService
 *
 * Render preview HTML dari template DOCX aktif untuk jenis surat tertentu.
 * Placeholder diisi dari data yang dikirim form (real-time), sehingga
 * preview selalu sinkron dengan template yang sedang aktif di server.
 */
class TemplatePreviewService
{
    /**
     * Render template DOCX ke HTML dengan placeholder sudah diisi.
     *
     * @param  array<string, string>  $data  Nilai placeholder dari form
     */
    public function render(string $jenisSurat, array $data): string
    {
        $template = TemplateSurat::aktif()->jenis($jenisSurat)->first();

        if (! $template) {
            return $this->htmlTidakAda($jenisSurat);
        }

        $templatePath = Storage::disk('private')->path($template->path_file);

        if (! file_exists($templatePath)) {
            return $this->htmlTidakAda($jenisSurat);
        }

        // Lengkapi placeholder yang belum diisi dengan nilai institusi dari Pengaturan
        $placeholders = $this->mergePlaceholders($data);

        // Salin template ke file temp, isi placeholder
        $tmpPath = sys_get_temp_dir().DIRECTORY_SEPARATOR.'preview_'.Str::random(12).'.docx';
        copy($templatePath, $tmpPath);

        $processor = new TemplateProcessor($tmpPath);

        foreach ($placeholders as $key => $value) {
            try {
                $processor->setValue($key, htmlspecialchars((string) $value));
            } catch (\Throwable) {
                // Placeholder tidak ada di template — skip
            }
        }

        $processor->saveAs($tmpPath);

        // Load hasil docx dan convert ke HTML
        $phpWord = IOFactory::load($tmpPath, 'Word2007');
        $writer = IOFactory::createWriter($phpWord, 'HTML');

        ob_start();
        $writer->save('php://output');
        $fullHtml = ob_get_clean();

        // Hapus temp file
        @unlink($tmpPath);

        // Ambil body + render bersih tanpa kop hardcode
        return $this->extractBody($fullHtml);
    }

    /**
     * Lengkapi nilai placeholder yang kosong dengan nilai dari Pengaturan DB.
     *
     * @param  array<string, string>  $data
     * @return array<string, string>
     */
    private function mergePlaceholders(array $data): array
    {
        $kota = Pengaturan::nilai('kota_prodi', '');
        // tanggal_surat HANYA berisi tanggal — kota ditaruh di kota_prodi terpisah
        $tanggalSurat = Carbon::now()->locale('id')->isoFormat('D MMMM Y');

        $defaults = [
            'nama_universitas' => Pengaturan::nilai('nama_universitas', ''),
            'nama_fakultas' => Pengaturan::nilai('nama_fakultas', ''),
            'nama_prodi' => Pengaturan::nilai('nama_prodi', ''),
            'alamat_prodi' => Pengaturan::nilai('alamat_prodi', ''),
            'telepon_prodi' => Pengaturan::nilai('telepon_prodi', ''),
            'email_prodi' => Pengaturan::nilai('email_prodi', ''),
            'kota_prodi' => $kota,
            'nama_kaprodi' => Pengaturan::nilai('nama_kaprodi', ''),
            'nip_kaprodi' => Pengaturan::nilai('nip_kaprodi', ''),
            'nama_dekan' => Pengaturan::nilai('nama_dekan', ''),
            'nip_dekan' => Pengaturan::nilai('nip_dekan', ''),
            'tanggal_surat' => $tanggalSurat,
            // ── Komponen nomor surat terpisah ─────────────────────────────────
            'nomor_urut' => '',  // diisi admin saat generate
            'kode_institusi' => Pengaturan::nilai('kode_institusi', ''),
            'kode_prodi' => Pengaturan::nilai('kode_prodi', ''),
            'bulan_surat' => now()->format('m'),
            'tahun_surat' => now()->format('Y'),
            // Placeholder kosong untuk field yang belum diisi form
            'nomor_surat' => '',
            'nama_mahasiswa' => '',
            'nim' => '',
            'angkatan' => '',
            'alamat_mahasiswa' => '',
            'keperluan' => '',
            'tujuan_instansi' => '',
            'tujuan_instansi' => '',
            'semester_aktif' => Pengaturan::nilai('semester_aktif', ''),
            'tahun_akademik' => Pengaturan::nilai('tahun_akademik', ''),
            'judul_skripsi' => '',
            'bidang_kajian' => '',
            'nama_pembimbing' => '',
            'nip_pembimbing' => '',
            'nama_pembimbing_1' => '',
            'nip_pembimbing_1' => '',
            'nama_pembimbing_2' => '',
            'nip_pembimbing_2' => '',
            'nama_penguji' => '',
            'nip_penguji' => '',
            'nama_penguji_1' => '',
            'nip_penguji_1' => '',
            'nama_penguji_2' => '',
            'nip_penguji_2' => '',
            'tanggal_seminar' => '',
            'tanggal_sidang' => '',
            'waktu_sidang' => '',
            'tempat_sidang' => '',
            // Magang & penelitian
            'nama_instansi' => '',
            'alamat_instansi' => '',
            'tanggal_mulai' => '',
            'tanggal_selesai' => '',
            'judul_penelitian' => '',
            'bidang_penelitian' => '',
        ];

        // Data dari form override defaults
        return array_merge($defaults, array_filter($data, fn ($v) => $v !== null && $v !== ''));
    }

    /**
     * Ekstrak isi <body> dari HTML lengkap yang dihasilkan PhpWord dan
     * bungkus dengan CSS minimal agar tampil seperti halaman A4.
     * Kop surat, footer, dan konten sepenuhnya berasal dari template DOCX
     * yang sudah diisi placeholder — tidak ada injeksi hardcode dari PHP.
     *
     * @param  array<string, string>  $placeholders
     */
    private function extractBody(string $fullHtml, array $placeholders = []): string
    {
        if (preg_match('/<body[^>]*>(.*?)<\/body>/si', $fullHtml, $m)) {
            $body = $m[1];
        } else {
            $body = $fullHtml;
        }

        $body = trim($body);

        return <<<HTML
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                * { box-sizing: border-box; margin: 0; padding: 0; }
                html, body {
                    background: #f3f4f6;
                    font-family: 'Times New Roman', Times, serif;
                    font-size: 12pt;
                    color: #000;
                }
                .page {
                    width: 210mm;
                    min-height: 297mm;
                    margin: 0 auto;
                    background: #fff;
                    padding: 20mm 22mm 20mm 28mm;
                    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
                }
                table { border-collapse: collapse; width: 100%; }
                td, th { vertical-align: top; }
                p { margin-bottom: 6pt; line-height: 1.5; }
                .phpword-footnotes, .phpword-endnotes { display: none; }
            </style>
        </head>
        <body>
            <div class="page">
                {$body}
            </div>
        </body>
        </html>
        HTML;
    }

    /**
     * Fallback HTML jika template tidak ditemukan.
     */
    private function htmlTidakAda(string $jenisSurat): string
    {
        return <<<HTML
        <!DOCTYPE html>
        <html lang="id">
        <head><meta charset="UTF-8"><style>
            body { font-family: sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; background: #f9fafb; }
            .box { text-align: center; color: #6b7280; padding: 32px; }
            .icon { font-size: 48px; margin-bottom: 12px; }
        </style></head>
        <body>
            <div class="box">
                <div class="icon">📄</div>
                <p style="font-size:14px; font-weight:600; color:#374151;">Template belum tersedia</p>
                <p style="font-size:12px; margin-top:6px;">Upload template <strong>{$jenisSurat}</strong> di menu Template Surat</p>
            </div>
        </body>
        </html>
        HTML;
    }
}
