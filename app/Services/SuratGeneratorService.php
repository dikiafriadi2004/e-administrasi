<?php

namespace App\Services;

use App\Exceptions\SuratGenerationException;
use App\Models\PengajuanSurat;
use App\Models\Pengaturan;
use App\Models\TemplateSurat;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpWord\TemplateProcessor;

/**
 * SuratGeneratorService
 *
 * Generate file .docx dari template dengan placeholder yang sudah terisi.
 * PDF TIDAK digenerate — admin cetak dari Word, minta TTD Kaprodi, lalu scan + upload.
 */
class SuratGeneratorService
{
    /**
     * Entry point — generate DOCX untuk satu pengajuan surat.
     *
     * @return array{docx: string}
     *
     * @throws SuratGenerationException
     */
    public function generate(PengajuanSurat $pengajuan): array
    {
        // Load semua relasi yang dibutuhkan untuk placeholder
        $pengajuan->loadMissing([
            'mahasiswa.user',
            'pengajuanJudul.dosenPembimbing',
            'dosenPenguji',
        ]);

        // Ambil template aktif untuk jenis surat ini
        $template = TemplateSurat::aktif()->jenis($pengajuan->jenis_surat)->first();

        if (! $template) {
            throw SuratGenerationException::templateTidakDitemukan($pengajuan->jenis_surat);
        }

        $templateAbsPath = Storage::disk('private')->path($template->path_file);

        if (! file_exists($templateAbsPath)) {
            throw SuratGenerationException::templateTidakDitemukan(
                "{$pengajuan->jenis_surat} (file tidak ditemukan di server)"
            );
        }

        // Buat direktori output berdasarkan NIM + jenis surat (bukan ID)
        $nim = $pengajuan->mahasiswa?->nim ?? 'unknown';
        $jenis = $pengajuan->jenis_surat;
        $outputDir = "surat/{$nim}/{$jenis}";
        Storage::disk('private')->makeDirectory($outputDir);
        $outputDirAbs = Storage::disk('private')->path($outputDir);

        // Salin template ke file baru — jangan edit template asli
        $uuid = Str::uuid()->toString();
        $docxFilename = "{$uuid}.docx";
        $docxAbsPath = $outputDirAbs.DIRECTORY_SEPARATOR.$docxFilename;
        copy($templateAbsPath, $docxAbsPath);

        // Isi semua placeholder dengan PHPWord TemplateProcessor
        $placeholders = $this->buildPlaceholders($pengajuan);
        $processor = new TemplateProcessor($docxAbsPath);

        foreach ($placeholders as $key => $value) {
            try {
                $processor->setValue($key, htmlspecialchars((string) $value));
            } catch (\Throwable) {
                // Placeholder tidak ada di template — skip, catat ke log
                Log::warning("Placeholder '{$key}' tidak ditemukan di template.", [
                    'pengajuan_id' => $pengajuan->id,
                    'jenis_surat' => $pengajuan->jenis_surat,
                ]);
            }
        }

        $processor->saveAs($docxAbsPath);

        // Simpan path DOCX ke database
        $docxStoragePath = $outputDir.'/'.$docxFilename;

        $pengajuan->update([
            'file_docx' => $docxStoragePath,
            'file_pdf' => null,   // tidak digenerate — admin cetak manual dari Word
            'generated_at' => now(),
        ]);

        return ['docx' => $docxStoragePath];
    }

    /**
     * Kumpulkan semua nilai placeholder dari data pengajuan.
     * Kota dan identitas kaprodi dibaca dari tabel pengaturan.
     *
     * @return array<string, string>
     */
    public function buildPlaceholders(PengajuanSurat $pengajuan): array
    {
        $pengajuan->loadMissing([
            'mahasiswa.user',
            'pengajuanJudul.dosenPembimbing',
            'pengajuanJudul.dosenPembimbing2',
            'dosenPenguji',
            'dosenPenguji2',
        ]);

        $mahasiswa = $pengajuan->mahasiswa;
        $user = $mahasiswa?->user;
        $judul = $pengajuan->pengajuanJudul;
        $pembimbing = $judul?->dosenPembimbing;
        $pembimbing2 = $judul?->dosenPembimbing2;
        $penguji = $pengajuan->dosenPenguji;
        $penguji2 = $pengajuan->dosenPenguji2;
        $dataForm = $pengajuan->data_form ?? [];

        $kota = Pengaturan::nilai('kota_prodi', '');
        // tanggal_surat HANYA berisi tanggal saja — kota sudah ada di kota_prodi secara terpisah
        $tanggalSurat = Carbon::now()->locale('id')->isoFormat('D MMMM Y');

        // ── Komponen nomor surat — masing-masing placeholder terpisah ─────────
        // nomor_surat (backward compat) dibentuk dari komponen-komponen ini
        $nomorUrut = '';
        $kodeInstitusi = Pengaturan::nilai('kode_institusi', '');
        $kodeProdi = Pengaturan::nilai('kode_prodi', '');
        $bulanSurat = now()->format('m');
        $tahunSurat = now()->format('Y');

        // Jika pengajuan sudah punya nomor_surat penuh (dari input admin), gunakan itu.
        // Kalau belum, biarkan kosong — placeholder akan tampil kosong di dokumen.
        if ($pengajuan->nomor_surat) {
            // Ekstrak nomor_urut dari nomor_surat penuh jika ada
            $parts = explode('/', $pengajuan->nomor_surat);
            $nomorUrut = $parts[0] ?? '';
        }

        return [
            // ── Nomor surat — tiap bagian jadi placeholder sendiri ────────────
            'nomor_surat' => $pengajuan->nomor_surat ?? '',  // backward compat
            'nomor_urut' => $nomorUrut,
            'kode_institusi' => $kodeInstitusi,
            'kode_prodi' => $kodeProdi,
            'bulan_surat' => $bulanSurat,
            'tahun_surat' => $tahunSurat,

            // ── Universal ─────────────────────────────────────────────────────
            'tanggal_surat' => $tanggalSurat,
            'nama_mahasiswa' => $user?->name ?? '',
            'nim' => $mahasiswa?->nim ?? '',
            'angkatan' => (string) ($mahasiswa?->angkatan ?? ''),
            'alamat_mahasiswa' => $mahasiswa?->alamat ?? '',
            'nama_kaprodi' => Pengaturan::nilai('nama_kaprodi', ''),
            'nip_kaprodi' => Pengaturan::nilai('nip_kaprodi', ''),
            'nama_dekan' => Pengaturan::nilai('nama_dekan', ''),
            'nip_dekan' => Pengaturan::nilai('nip_dekan', ''),

            // Identitas institusi
            'nama_prodi' => Pengaturan::nilai('nama_prodi', ''),
            'nama_fakultas' => Pengaturan::nilai('nama_fakultas', ''),
            'nama_universitas' => Pengaturan::nilai('nama_universitas', ''),
            'alamat_prodi' => Pengaturan::nilai('alamat_prodi', ''),
            'telepon_prodi' => Pengaturan::nilai('telepon_prodi', ''),
            'email_prodi' => Pengaturan::nilai('email_prodi', ''),
            'kota_prodi' => $kota,

            // Surat Aktif Kuliah
            'keperluan' => $dataForm['keperluan'] ?? '',
            'tujuan_instansi' => $dataForm['tujuan_instansi'] ?? '',

            // Kalender akademik
            'semester_aktif' => Pengaturan::nilai('semester_aktif', ''),
            'tahun_akademik' => Pengaturan::nilai('tahun_akademik', ''),

            // Judul & bidang
            'judul_skripsi' => $judul?->judul ?? $dataForm['judul_skripsi'] ?? '',
            'bidang_kajian' => $judul?->bidang_kajian ?? $dataForm['bidang_kajian'] ?? '',

            // Tanggal & waktu pelaksanaan
            'tanggal_seminar' => $pengajuan->tanggal_jadwal
                ? Carbon::parse($pengajuan->tanggal_jadwal)->locale('id')->isoFormat('dddd, D MMMM Y')
                : ($dataForm['tanggal_rencana'] ?? ''),
            'tanggal_sidang' => $pengajuan->tanggal_jadwal
                ? Carbon::parse($pengajuan->tanggal_jadwal)->locale('id')->isoFormat('dddd, D MMMM Y')
                : ($dataForm['tanggal_rencana'] ?? ''),
            'waktu_sidang' => $pengajuan->waktu_jadwal ?? $dataForm['waktu_rencana'] ?? '',
            'tempat_sidang' => $pengajuan->tempat_jadwal ?? $dataForm['tempat'] ?? '',

            // Dosen Pembimbing 1
            'nama_pembimbing' => $pembimbing?->nama ?? $dataForm['nama_pembimbing_1'] ?? $dataForm['nama_pembimbing'] ?? '',
            'nip_pembimbing' => $pembimbing?->nip ?? '',
            'nama_pembimbing_1' => $pembimbing?->nama ?? $dataForm['nama_pembimbing_1'] ?? $dataForm['nama_pembimbing'] ?? '',
            'nip_pembimbing_1' => $pembimbing?->nip ?? '',

            // Dosen Pembimbing 2
            'nama_pembimbing_2' => $pembimbing2?->nama ?? $dataForm['nama_pembimbing_2'] ?? '',
            'nip_pembimbing_2' => $pembimbing2?->nip ?? '',

            // Dosen Penguji 1
            'nama_penguji' => $penguji?->nama ?? $dataForm['nama_penguji_1'] ?? $dataForm['nama_penguji'] ?? '',
            'nip_penguji' => $penguji?->nip ?? '',
            'nama_penguji_1' => $penguji?->nama ?? $dataForm['nama_penguji_1'] ?? $dataForm['nama_penguji'] ?? '',
            'nip_penguji_1' => $penguji?->nip ?? '',

            // Dosen Penguji 2
            'nama_penguji_2' => $penguji2?->nama ?? $dataForm['nama_penguji_2'] ?? '',
            'nip_penguji_2' => $penguji2?->nip ?? '',

            // Surat Magang & Penelitian
            'nama_instansi' => $dataForm['nama_instansi'] ?? '',
            'alamat_instansi' => $dataForm['alamat_instansi'] ?? '',
            'tanggal_mulai' => isset($dataForm['tanggal_mulai'])
                ? Carbon::parse($dataForm['tanggal_mulai'])->locale('id')->isoFormat('D MMMM Y')
                : '',
            'tanggal_selesai' => isset($dataForm['tanggal_selesai'])
                ? Carbon::parse($dataForm['tanggal_selesai'])->locale('id')->isoFormat('D MMMM Y')
                : '',

            // Surat Izin Penelitian
            'judul_penelitian' => $dataForm['judul_penelitian'] ?? '',
            'bidang_penelitian' => $dataForm['bidang_penelitian'] ?? '',
        ];
    }
}
