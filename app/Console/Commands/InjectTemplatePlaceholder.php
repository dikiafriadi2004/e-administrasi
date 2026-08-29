<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

/**
 * Inject placeholder ke file .docx dengan cara:
 *  1. Buka docx sebagai ZIP
 *  2. Parse word/document.xml sebagai DOM
 *  3. Per paragraf: ekstrak teks gabungan dari semua <w:r>, replace, lalu
 *     tulis hasilnya ke <w:t> di run pertama dan hapus run lainnya
 *  4. Simpan kembali ke ZIP
 */
class InjectTemplatePlaceholder extends Command
{
    protected $signature = 'template:inject {jenis : aktif_kuliah|seminar_proposal|sidang_skripsi|undangan_penguji|izin_magang|rekomendasi_magang|izin_penelitian|keluar_prodi|semua}';

    protected $description = 'Inject placeholder ke template .docx dari folder doc/';

    private const SOURCE_FILES = [
        'aktif_kuliah'       => 'Surat Aktif Kuliah.docx',
        'seminar_proposal'   => 'Undangan Seminar.docx',
        'sidang_skripsi'     => 'undangan sidang prodi 2025.docx',
        'undangan_penguji'   => 'undangan sidang prodi 2025.docx',
        'izin_magang'        => 'Surat izin magang.docx',
        'rekomendasi_magang' => 'Surat Rekomendasi Magang.docx',
        'izin_penelitian'    => 'Izin penelitian Mahasiswa.docx',
        'keluar_prodi'       => 'Surat Keluar Prodi.docx',
    ];

    // Teks asli (setelah flatten) → placeholder
    private const REPLACEMENTS = [
        '_UNIVERSAL_' => [
            'UNIVERSITAS SYIAH KUALA'               => '${nama_universitas}',
            'FAKULTAS ILMU SOSIAL DAN ILMU POLITIK' => '${nama_fakultas}',
            'Fakultas Ilmu Sosial dan Ilmu Politik'  => '${nama_fakultas}',
            'Darussalam, Banda Aceh 23111'           => '${alamat_prodi}',
            '(0651) 3617196, 7555267, 7555270'       => '${telepon_prodi}',
            'www.fisip.usk.ac.id, Surel : fisip@usk.ac.id' => '${email_prodi}',
            'www.fisip.usk.ac.id'                    => '${email_prodi}',
            'Dr. Effendi Hasan, M.A'                 => '${nama_kaprodi}',
            'Dr. Effendi Hasan, MA'                  => '${nama_kaprodi}',
            'NIP 197510012009121005'                 => 'NIP. ${nip_kaprodi}',
            '197510012009121005'                     => '${nip_kaprodi}',
            'a.n. Dekan'                             => '',
            'Wakil Dekan Akademik,'                  => 'Kepala Program Studi,',
            'Wakil Dekan Bidang Akademik,'           => 'Kepala Program Studi,',
            'Banda Aceh, 01 Oktober 2025'            => '${kota_prodi}, ${tanggal_surat}',
            'Banda Aceh,  01 Oktober 2025'           => '${kota_prodi}, ${tanggal_surat}',
        ],
        'aktif_kuliah' => [
            // Nomor surat — match teks setelah flatten (ada spasi di depan dari tab)
            '5176/UN11.F9 /PK.01.06/2025AL'         => '${nomor_surat}',
            '5176/UN11.F9/PK.01.06/2025AL'          => '${nomor_surat}',
            'Nomor : 5176'                           => 'Nomor : ${nomor_surat}',
            'Dhea Aldita Salsabila'                  => '${nama_mahasiswa}',
            // NIM — replace dalam konteks "NPM: NIM" dulu agar tidak salah replace
            ': 1910102010019'                        => ': ${nim}',
            '1910102010019'                          => '${nim}',
            'NPM'                                    => 'NIM',
            'Ilmu Komunikasi'                        => '${nama_prodi}',
            'Desa Tambun Baroeh, Aceh Utara'         => '${alamat_mahasiswa}',
            'Program Studi Ilmu Komunikasi  Fakultas Ilmu Sosial dan Ilmu Politik Universitas Syiah Kuala, terdaftar pada semester Ganjil Tahun Akademik 2025/2026.'
                => 'Program Studi ${nama_prodi} ${nama_fakultas} ${nama_universitas}, terdaftar pada semester ${semester_aktif} Tahun Akademik ${tahun_akademik}.',
            'semester Ganjil Tahun Akademik 2025/2026' => 'semester ${semester_aktif} Tahun Akademik ${tahun_akademik}',
            'Beasiswa Sinergi'                       => '${keperluan}',
            'Universitas Syiah Kuala'                => '${nama_universitas}',
        ],
        'izin_magang' => [
            'PT. Nama Perusahaan'                    => '${nama_instansi}',
            'Jl. Contoh No. 1, Kota'                 => '${alamat_instansi}',
            'Nama Mahasiswa'                         => '${nama_mahasiswa}',
            '2021001001'                             => '${nim}',
            'Ilmu Komunikasi'                        => '${nama_prodi}',
            'Semester 7'                             => 'Semester ${semester_aktif}',
            '01 September 2025'                      => '${tanggal_mulai}',
            '31 Desember 2025'                       => '${tanggal_selesai}',
            'Universitas Syiah Kuala'                => '${nama_universitas}',
        ],
        'rekomendasi_magang' => [
            'PT. Nama Perusahaan'                    => '${nama_instansi}',
            'Jl. Contoh No. 1, Kota'                 => '${alamat_instansi}',
            'Nama Mahasiswa'                         => '${nama_mahasiswa}',
            '2021001001'                             => '${nim}',
            'Ilmu Komunikasi'                        => '${nama_prodi}',
            'Universitas Syiah Kuala'                => '${nama_universitas}',
        ],
        'izin_penelitian' => [
            'Dinas / Lembaga Tujuan'                 => '${nama_instansi}',
            'Jl. Contoh No. 1, Kota'                 => '${alamat_instansi}',
            'Nama Mahasiswa'                         => '${nama_mahasiswa}',
            '2021001001'                             => '${nim}',
            'Ilmu Komunikasi'                        => '${nama_prodi}',
            'Judul Penelitian / Skripsi Mahasiswa'   => '${judul_penelitian}',
            'Ilmu Komunikasi / Kebijakan Publik'     => '${bidang_penelitian}',
            '01 September 2025'                      => '${tanggal_mulai}',
            '31 Desember 2025'                       => '${tanggal_selesai}',
            'Universitas Syiah Kuala'                => '${nama_universitas}',
        ],
        'seminar_proposal' => [
            'Nama Mahasiswa'                         => '${nama_mahasiswa}',
            '2021001001'                             => '${nim}',
            'Ilmu Komunikasi'                        => '${nama_prodi}',
            'Judul Proposal Skripsi'                 => '${judul_skripsi}',
            'Dr. Pembimbing Satu, M.Si'              => '${nama_pembimbing_1}',
            'Dr. Pembimbing Dua, M.Si'               => '${nama_pembimbing_2}',
            'Dr. Penguji Satu, M.Si'                 => '${nama_penguji_1}',
            'Dr. Penguji Dua, M.Si'                  => '${nama_penguji_2}',
            'Senin, 01 September 2025'               => '${tanggal_seminar}',
            '09.00 - 11.00 WITA'                     => '${waktu_sidang}',
            'Ruang Seminar'                          => '${tempat_sidang}',
            'Universitas Syiah Kuala'                => '${nama_universitas}',
        ],
        'sidang_skripsi' => [
            'Nama Mahasiswa'                         => '${nama_mahasiswa}',
            '2021001001'                             => '${nim}',
            'Ilmu Komunikasi'                        => '${nama_prodi}',
            'Judul Skripsi Mahasiswa'                => '${judul_skripsi}',
            'Dr. Pembimbing Satu, M.Si'              => '${nama_pembimbing_1}',
            'Dr. Pembimbing Dua, M.Si'               => '${nama_pembimbing_2}',
            'Dr. Penguji Satu, M.Si'                 => '${nama_penguji_1}',
            'Dr. Penguji Dua, M.Si'                  => '${nama_penguji_2}',
            'Senin, 01 September 2025'               => '${tanggal_sidang}',
            '09.00 - 11.00 WITA'                     => '${waktu_sidang}',
            'Ruang Sidang'                           => '${tempat_sidang}',
            'Universitas Syiah Kuala'                => '${nama_universitas}',
        ],
        'undangan_penguji' => [
            'Nama Mahasiswa'                         => '${nama_mahasiswa}',
            '2021001001'                             => '${nim}',
            'Ilmu Komunikasi'                        => '${nama_prodi}',
            'Judul Skripsi Mahasiswa'                => '${judul_skripsi}',
            'Dr. Pembimbing Satu, M.Si'              => '${nama_pembimbing_1}',
            'Dr. Pembimbing Dua, M.Si'               => '${nama_pembimbing_2}',
            'Dr. Penguji Satu, M.Si'                 => '${nama_penguji_1}',
            'Dr. Penguji Dua, M.Si'                  => '${nama_penguji_2}',
            'Senin, 01 September 2025'               => '${tanggal_sidang}',
            '09.00 - 11.00 WITA'                     => '${waktu_sidang}',
            'Ruang Sidang'                           => '${tempat_sidang}',
            'Universitas Syiah Kuala'                => '${nama_universitas}',
        ],
        'keluar_prodi' => [
            'Nama Mahasiswa'                         => '${nama_mahasiswa}',
            '2021001001'                             => '${nim}',
            'Ilmu Komunikasi'                        => '${nama_prodi}',
            'Universitas Syiah Kuala'                => '${nama_universitas}',
        ],
    ];

    public function handle(): int
    {
        $jenis  = $this->argument('jenis');
        $daftar = $jenis === 'semua' ? array_keys(self::SOURCE_FILES) : [$jenis];

        foreach ($daftar as $j) {
            $this->info("\n── Memproses: {$j}");
            $this->processJenis($j);
        }

        $this->newLine();
        $this->info('Selesai. Jalankan: php artisan surat:buat-template semua');

        return self::SUCCESS;
    }

    private function processJenis(string $jenis): void
    {
        $docDir = base_path('doc');

        // Cari file sumber — nama asli dulu, fallback ke *_v1.*
        $srcPath = $this->findSourceFile($jenis, $docDir);

        if (! $srcPath) {
            $this->error("  File tidak ditemukan untuk: {$jenis}");

            return;
        }

        if (strtolower(pathinfo($srcPath, PATHINFO_EXTENSION)) === 'doc') {
            $this->warn('  File .doc tidak bisa diproses. Convert ke .docx dengan Word lalu jalankan ulang.');

            return;
        }

        $outputName = $jenis.'_ready.docx';
        $outputPath = $docDir.DIRECTORY_SEPARATOR.$outputName;

        File::copy($srcPath, $outputPath);

        $count = $this->processDocx($outputPath, $jenis);

        if ($count > 0) {
            $this->line("  ✓ {$count} replacement → {$outputName}");
        } else {
            $this->warn("  ⚠ Tidak ada teks cocok. Periksa REPLACEMENTS atau buka file asli untuk cek teks.");
        }
    }

    private function findSourceFile(string $jenis, string $docDir): ?string
    {
        // Prioritas: nama asli, lalu fallback _v1.*
        $candidates = [
            self::SOURCE_FILES[$jenis] ?? null,
            "{$jenis}_v1.docx",
            "{$jenis}_v1.doc",
        ];

        foreach ($candidates as $name) {
            if (! $name) {
                continue;
            }
            $path = $docDir.DIRECTORY_SEPARATOR.$name;
            if (File::exists($path)) {
                return $path;
            }
        }

        return null;
    }

    /**
     * Proses file .docx:
     *  - Buka sebagai ZIP
     *  - Per file XML di dalamnya: flatten paragraf, replace, tulis balik
     */
    private function processDocx(string $path, string $jenis): int
    {
        $zip = new ZipArchive;
        if ($zip->open($path) !== true) {
            $this->error("  Tidak bisa buka ZIP: {$path}");

            return 0;
        }

        $replacements = array_merge(
            self::REPLACEMENTS['_UNIVERSAL_'] ?? [],
            self::REPLACEMENTS[$jenis] ?? []
        );

        $xmlParts = ['word/document.xml'];
        for ($i = 1; $i <= 3; $i++) {
            foreach (['header', 'footer'] as $t) {
                if ($zip->locateName("word/{$t}{$i}.xml") !== false) {
                    $xmlParts[] = "word/{$t}{$i}.xml";
                }
            }
        }

        $total = 0;

        foreach ($xmlParts as $part) {
            $idx = $zip->locateName($part);
            if ($idx === false) {
                continue;
            }
            $xml = $zip->getFromIndex($idx);
            if (! $xml) {
                continue;
            }

            [$newXml, $n] = $this->processXml($xml, $replacements);

            if ($n > 0) {
                $zip->addFromString($part, $newXml);
                $total += $n;
                $this->line("    [{$part}] {$n} penggantian");
            }
        }

        $zip->close();

        return $total;
    }

    /**
     * Proses satu file XML dari docx:
     *  1. Per paragraf <w:p>: kumpulkan semua teks dari <w:t> menjadi string gabungan
     *  2. Jalankan replacement di string gabungan
     *  3. Jika ada perubahan: hapus semua <w:t> dan tulis ulang ke run pertama
     *
     * @return array{string, int}
     */
    private function processXml(string $xml, array $replacements): array
    {
        $total = 0;

        // Proses per paragraf
        $newXml = preg_replace_callback(
            '/(<w:p[ >])(.*?)(<\/w:p>)/s',
            function (array $m) use ($replacements, &$total) {
                [$changed, $n] = $this->processParagraph($m[0], $replacements);
                $total += $n;

                return $changed;
            },
            $xml
        );

        return [$newXml ?? $xml, $total];
    }

    /**
     * Proses satu <w:p>:
     *  - Ekstrak teks gabungan dari semua <w:t>
     *  - Replace teks
     *  - Jika berubah: hapus semua <w:t> lama, tulis teks baru ke <w:t> di run pertama
     *
     * @return array{string, int}
     */
    private function processParagraph(string $para, array $replacements): array
    {
        // Kumpulkan semua <w:t>...</w:t> (dengan posisinya)
        preg_match_all('/<w:t(?:[^>]*)>(.*?)<\/w:t>/s', $para, $tMatches, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        if (empty($tMatches)) {
            return [$para, 0];
        }

        // Gabungkan semua teks (decode entity)
        $combinedText = '';
        foreach ($tMatches as $m) {
            $combinedText .= html_entity_decode($m[1][0], ENT_XML1 | ENT_QUOTES, 'UTF-8');
        }

        // Jalankan replacement
        $newText = $combinedText;
        $count   = 0;

        foreach ($replacements as $search => $replace) {
            if ($search !== '' && str_contains($newText, $search)) {
                $newText = str_replace($search, $replace, $newText);
                $count++;
            }
        }

        if ($count === 0 || $newText === $combinedText) {
            return [$para, 0];
        }

        // Hapus semua <w:t>...</w:t> dari paragraf
        $newPara = preg_replace('/<w:t(?:[^>]*)>.*?<\/w:t>/s', '', $para);

        // Sisipkan teks baru ke <w:t> pertama setelah </w:rPr> atau setelah <w:r>
        // Cari run pertama yang memiliki <w:t> (sudah dihapus), lalu sisipkan sebelum </w:r>
        $encoded  = $this->encodeXmlKeepPlaceholders($newText);
        $hasSpace = str_contains($newText, ' ');
        $tTag     = $hasSpace
            ? "<w:t xml:space=\"preserve\">{$encoded}</w:t>"
            : "<w:t>{$encoded}</w:t>";

        // Sisipkan di run pertama yang ada — sebelum </w:r> pertama
        $newPara = preg_replace('/<\/w:r>/', $tTag.'</w:r>', $newPara, 1);

        return [$newPara, $count];
    }

    /**
     * Encode XML entities tapi jaga placeholder ${...} tetap utuh.
     */
    private function encodeXmlKeepPlaceholders(string $text): string
    {
        $parts  = preg_split('/(\$\{[^}]+\})/', $text, -1, PREG_SPLIT_DELIM_CAPTURE);
        $result = '';
        foreach ($parts as $part) {
            $result .= preg_match('/^\$\{[^}]+\}$/', $part)
                ? $part
                : htmlspecialchars($part, ENT_XML1 | ENT_QUOTES, 'UTF-8');
        }

        return $result;
    }
}
