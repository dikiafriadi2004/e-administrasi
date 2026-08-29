<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\TemplateSurat;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class TemplateSuratController extends Controller
{
    private const JENIS_TERSEDIA = [
        'aktif_kuliah' => 'Surat Aktif Kuliah',
        'seminar_proposal' => 'Seminar Proposal',
        'sidang_skripsi' => 'Sidang Skripsi',
        'undangan_penguji' => 'Undangan Penguji',
        'izin_magang' => 'Surat Izin Magang / PKL',
        'rekomendasi_magang' => 'Surat Rekomendasi Magang',
        'izin_penelitian' => 'Surat Izin Penelitian',
    ];

    /** Daftar semua template aktif. */
    public function index(): View
    {
        $templates = TemplateSurat::aktif()
            ->orderBy('jenis_surat')
            ->get()
            ->keyBy('jenis_surat');

        return view('admin.template-surat.index', [
            'templates' => $templates,
            'jenisTersedia' => self::JENIS_TERSEDIA,
        ]);
    }

    /** Form upload template baru. */
    public function upload(string $jenis): View
    {
        abort_unless(array_key_exists($jenis, self::JENIS_TERSEDIA), 404);

        // Kumpulkan semua placeholder untuk jenis surat ini
        $placeholders = $this->getPlaceholders($jenis);

        return view('admin.template-surat.upload', [
            'jenis' => $jenis,
            'labelJenis' => self::JENIS_TERSEDIA[$jenis],
            'placeholders' => $placeholders,
        ]);
    }

    /** Download template aktif (.docx) agar bisa diedit di Word. */
    public function download(string $jenis): StreamedResponse
    {
        abort_unless(array_key_exists($jenis, self::JENIS_TERSEDIA), 404);

        $template = TemplateSurat::aktif()->jenis($jenis)->first();

        abort_unless($template, 404, "Template untuk {$jenis} belum tersedia.");
        abort_unless(Storage::disk('private')->exists($template->path_file), 404, 'File template tidak ditemukan di server.');

        $namaDownload = $jenis.'_template.docx';

        return Storage::disk('private')->download($template->path_file, $namaDownload);
    }

    /** Simpan template baru — hapus file lama dari disk, replace bukan tambah. */
    public function store(Request $request, string $jenis): RedirectResponse
    {
        abort_unless(array_key_exists($jenis, self::JENIS_TERSEDIA), 404);

        $request->validate([
            'template_file' => ['required', 'file', 'mimes:docx', 'max:5120'],
        ], [
            'template_file.mimes' => 'File harus berformat .docx',
            'template_file.max' => 'Ukuran file maksimal 5 MB',
        ]);

        // Hapus semua file template lama dari disk
        $templateLama = TemplateSurat::where('jenis_surat', $jenis)->get();
        foreach ($templateLama as $lama) {
            if ($lama->path_file && Storage::disk('private')->exists($lama->path_file)) {
                Storage::disk('private')->delete($lama->path_file);
            }
        }

        // Hapus semua record lama dari DB (bukan hanya nonaktifkan)
        TemplateSurat::where('jenis_surat', $jenis)->delete();

        // Simpan file baru — selalu pakai nama tetap (tidak ditambah versi)
        $filename = "{$jenis}_v1.docx";
        $path = $request->file('template_file')->storeAs('templates', $filename, 'private');

        TemplateSurat::create([
            'jenis_surat' => $jenis,
            'path_file' => $path,
            'versi' => 1,
            'is_aktif' => true,
        ]);

        return redirect()->route('admin.template-surat.index')
            ->with('success', "Template {$this->labelJenis($jenis)} berhasil diperbarui. File lama telah dihapus otomatis.");
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function labelJenis(string $jenis): string
    {
        return self::JENIS_TERSEDIA[$jenis] ?? $jenis;
    }

    /**
     * Daftar placeholder wajib per jenis surat beserta keterangan.
     * Semua placeholder yang tersedia di SuratGeneratorService::buildPlaceholders().
     *
     * @return array<string, array{desc: string, sumber: string}>
     */
    private function getPlaceholders(string $jenis): array
    {
        // Placeholder universal — ada di SEMUA jenis surat
        $universal = [
            // ── Nomor Surat — pisahkan tiap bagian sebagai placeholder sendiri ──
            // Tulis di template Word: ${nomor_urut}/${kode_institusi}/${kode_prodi}/${bulan_surat}/${tahun_surat}
            '${nomor_urut}' => ['desc' => 'Angka urutan surat (diinput admin, contoh: 2032)',       'sumber' => 'Diisi admin saat generate'],
            '${kode_institusi}' => ['desc' => 'Kode institusi dari Pengaturan (contoh: UN11.F9)',        'sumber' => 'Pengaturan Sistem'],
            '${kode_prodi}' => ['desc' => 'Kode prodi dari Pengaturan (contoh: PK.01.06)',           'sumber' => 'Pengaturan Sistem'],
            '${bulan_surat}' => ['desc' => 'Bulan generate 2 digit otomatis (contoh: 08)',            'sumber' => 'Otomatis saat generate'],
            '${tahun_surat}' => ['desc' => 'Tahun generate 4 digit otomatis (contoh: 2026)',          'sumber' => 'Otomatis saat generate'],
            '${nomor_surat}' => ['desc' => 'Nomor penuh (alternatif jika hanya butuh 1 kolom)',       'sumber' => 'Diisi admin saat generate'],
            // ── Tanggal ──────────────────────────────────────────────────────────────
            '${tanggal_surat}' => ['desc' => 'Tanggal surat (25 Agustus 2026)',                         'sumber' => 'Otomatis saat generate'],
            '${kota_prodi}' => ['desc' => 'Kota untuk tanggal surat (contoh: Banda Aceh)',           'sumber' => 'Pengaturan Sistem'],
            // ── Mahasiswa ────────────────────────────────────────────────────────────
            '${nama_mahasiswa}' => ['desc' => 'Nama lengkap mahasiswa',                                  'sumber' => 'Data Mahasiswa'],
            '${nim}' => ['desc' => 'NIM mahasiswa',                                           'sumber' => 'Data Mahasiswa'],
            '${angkatan}' => ['desc' => 'Tahun angkatan mahasiswa',                                'sumber' => 'Data Mahasiswa'],
            '${alamat_mahasiswa}' => ['desc' => 'Alamat lengkap mahasiswa',                                'sumber' => 'Data Mahasiswa'],
            '${semester_aktif}' => ['desc' => 'Semester aktif (Ganjil / Genap)',                         'sumber' => 'Pengaturan Sistem'],
            '${tahun_akademik}' => ['desc' => 'Tahun akademik (contoh: 2025/2026)',                      'sumber' => 'Pengaturan Sistem'],
            // ── Institusi ────────────────────────────────────────────────────────────
            '${nama_prodi}' => ['desc' => 'Nama program studi',                                      'sumber' => 'Pengaturan Sistem'],
            '${nama_fakultas}' => ['desc' => 'Nama fakultas',                                           'sumber' => 'Pengaturan Sistem'],
            '${nama_universitas}' => ['desc' => 'Nama universitas',                                        'sumber' => 'Pengaturan Sistem'],
            '${alamat_prodi}' => ['desc' => 'Alamat lengkap prodi',                                    'sumber' => 'Pengaturan Sistem'],
            '${telepon_prodi}' => ['desc' => 'Nomor telepon prodi',                                     'sumber' => 'Pengaturan Sistem'],
            '${email_prodi}' => ['desc' => 'Email prodi',                                             'sumber' => 'Pengaturan Sistem'],
            // ── Penandatangan ────────────────────────────────────────────────────────
            '${nama_kaprodi}' => ['desc' => 'Nama & gelar Kepala Program Studi',                       'sumber' => 'Pengaturan Sistem'],
            '${nip_kaprodi}' => ['desc' => 'NIP Kepala Program Studi',                                'sumber' => 'Pengaturan Sistem'],
            '${nama_dekan}' => ['desc' => 'Nama & gelar Dekan (jika surat a.n. Dekan)',              'sumber' => 'Pengaturan Sistem'],
            '${nip_dekan}' => ['desc' => 'NIP Dekan',                                               'sumber' => 'Pengaturan Sistem'],
        ];

        $spesifik = match ($jenis) {
            'aktif_kuliah' => [
                '${keperluan}' => ['desc' => 'Keperluan surat (misal: Magang / PKL)',  'sumber' => 'Diisi mahasiswa'],
                '${tujuan_instansi}' => ['desc' => 'Ditujukan kepada instansi apa',          'sumber' => 'Diisi mahasiswa'],
            ],

            'seminar_proposal' => [
                '${judul_skripsi}' => ['desc' => 'Judul skripsi / proposal',             'sumber' => 'Data Pengajuan Judul'],
                '${bidang_kajian}' => ['desc' => 'Bidang kajian skripsi',                'sumber' => 'Data Pengajuan Judul'],
                '${nama_pembimbing}' => ['desc' => 'Nama dosen pembimbing',                'sumber' => 'Data Dosen'],
                '${nip_pembimbing}' => ['desc' => 'NIP dosen pembimbing',                 'sumber' => 'Data Dosen'],
                '${nama_pembimbing_1}' => ['desc' => 'Alias nama_pembimbing',                'sumber' => 'Data Dosen'],
                '${nama_penguji_1}' => ['desc' => 'Nama dosen penguji I',                 'sumber' => 'Data Dosen'],
                '${nip_penguji_1}' => ['desc' => 'NIP dosen penguji I',                  'sumber' => 'Data Dosen'],
                '${nama_penguji_2}' => ['desc' => 'Nama dosen penguji II (opsional)',      'sumber' => 'Data Dosen'],
                '${nip_penguji_2}' => ['desc' => 'NIP dosen penguji II (opsional)',       'sumber' => 'Data Dosen'],
                '${tanggal_seminar}' => ['desc' => 'Tanggal jadwal seminar',               'sumber' => 'Ditetapkan Kaprodi'],
                '${waktu_sidang}' => ['desc' => 'Waktu seminar (09.00 WIB)',             'sumber' => 'Ditetapkan Kaprodi'],
                '${tempat_sidang}' => ['desc' => 'Tempat / ruangan seminar',             'sumber' => 'Ditetapkan Kaprodi'],
            ],

            'sidang_skripsi' => [
                '${judul_skripsi}' => ['desc' => 'Judul skripsi',                        'sumber' => 'Data Pengajuan Judul'],
                '${bidang_kajian}' => ['desc' => 'Bidang kajian',                        'sumber' => 'Data Pengajuan Judul'],
                '${nama_pembimbing}' => ['desc' => 'Nama dosen pembimbing',                'sumber' => 'Data Dosen'],
                '${nip_pembimbing}' => ['desc' => 'NIP dosen pembimbing',                 'sumber' => 'Data Dosen'],
                '${nama_pembimbing_1}' => ['desc' => 'Alias nama_pembimbing',                'sumber' => 'Data Dosen'],
                '${nama_penguji_1}' => ['desc' => 'Nama dosen penguji I',                 'sumber' => 'Data Dosen'],
                '${nip_penguji_1}' => ['desc' => 'NIP dosen penguji I',                  'sumber' => 'Data Dosen'],
                '${nama_penguji_2}' => ['desc' => 'Nama dosen penguji II (opsional)',      'sumber' => 'Data Dosen'],
                '${nip_penguji_2}' => ['desc' => 'NIP dosen penguji II (opsional)',       'sumber' => 'Data Dosen'],
                '${tanggal_sidang}' => ['desc' => 'Tanggal jadwal sidang',                'sumber' => 'Ditetapkan Kaprodi'],
                '${waktu_sidang}' => ['desc' => 'Waktu sidang (09.00 WIB)',              'sumber' => 'Ditetapkan Kaprodi'],
                '${tempat_sidang}' => ['desc' => 'Tempat / ruangan sidang',              'sumber' => 'Ditetapkan Kaprodi'],
            ],

            'undangan_penguji' => [
                '${judul_skripsi}' => ['desc' => 'Judul skripsi',                        'sumber' => 'Data Pengajuan Judul'],
                '${nama_pembimbing}' => ['desc' => 'Nama dosen pembimbing',                'sumber' => 'Data Dosen'],
                '${nip_pembimbing}' => ['desc' => 'NIP dosen pembimbing',                 'sumber' => 'Data Dosen'],
                '${nama_penguji}' => ['desc' => 'Nama dosen penguji (diundang)',         'sumber' => 'Data Dosen'],
                '${nip_penguji}' => ['desc' => 'NIP dosen penguji',                    'sumber' => 'Data Dosen'],
                '${nama_penguji_1}' => ['desc' => 'Alias nama_penguji',                   'sumber' => 'Data Dosen'],
                '${nama_penguji_2}' => ['desc' => 'Nama dosen penguji II (opsional)',      'sumber' => 'Data Dosen'],
                '${tanggal_sidang}' => ['desc' => 'Tanggal sidang',                       'sumber' => 'Ditetapkan Kaprodi'],
                '${waktu_sidang}' => ['desc' => 'Waktu sidang',                         'sumber' => 'Ditetapkan Kaprodi'],
                '${tempat_sidang}' => ['desc' => 'Tempat sidang',                        'sumber' => 'Ditetapkan Kaprodi'],
            ],

            'izin_magang' => [
                '${nama_instansi}' => ['desc' => 'Nama instansi tujuan magang',           'sumber' => 'Diisi mahasiswa'],
                '${alamat_instansi}' => ['desc' => 'Alamat lengkap instansi',               'sumber' => 'Diisi mahasiswa'],
                '${tanggal_mulai}' => ['desc' => 'Tanggal mulai magang',                  'sumber' => 'Diisi mahasiswa'],
                '${tanggal_selesai}' => ['desc' => 'Tanggal selesai magang',                'sumber' => 'Diisi mahasiswa'],
            ],

            'rekomendasi_magang' => [
                '${nama_instansi}' => ['desc' => 'Nama instansi tujuan magang',           'sumber' => 'Diisi mahasiswa'],
                '${alamat_instansi}' => ['desc' => 'Alamat lengkap instansi',               'sumber' => 'Diisi mahasiswa'],
            ],

            'izin_penelitian' => [
                '${nama_instansi}' => ['desc' => 'Nama instansi / lokasi penelitian',     'sumber' => 'Diisi mahasiswa'],
                '${alamat_instansi}' => ['desc' => 'Alamat lengkap instansi',               'sumber' => 'Diisi mahasiswa'],
                '${judul_penelitian}' => ['desc' => 'Judul penelitian / skripsi',            'sumber' => 'Diisi mahasiswa'],
                '${bidang_penelitian}' => ['desc' => 'Bidang penelitian',                     'sumber' => 'Diisi mahasiswa'],
                '${tanggal_mulai}' => ['desc' => 'Tanggal mulai penelitian',              'sumber' => 'Diisi mahasiswa'],
                '${tanggal_selesai}' => ['desc' => 'Tanggal selesai penelitian',            'sumber' => 'Diisi mahasiswa'],
            ],

            default => [],
        };

        return array_merge($universal, $spesifik);
    }
}
