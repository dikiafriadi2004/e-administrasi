<?php

namespace App\Console\Commands;

use App\Models\TemplateSurat;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Element\Section;
use PhpOffice\PhpWord\IOFactory;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\Style\Language;

/**
 * Generate template .docx bersih dari scratch dengan PHPWord.
 * Mengikuti struktur surat resmi institusi berdasarkan file asli di folder doc/.
 *
 * Placeholder menggunakan format ${...} yang kompatibel dengan PHPWord TemplateProcessor.
 */
class GenerateTemplateBersih extends Command
{
    protected $signature = 'template:generate {jenis? : aktif_kuliah|seminar_proposal|sidang_skripsi|undangan_penguji|izin_magang|rekomendasi_magang|izin_penelitian|keluar_prodi|semua}';

    protected $description = 'Generate template .docx bersih dengan placeholder lengkap';

    private const JENIS_TERSEDIA = [
        'aktif_kuliah',
        'seminar_proposal',
        'sidang_skripsi',
        'undangan_penguji',
        'izin_magang',
        'rekomendasi_magang',
        'izin_penelitian',
        'keluar_prodi',
    ];

    // ── Font & style constants ────────────────────────────────────────────────
    private const FONT = 'Times New Roman';

    private const SIZE_NORMAL = 12;

    private const SIZE_KOP = 14;

    private const SIZE_SUB = 10;

    private const LINE_SPACE = 1.5;

    public function handle(): int
    {
        $jenis = $this->argument('jenis') ?? 'semua';
        $daftar = $jenis === 'semua' ? self::JENIS_TERSEDIA : [$jenis];

        foreach ($daftar as $j) {
            if (! in_array($j, self::JENIS_TERSEDIA)) {
                $this->error("Jenis '{$j}' tidak dikenal.");

                continue;
            }

            $this->info("Generating: {$j}...");
            $path = $this->generate($j);
            $this->daftarkan($j, $path);
            $this->line("  ✓ {$path}");
        }

        $this->newLine();
        $this->info('Semua template berhasil digenerate.');

        return self::SUCCESS;
    }

    // ── Entry point per jenis ─────────────────────────────────────────────────

    private function generate(string $jenis): string
    {
        $phpWord = new PhpWord;
        $phpWord->getSettings()->setThemeFontLang(new Language(Language::EN_US));
        $phpWord->setDefaultFontName(self::FONT);
        $phpWord->setDefaultFontSize(self::SIZE_NORMAL);

        $sectionStyle = [
            'pageSizeW' => Converter::cmToTwip(21),
            'pageSizeH' => Converter::cmToTwip(29.7),
            'marginTop' => Converter::cmToTwip(2.5),
            'marginBottom' => Converter::cmToTwip(2),
            'marginLeft' => Converter::cmToTwip(3),
            'marginRight' => Converter::cmToTwip(2.5),
        ];
        $section = $phpWord->addSection($sectionStyle);

        $this->buatKop($section);
        $this->buatFooter($section);

        match ($jenis) {
            'aktif_kuliah' => $this->templateAktifKuliah($section),
            'seminar_proposal' => $this->templateSeminarProposal($section),
            'sidang_skripsi' => $this->templateSidangSkripsi($section),
            'undangan_penguji' => $this->templateUndanganPenguji($section),
            'izin_magang' => $this->templateIzinMagang($section),
            'rekomendasi_magang' => $this->templateRekomendasiMagang($section),
            'izin_penelitian' => $this->templateIzinPenelitian($section),
            'keluar_prodi' => $this->templateKeluarProdi($section),
        };

        $storageDir = Storage::disk('private')->path('templates');
        if (! File::isDirectory($storageDir)) {
            File::makeDirectory($storageDir, 0755, true);
        }

        // Hapus file lama (doc/docx)
        foreach (['doc', 'docx'] as $ext) {
            $old = Storage::disk('private')->path("templates/{$jenis}_v1.{$ext}");
            if (File::exists($old)) {
                File::delete($old);
            }
        }

        $storagePath = "templates/{$jenis}_v1.docx";
        $absPath = Storage::disk('private')->path($storagePath);

        $writer = IOFactory::createWriter($phpWord, 'Word2007');
        $writer->save($absPath);

        return $storagePath;
    }

    // ── Kop Surat ─────────────────────────────────────────────────────────────

    private function buatKop(Section $section): void
    {
        $header = $section->addHeader();

        // Tabel kop: kolom logo | kolom teks
        $tbl = $header->addTable([
            'unit' => 'pct',
            'width' => 100 * 50,
        ]);
        $row = $tbl->addRow(Converter::cmToTwip(2.5));

        // Kolom logo
        $cellLogo = $row->addCell(Converter::cmToTwip(2.5), [
            'borderSize' => 8,
            'borderColor' => '000000',
            'valign' => 'center',
        ]);
        $cellLogo->addText('LOGO', [
            'name' => self::FONT,
            'size' => 8,
            'bold' => true,
            'color' => '666666',
        ], ['alignment' => 'center']);

        // Kolom teks institusi
        $cellTeks = $row->addCell(null, ['valign' => 'center']);
        $cellTeks->addText(
            'KEMENTERIAN PENDIDIKAN TINGGI, SAINS DAN TEKNOLOGI',
            ['name' => self::FONT, 'size' => 9],
            ['alignment' => 'center', 'spaceAfter' => 0]
        );
        $cellTeks->addText(
            '${nama_universitas}',
            ['name' => self::FONT, 'size' => self::SIZE_KOP, 'bold' => true],
            ['alignment' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0]
        );
        $cellTeks->addText(
            '${nama_fakultas}',
            ['name' => self::FONT, 'size' => 11, 'bold' => true],
            ['alignment' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0]
        );
        $cellTeks->addText(
            '${nama_prodi}',
            ['name' => self::FONT, 'size' => 11, 'bold' => true],
            ['alignment' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0]
        );
        $cellTeks->addText(
            '${alamat_prodi}',
            ['name' => self::FONT, 'size' => self::SIZE_SUB],
            ['alignment' => 'center', 'spaceBefore' => 0, 'spaceAfter' => 0]
        );
        $cellTeks->addText(
            'Telepon: ${telepon_prodi} | Email: ${email_prodi}',
            ['name' => self::FONT, 'size' => self::SIZE_SUB],
            ['alignment' => 'center', 'spaceBefore' => 0]
        );

        // Garis bawah kop
        $header->addLine(['weight' => 3, 'width' => Converter::cmToEmu(16), 'color' => '000000', 'position' => 'left']);
        $header->addTextBreak(0);
    }

    // ── Footer ────────────────────────────────────────────────────────────────

    private function buatFooter(Section $section): void
    {
        $footer = $section->addFooter();
        $footer->addLine(['weight' => 1, 'width' => Converter::cmToEmu(16), 'color' => '666666', 'position' => 'left']);
        $footer->addText(
            '${alamat_prodi} | Telp. ${telepon_prodi} | ${email_prodi}',
            ['name' => self::FONT, 'size' => 8, 'italic' => true, 'color' => '666666'],
            ['alignment' => 'center']
        );
    }

    // ── Helper styles ─────────────────────────────────────────────────────────

    private function f(array $extra = []): array
    {
        return array_merge(['name' => self::FONT, 'size' => self::SIZE_NORMAL], $extra);
    }

    private function pj(array $extra = []): array
    {
        return array_merge(['alignment' => 'both', 'lineHeight' => self::LINE_SPACE, 'spaceAfter' => 120], $extra);
    }

    private function pl(array $extra = []): array
    {
        return array_merge(['alignment' => 'left', 'lineHeight' => self::LINE_SPACE, 'spaceAfter' => 0], $extra);
    }

    // ── Area TTD ──────────────────────────────────────────────────────────────

    private function buatAreaTTD(Section $section, bool $kananSaja = true): void
    {
        $section->addTextBreak(1);
        $tbl = $section->addTable(['unit' => 'pct', 'width' => 100 * 50]);
        $row = $tbl->addRow();

        if ($kananSaja) {
            $row->addCell(Converter::cmToTwip(9));
            $cell = $row->addCell(Converter::cmToTwip(8));
        } else {
            $cell = $row->addCell(Converter::cmToTwip(17));
        }

        $cell->addText('${kota_prodi}, ${tanggal_surat}', $this->f(), ['alignment' => 'center']);
        $cell->addText('Kepala Program Studi,', $this->f(), ['alignment' => 'center']);
        for ($i = 0; $i < 4; $i++) {
            $cell->addTextBreak(1);
        }
        $cell->addText('${nama_kaprodi}', $this->f(['bold' => true, 'underline' => 'single']), ['alignment' => 'center']);
        $cell->addText('NIP. ${nip_kaprodi}', $this->f(), ['alignment' => 'center']);
    }

    // ── Data tabel (label: nilai) ─────────────────────────────────────────────

    /**
     * @param  array<array{string, string}>  $rows
     */
    private function tabelData(Section $section, array $rows): void
    {
        $tbl = $section->addTable(['unit' => 'pct', 'width' => 80 * 50, 'borderSize' => 0, 'borderColor' => 'ffffff']);
        foreach ($rows as [$label, $value]) {
            $r = $tbl->addRow();
            $r->addCell(Converter::cmToTwip(4.5))->addText($label, $this->f());
            $r->addCell(Converter::cmToTwip(0.5))->addText(':', $this->f());
            $r->addCell(null)->addText($value, $this->f());
        }
    }

    /**
     * @param  array<array{string, string, string}>  $rows
     */
    private function tabelDosen(Section $section, array $rows): void
    {
        $tbl = $section->addTable(['unit' => 'pct', 'width' => 90 * 50, 'borderSize' => 0, 'borderColor' => 'ffffff']);
        foreach ($rows as [$no, $nama, $peran]) {
            $r = $tbl->addRow();
            $r->addCell(Converter::cmToTwip(0.8))->addText($no, $this->f());
            $r->addCell(Converter::cmToTwip(8))->addText($nama, $this->f(['bold' => true]));
            $r->addCell(Converter::cmToTwip(6))->addText($peran, $this->f());
        }
    }

    // ── TEMPLATE 1: Surat Keterangan Aktif Kuliah ─────────────────────────────

    private function templateAktifKuliah(Section $s): void
    {
        // Judul surat — sesuai template asli
        $s->addTextBreak(1);
        $s->addText(
            'SURAT  KETERANGAN',
            $this->f(['bold' => true, 'size' => 14, 'underline' => 'single']),
            ['alignment' => 'center', 'spaceAfter' => 0]
        );
        $s->addText(
            'Nomor : ${nomor_surat}',
            $this->f(['size' => 11]),
            ['alignment' => 'center', 'spaceAfter' => 200]
        );

        $s->addTextBreak(1);

        // Paragraf pembuka — sesuai template asli (Dekan, bukan Kaprodi)
        $s->addText(
            'Dekan ${nama_fakultas} ${nama_universitas} dengan ini menerangkan bahwa :',
            $this->f(),
            $this->pj()
        );

        $s->addTextBreak(1);

        // Data mahasiswa — format tabel dengan tab alignment sesuai asli
        $tbl = $s->addTable([
            'unit' => 'pct',
            'width' => 85 * 50,
            'borderSize' => 0,
            'borderColor' => 'ffffff',
        ]);

        $rows = [
            ['Nama',              '${nama_mahasiswa}'],
            ['NIM',               '${nim}'],
            ['Jurusan/Program Studi', '${nama_prodi}'],
            ['Alamat',            '${alamat_mahasiswa}'],
        ];

        foreach ($rows as [$label, $value]) {
            $r = $tbl->addRow();
            $r->addCell(Converter::cmToTwip(5.5))->addText($label, $this->f(), $this->pl());
            $r->addCell(Converter::cmToTwip(0.4))->addText(':', $this->f(), $this->pl());
            $r->addCell(null)->addText($value, $this->f(), $this->pl());
        }

        $s->addTextBreak(1);

        // Paragraf isi — sesuai template asli
        $s->addText(
            'adalah benar yang tersebut namanya di atas mahasiswa Program Studi ${nama_prodi} '
            .'${nama_fakultas} ${nama_universitas}, terdaftar pada semester ${semester_aktif} '
            .'Tahun Akademik ${tahun_akademik}.',
            $this->f(),
            $this->pj()
        );

        $s->addTextBreak(1);

        $s->addText(
            'Demikian surat keterangan ini dikeluarkan untuk keperluan ${keperluan}.',
            $this->f(),
            $this->pj()
        );

        // Area TTD — sesuai asli: a.n. Dekan, Wakil Dekan Akademik → diganti Kaprodi
        $this->buatAreaTTDAktifKuliah($s);
    }

    /**
     * Area TTD khusus Surat Aktif Kuliah — sesuai format asli:
     * kota + tanggal, Kepala Program Studi, nama & NIP Kaprodi
     */
    private function buatAreaTTDAktifKuliah(Section $s): void
    {
        $s->addTextBreak(2);

        $tbl = $s->addTable(['unit' => 'pct', 'width' => 100 * 50]);
        $row = $tbl->addRow();
        $row->addCell(Converter::cmToTwip(9));

        $cell = $row->addCell(Converter::cmToTwip(8));
        $cell->addText('${kota_prodi}, ${tanggal_surat}', $this->f(), $this->pl());
        $cell->addText('Kepala Program Studi,', $this->f(), $this->pl());

        for ($i = 0; $i < 4; $i++) {
            $cell->addTextBreak(1);
        }

        $cell->addText(
            '${nama_kaprodi}',
            $this->f(['bold' => true, 'underline' => 'single']),
            $this->pl()
        );
        $cell->addText('NIP ${nip_kaprodi}', $this->f(), $this->pl());
    }

    // ── TEMPLATE 2: Undangan Seminar Proposal ─────────────────────────────────

    private function templateSeminarProposal(Section $s): void
    {
        // Nomor/Lampiran/Perihal
        $this->tabelData($s, [
            ['Nomor',    '${nomor_surat}'],
            ['Lampiran', '1 (satu) Berkas'],
            ['Perihal',  'Seminar Kajian Proposal Skripsi'],
        ]);

        $s->addTextBreak(1);
        $s->addText('Yth.', $this->f(), $this->pl());
        $s->addText('Bapak/Ibu ${nama_pembimbing_1}', $this->f(['bold' => true]), $this->pl());
        $s->addText('di Tempat', $this->f(), $this->pl(['spaceAfter' => 200]));

        $s->addText('Dengan hormat,', $this->f(), $this->pj());
        $s->addText(
            'Koordinator ${nama_prodi} ${nama_fakultas} ${nama_universitas} menetapkan saudara:',
            $this->f(), $this->pj()
        );
        $s->addTextBreak(1);

        $this->tabelDosen($s, [
            ['1.', '${nama_pembimbing_1}', 'Pembimbing I'],
            ['2.', '${nama_pembimbing_2}', 'Pembimbing II'],
            ['3.', '${nama_penguji_1}',    'Penguji I'],
            ['4.', '${nama_penguji_2}',    'Penguji II'],
        ]);

        $s->addTextBreak(1);
        $s->addText('Pada Kajian Proposal yang berjudul:', $this->f(), $this->pj());
        $s->addText('"${judul_skripsi}"', $this->f(['bold' => true]), $this->pj());
        $s->addTextBreak(1);

        $this->tabelData($s, [
            ['Nama',    '${nama_mahasiswa}'],
            ['NIM',     '${nim}'],
            ['Jurusan', '${nama_prodi}'],
        ]);

        $s->addTextBreak(1);
        $s->addText('Akan dilaksanakan pada:', $this->f(), $this->pj());
        $s->addTextBreak(1);

        $this->tabelData($s, [
            ['Hari / Tanggal', '${tanggal_seminar}'],
            ['Waktu',          '${waktu_sidang}'],
            ['Tempat',         '${tempat_sidang}'],
        ]);

        $s->addTextBreak(1);
        $s->addText(
            'Sehubungan dengan hal tersebut di atas, kami mengundang Bapak/Ibu untuk menjadi pengkaji proposal.',
            $this->f(), $this->pj()
        );
        $s->addText('Atas kehadiran Bapak/Ibu tepat waktu kami ucapkan terima kasih.', $this->f(), $this->pj());

        $this->buatAreaTTD($s);
    }

    // ── TEMPLATE 3: Undangan Sidang Skripsi ──────────────────────────────────

    private function templateSidangSkripsi(Section $s): void
    {
        $this->tabelData($s, [
            ['Nomor',    '${nomor_surat}'],
            ['Lampiran', '1 (satu) Berkas'],
            ['Perihal',  'Sidang Skripsi Mahasiswa ${nama_prodi}'],
        ]);

        $s->addTextBreak(1);
        $s->addText('Yth.', $this->f(), $this->pl());
        $s->addText('Tempat', $this->f(), $this->pl(['spaceAfter' => 200]));

        $s->addText('Dengan hormat,', $this->f(), $this->pj());
        $s->addText(
            'Koordinator ${nama_prodi} ${nama_fakultas} ${nama_universitas} menetapkan saudara:',
            $this->f(), $this->pj()
        );
        $s->addTextBreak(1);

        $this->tabelDosen($s, [
            ['1.', '${nama_pembimbing_1}', 'Pembimbing I'],
            ['2.', '${nama_pembimbing_2}', 'Pembimbing II'],
            ['3.', '${nama_penguji_1}',    'Penguji I'],
            ['4.', '${nama_penguji_2}',    'Penguji II'],
        ]);

        $s->addTextBreak(1);
        $s->addText('Pada Sidang Skripsi yang berjudul:', $this->f(), $this->pj());
        $s->addText('"${judul_skripsi}"', $this->f(['bold' => true]), $this->pj());
        $s->addTextBreak(1);

        $this->tabelData($s, [
            ['Nama',    '${nama_mahasiswa}'],
            ['NIM',     '${nim}'],
            ['Jurusan', '${nama_prodi}'],
        ]);

        $s->addTextBreak(1);
        $s->addText('Akan dilaksanakan pada:', $this->f(), $this->pj());
        $s->addTextBreak(1);

        $this->tabelData($s, [
            ['Hari / Tanggal', '${tanggal_sidang}'],
            ['Pukul',          '${waktu_sidang}'],
            ['Tempat',         '${tempat_sidang}'],
        ]);

        $s->addTextBreak(1);
        $s->addText(
            'Sehubungan dengan hal tersebut, kami mengundang Bapak/Ibu sebagai penguji pada sidang yang dimaksud.',
            $this->f(), $this->pj()
        );
        $s->addText('Atas kehadiran Bapak/Ibu tepat waktu kami ucapkan terima kasih.', $this->f(), $this->pj());

        $this->buatAreaTTD($s);
    }

    // ── TEMPLATE 4: Surat Undangan Dosen Penguji ─────────────────────────────

    private function templateUndanganPenguji(Section $s): void
    {
        $this->tabelData($s, [
            ['Nomor',    '${nomor_surat}'],
            ['Lampiran', '1 (satu) Berkas'],
            ['Perihal',  'Undangan Penguji Sidang Skripsi'],
        ]);

        $s->addTextBreak(1);
        $s->addText('Yth.', $this->f(), $this->pl());
        $s->addText('Bapak/Ibu ${nama_penguji_1}', $this->f(['bold' => true]), $this->pl());
        $s->addText('di Tempat', $this->f(), $this->pl(['spaceAfter' => 200]));

        $s->addText('Dengan hormat,', $this->f(), $this->pj());
        $s->addText(
            'Sehubungan dengan pelaksanaan Sidang Skripsi, kami mengundang Bapak/Ibu sebagai Dosen Penguji pada:',
            $this->f(), $this->pj()
        );
        $s->addTextBreak(1);

        $this->tabelData($s, [
            ['Hari / Tanggal', '${tanggal_sidang}'],
            ['Waktu',          '${waktu_sidang}'],
            ['Tempat',         '${tempat_sidang}'],
        ]);

        $s->addTextBreak(1);
        $s->addText('Pada Sidang Skripsi yang berjudul:', $this->f(), $this->pj());
        $s->addText('"${judul_skripsi}"', $this->f(['bold' => true]), $this->pj());
        $s->addTextBreak(1);

        $this->tabelData($s, [
            ['Nama',    '${nama_mahasiswa}'],
            ['NIM',     '${nim}'],
            ['Jurusan', '${nama_prodi}'],
        ]);

        $s->addTextBreak(1);
        $s->addText('Dengan susunan tim:', $this->f(), $this->pj());
        $s->addTextBreak(1);

        $this->tabelDosen($s, [
            ['1.', '${nama_pembimbing_1}', 'Pembimbing I'],
            ['2.', '${nama_pembimbing_2}', 'Pembimbing II'],
            ['3.', '${nama_penguji_1}',    'Penguji I'],
            ['4.', '${nama_penguji_2}',    'Penguji II'],
        ]);

        $s->addTextBreak(1);
        $s->addText('Atas kesediaan dan perhatian Bapak/Ibu kami ucapkan terima kasih.', $this->f(), $this->pj());

        $this->buatAreaTTD($s);
    }

    // ── TEMPLATE 5: Surat Izin Magang / PKL ──────────────────────────────────

    private function templateIzinMagang(Section $s): void
    {
        $this->tabelData($s, [
            ['Nomor',    '${nomor_surat}'],
            ['Lampiran', '-'],
            ['Perihal',  'Permohonan Izin Magang / PKL'],
        ]);

        $s->addTextBreak(1);
        $s->addText('Kepada Yth.', $this->f(), $this->pl());
        $s->addText('${nama_instansi}', $this->f(['bold' => true]), $this->pl());
        $s->addText('${alamat_instansi}', $this->f(), $this->pl());
        $s->addText('di Tempat', $this->f(), $this->pl(['spaceAfter' => 200]));

        $s->addText('Dengan hormat,', $this->f(), $this->pj());
        $s->addText(
            'Yang bertanda tangan di bawah ini, Kepala Program Studi ${nama_prodi}, ${nama_fakultas}, '
            .'${nama_universitas}, dengan ini mengajukan permohonan izin magang / PKL bagi mahasiswa:',
            $this->f(), $this->pj()
        );
        $s->addTextBreak(1);

        $this->tabelData($s, [
            ['Nama',          '${nama_mahasiswa}'],
            ['NIM',           '${nim}'],
            ['Program Studi', '${nama_prodi}'],
            ['Semester',      '${semester_aktif}'],
            ['Alamat',        '${alamat_mahasiswa}'],
        ]);

        $s->addTextBreak(1);
        $s->addText(
            'Untuk melaksanakan kegiatan Magang / Praktik Kerja Lapangan (PKL) di instansi yang Bapak/Ibu pimpin, '
            .'terhitung mulai ${tanggal_mulai} s.d. ${tanggal_selesai}.',
            $this->f(), $this->pj()
        );
        $s->addTextBreak(1);
        $s->addText(
            'Demikian surat permohonan ini kami sampaikan. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.',
            $this->f(), $this->pj()
        );

        $this->buatAreaTTD($s);
    }

    // ── TEMPLATE 6: Surat Rekomendasi Magang ─────────────────────────────────

    private function templateRekomendasiMagang(Section $s): void
    {
        $this->tabelData($s, [
            ['Nomor',    '${nomor_surat}'],
            ['Lampiran', '-'],
            ['Perihal',  'Rekomendasi Magang / PKL'],
        ]);

        $s->addTextBreak(1);
        $s->addText('Kepada Yth.', $this->f(), $this->pl());
        $s->addText('${nama_instansi}', $this->f(['bold' => true]), $this->pl());
        $s->addText('${alamat_instansi}', $this->f(), $this->pl());
        $s->addText('di Tempat', $this->f(), $this->pl(['spaceAfter' => 200]));

        $s->addText('Dengan hormat,', $this->f(), $this->pj());
        $s->addText(
            'Yang bertanda tangan di bawah ini, Kepala Program Studi ${nama_prodi}, ${nama_fakultas}, '
            .'${nama_universitas}, dengan ini merekomendasikan mahasiswa berikut:',
            $this->f(), $this->pj()
        );
        $s->addTextBreak(1);

        $this->tabelData($s, [
            ['Nama',          '${nama_mahasiswa}'],
            ['NIM',           '${nim}'],
            ['Program Studi', '${nama_prodi}'],
            ['Semester',      '${semester_aktif}'],
            ['Alamat',        '${alamat_mahasiswa}'],
        ]);

        $s->addTextBreak(1);
        $s->addText(
            'Untuk dapat diterima melaksanakan kegiatan Magang / PKL di instansi yang Bapak/Ibu pimpin. '
            .'Mahasiswa yang bersangkutan memiliki kemampuan akademik yang baik dan berkomitmen.',
            $this->f(), $this->pj()
        );
        $s->addTextBreak(1);
        $s->addText(
            'Demikian surat rekomendasi ini kami sampaikan. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.',
            $this->f(), $this->pj()
        );

        $this->buatAreaTTD($s);
    }

    // ── TEMPLATE 7: Surat Izin Penelitian ────────────────────────────────────

    private function templateIzinPenelitian(Section $s): void
    {
        $this->tabelData($s, [
            ['Nomor',    '${nomor_surat}'],
            ['Lampiran', '-'],
            ['Perihal',  'Permohonan Izin Penelitian'],
        ]);

        $s->addTextBreak(1);
        $s->addText('Kepada Yth.', $this->f(), $this->pl());
        $s->addText('${nama_instansi}', $this->f(['bold' => true]), $this->pl());
        $s->addText('${alamat_instansi}', $this->f(), $this->pl());
        $s->addText('di Tempat', $this->f(), $this->pl(['spaceAfter' => 200]));

        $s->addText('Dengan hormat,', $this->f(), $this->pj());
        $s->addText(
            'Yang bertanda tangan di bawah ini, Kepala Program Studi ${nama_prodi}, ${nama_fakultas}, '
            .'${nama_universitas}, dengan ini mengajukan permohonan izin penelitian bagi mahasiswa:',
            $this->f(), $this->pj()
        );
        $s->addTextBreak(1);

        $this->tabelData($s, [
            ['Nama',          '${nama_mahasiswa}'],
            ['NIM',           '${nim}'],
            ['Program Studi', '${nama_prodi}'],
            ['Alamat',        '${alamat_mahasiswa}'],
        ]);

        $s->addTextBreak(1);
        $s->addText(
            'Untuk melaksanakan penelitian dalam rangka penyusunan skripsi dengan judul:',
            $this->f(), $this->pj()
        );
        $s->addText('"${judul_penelitian}"', $this->f(['bold' => true]), $this->pj());
        $s->addText('Bidang kajian: ${bidang_penelitian}', $this->f(), $this->pj());
        $s->addText(
            'Penelitian dilaksanakan di lokasi yang Bapak/Ibu kelola, '
            .'terhitung mulai ${tanggal_mulai} s.d. ${tanggal_selesai}.',
            $this->f(), $this->pj()
        );
        $s->addTextBreak(1);
        $s->addText(
            'Demikian surat permohonan ini kami sampaikan. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.',
            $this->f(), $this->pj()
        );

        $this->buatAreaTTD($s);
    }

    // ── TEMPLATE 8: Surat Keluar Prodi ───────────────────────────────────────

    private function templateKeluarProdi(Section $s): void
    {
        $s->addTextBreak(1);
        $s->addText('SURAT KETERANGAN', $this->f(['bold' => true, 'size' => 13]), ['alignment' => 'center']);
        $s->addText('Nomor: ${nomor_surat}', $this->f(), ['alignment' => 'center', 'spaceAfter' => 200]);

        $s->addText('Dengan hormat,', $this->f(), $this->pj());
        $s->addText(
            'Yang bertanda tangan di bawah ini, Kepala Program Studi ${nama_prodi}, ${nama_fakultas}, '
            .'${nama_universitas}, dengan ini menerangkan bahwa:',
            $this->f(), $this->pj()
        );
        $s->addTextBreak(1);

        $this->tabelData($s, [
            ['Nama',          '${nama_mahasiswa}'],
            ['NIM',           '${nim}'],
            ['Program Studi', '${nama_prodi}'],
            ['Angkatan',      '${angkatan}'],
        ]);

        $s->addTextBreak(1);
        $s->addText(
            'adalah benar mahasiswa pada ${nama_prodi}, ${nama_fakultas}, ${nama_universitas}.',
            $this->f(), $this->pj()
        );
        $s->addTextBreak(1);
        $s->addText(
            'Demikian surat keterangan ini dibuat untuk dapat dipergunakan sebagaimana mestinya.',
            $this->f(), $this->pj()
        );

        $this->buatAreaTTD($s);
    }

    // ── Daftarkan ke database ─────────────────────────────────────────────────

    private function daftarkan(string $jenis, string $path): void
    {
        TemplateSurat::updateOrCreate(
            ['jenis_surat' => $jenis],
            ['path_file' => $path, 'versi' => 1, 'is_aktif' => true]
        );
    }
}
