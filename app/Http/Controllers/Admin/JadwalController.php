<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\SuratGenerationException;
use App\Http\Controllers\Controller;
use App\Models\BerkasPengajuan;
use App\Models\PengajuanSurat;
use App\Models\StatusHistory;
use App\Services\NomorSuratService;
use App\Services\PengajuanStateService;
use App\Services\SuratGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Admin: lihat semua jadwal yang sudah ditetapkan kaprodi (seminar & sidang)
 * dan generate surat undangan + upload scan ke masing-masing mahasiswa.
 */
class JadwalController extends Controller
{
    public function __construct(
        private readonly SuratGeneratorService $generator,
        private readonly PengajuanStateService $stateService,
        private readonly NomorSuratService $nomorService,
    ) {}

    /** Daftar semua pengajuan seminar & sidang yang sudah disetujui Kaprodi */
    public function index(): View
    {
        $perPage = (int) min(max((int) request('perPage', 10), 5), 100);

        // Sudah ada jadwal — tampil di tabel utama
        $jadwal = PengajuanSurat::whereIn('jenis_surat', ['seminar_proposal', 'sidang_skripsi'])
            ->whereNotNull('tanggal_jadwal')
            ->with([
                'mahasiswa.user',
                'pengajuanJudul.dosenPembimbing',
                'dosenPenguji',
                'dosenPenguji2',
            ])
            ->orderBy('tanggal_jadwal')
            ->paginate($perPage, ['*'], 'jadwal')
            ->withQueryString();

        // Disetujui Kaprodi tapi jadwal BELUM ditetapkan Admin — perlu tindakan
        $menungguJadwal = PengajuanSurat::whereIn('jenis_surat', ['seminar_proposal', 'sidang_skripsi'])
            ->where('status', 'disetujui')
            ->whereNull('tanggal_jadwal')
            ->with(['mahasiswa.user', 'dosenPenguji', 'dosenPenguji2'])
            ->orderBy('updated_at')
            ->paginate($perPage, ['*'], 'menunggu')
            ->withQueryString();

        // Sidang baru diajukan — perlu verifikasi berkas oleh Admin
        $sidangPerluVerifikasi = PengajuanSurat::where('jenis_surat', 'sidang_skripsi')
            ->where('status', 'diajukan')
            ->with(['mahasiswa.user', 'pengajuanJudul'])
            ->orderBy('created_at')
            ->paginate($perPage, ['*'], 'verifikasi')
            ->withQueryString();

        return view('admin.jadwal.index', compact('jadwal', 'menungguJadwal', 'sidangPerluVerifikasi', 'perPage'));
    }

    /** Detail satu jadwal */
    public function show(PengajuanSurat $pengajuan): View
    {
        $pengajuan->load([
            'mahasiswa.user',
            'pengajuanJudul.dosenPembimbing',
            'pengajuanJudul.dosenPembimbing2',
            'dosenPenguji',
            'dosenPenguji2',
            'berkas',
            'statusHistories.changedBy',
        ]);

        return view('admin.jadwal.show', [
            'pengajuan' => $pengajuan,
            'nomorSuffix' => $this->nomorService->getSuffix(),
        ]);
    }

    /**
     * Admin verifikasi berkas sidang skripsi.
     * Jika berkas kurang → kembalikan dengan catatan ke mahasiswa.
     * Jika berkas OK → tandai terverifikasi, Kaprodi bisa ACC.
     */
    public function verifikasiBerkas(Request $request, PengajuanSurat $pengajuan): RedirectResponse
    {
        abort_unless($pengajuan->jenis_surat === 'sidang_skripsi', 404);
        abort_unless($pengajuan->status === 'diajukan', 403, 'Hanya bisa verifikasi pengajuan berstatus diajukan.');

        $request->validate([
            'keputusan' => ['required', 'in:lulus,kembalikan'],
            'catatan' => ['required_if:keputusan,kembalikan', 'nullable', 'string', 'max:1000'],
        ], [
            'keputusan.required' => 'Pilih keputusan verifikasi.',
            'catatan.required_if' => 'Catatan wajib diisi jika berkas dikembalikan.',
        ]);

        $lulus = $request->keputusan === 'lulus';
        $catatan = $request->catatan ?? '';

        $this->stateService->verifikasiBerkas($pengajuan, auth()->user(), $catatan, $lulus);

        if ($lulus) {
            return back()->with('success', 'Berkas dinyatakan lengkap. Pengajuan sudah bisa di-ACC oleh Kaprodi.');
        }

        return back()->with('warning', 'Berkas dikembalikan ke mahasiswa dengan catatan: '.$catatan);
    }

    /**
     * Admin tetapkan jadwal (tanggal/waktu/tempat) untuk seminar atau sidang
     * yang sudah disetujui Kaprodi (penguji sudah ditetapkan, jadwal belum).
     */
    public function tetapkanJadwal(Request $request, PengajuanSurat $pengajuan): RedirectResponse
    {
        abort_unless(
            in_array($pengajuan->jenis_surat, ['seminar_proposal', 'sidang_skripsi']),
            404
        );
        abort_unless($pengajuan->status === 'disetujui', 403, 'Jadwal hanya bisa ditetapkan pada pengajuan berstatus disetujui.');

        $jenisLabel = $pengajuan->jenis_surat === 'seminar_proposal' ? 'Seminar Proposal' : 'Sidang Skripsi';

        $request->validate([
            'tanggal_jadwal' => ['required', 'date'],
            'waktu_jadwal' => ['required', 'string', 'max:50'],
            'tempat_jadwal' => ['required', 'string', 'max:255'],
        ], [
            'tanggal_jadwal.required' => 'Tanggal jadwal wajib diisi.',
            'waktu_jadwal.required' => 'Waktu jadwal wajib diisi.',
            'tempat_jadwal.required' => 'Tempat / ruangan wajib diisi.',
        ]);

        $pengajuan->update([
            'tanggal_jadwal' => $request->tanggal_jadwal,
            'waktu_jadwal' => $request->waktu_jadwal,
            'tempat_jadwal' => $request->tempat_jadwal,
        ]);

        StatusHistory::create([
            'model_type' => PengajuanSurat::class,
            'model_id' => $pengajuan->id,
            'status_lama' => 'disetujui',
            'status_baru' => 'disetujui',
            'catatan' => "Jadwal {$jenisLabel} ditetapkan oleh admin: "
                ."{$request->tanggal_jadwal}, {$request->waktu_jadwal}, {$request->tempat_jadwal}.",
            'changed_by' => auth()->id(),
            'created_at' => now(),
        ]);

        return back()->with('success', "Jadwal {$jenisLabel} berhasil ditetapkan. Silakan generate surat undangan.");
    }

    /**
     * Generate DOCX surat undangan seminar/sidang.
     * Admin input angka urutan, suffix otomatis dari Pengaturan.
     */
    public function generateUndangan(Request $request, PengajuanSurat $pengajuan): RedirectResponse
    {
        $request->validate([
            'nomor_urutan' => ['required', 'string', 'max:20'],
        ], [
            'nomor_urutan.required' => 'Nomor urutan surat wajib diisi sebelum generate.',
        ]);

        // Simpan nomor_urut saja ke database
        $nomorSurat = trim($request->nomor_urutan);
        $pengajuan->update(['nomor_surat' => $nomorSurat]);
        $pengajuan->refresh();

        try {
            $this->generator->generate($pengajuan);

            // Ubah status disetujui → menunggu_ttd
            if ($pengajuan->status === 'disetujui') {
                $pengajuan->update(['status' => 'menunggu_ttd']);

                StatusHistory::create([
                    'model_type' => PengajuanSurat::class,
                    'model_id' => $pengajuan->id,
                    'status_lama' => 'disetujui',
                    'status_baru' => 'menunggu_ttd',
                    'catatan' => 'Surat undangan digenerate oleh admin, menunggu tanda tangan Kaprodi.',
                    'changed_by' => auth()->id(),
                    'created_at' => now(),
                ]);
            }

            return back()->with('success', 'Surat undangan berhasil dibuat (DOCX). Unduh, cetak, dan minta tanda tangan Kaprodi, kemudian upload scan.');
        } catch (SuratGenerationException $e) {
            Log::error('Generate undangan gagal', [
                'pengajuan_id' => $pengajuan->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', $e->getMessage());
        }
    }

    /**
     * Download DOCX surat undangan untuk dicetak.
     */
    public function downloadUndangan(PengajuanSurat $pengajuan): mixed
    {
        // Prioritas: scan (sudah TTD) → docx (belum TTD)
        $path = $pengajuan->file_scan ?? $pengajuan->file_docx;
        abort_if(! $path, 404, 'Surat undangan belum tersedia.');
        abort_unless(Storage::disk('private')->exists($path), 404, 'File tidak ditemukan.');

        $ext = $pengajuan->file_scan ? 'pdf' : 'docx';
        $nama = PengajuanSurat::namaFileDownload($pengajuan, $ext);

        return Storage::disk('private')->download($path, $nama);
    }

    /**
     * Upload surat undangan (hasil scan TTD kaprodi) ke pengajuan mahasiswa.
     * File ini akan bisa didownload mahasiswa dari dashboard.
     */
    public function uploadUndangan(Request $request, PengajuanSurat $pengajuan): RedirectResponse
    {
        $request->validate([
            'file_undangan' => ['required', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'file_undangan.required' => 'File undangan wajib diupload.',
            'file_undangan.mimes' => 'File harus berformat PDF.',
            'file_undangan.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $pengajuan->loadMissing('mahasiswa');
        $nim = $pengajuan->mahasiswa?->nim ?? 'unknown';

        // Hapus file undangan lama jika ada
        if ($pengajuan->file_scan) {
            Storage::disk('private')->delete($pengajuan->file_scan);
        }

        $path = $request->file('file_undangan')->storeAs(
            "undangan/{$nim}/{$pengajuan->jenis_surat}",
            'undangan_'.now()->format('Ymd_His').'.pdf',
            'private'
        );

        // Simpan status lama SEBELUM update agar StatusHistory akurat
        $statusLama = $pengajuan->status;

        $pengajuan->update([
            'file_scan' => $path,
            'status' => 'sudah_ditandatangani',
        ]);

        StatusHistory::create([
            'model_type' => PengajuanSurat::class,
            'model_id' => $pengajuan->id,
            'status_lama' => $statusLama,
            'status_baru' => 'sudah_ditandatangani',
            'catatan' => 'Surat undangan diupload oleh admin. Mahasiswa dapat mengunduh.',
            'changed_by' => auth()->id(),
            'created_at' => now(),
        ]);

        return back()->with('success', 'Surat undangan berhasil diupload. Mahasiswa kini dapat mengunduhnya.');
    }

    /** Download berkas syarat mahasiswa (untuk admin) */
    public function downloadBerkas(BerkasPengajuan $berkas): StreamedResponse
    {
        abort_unless(Storage::disk('private')->exists($berkas->path_file), 404, 'File tidak ditemukan.');

        return Storage::disk('private')->download($berkas->path_file, $berkas->nama_asli);
    }

    /**
     * Upload absensi seminar proposal (oleh admin setelah seminar selesai).
     * File ini menjadi syarat mahasiswa untuk mengajukan izin penelitian.
     * Hanya berlaku untuk jenis_surat = seminar_proposal.
     */
    public function uploadAbsensi(Request $request, PengajuanSurat $pengajuan): RedirectResponse
    {
        abort_unless($pengajuan->jenis_surat === 'seminar_proposal', 404, 'Hanya untuk seminar proposal.');

        $request->validate([
            'file_absensi' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
        ], [
            'file_absensi.required' => 'File absensi wajib diupload.',
            'file_absensi.mimes' => 'File harus berformat PDF, JPG, atau PNG.',
            'file_absensi.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        // Hapus file absensi lama jika ada
        if ($pengajuan->file_absensi_seminar) {
            Storage::disk('private')->delete($pengajuan->file_absensi_seminar);
        }

        $pengajuan->loadMissing('mahasiswa');
        $nim = $pengajuan->mahasiswa?->nim ?? 'unknown';
        $ext = $request->file('file_absensi')->getClientOriginalExtension();
        $path = $request->file('file_absensi')->storeAs(
            "absensi/{$nim}",
            'absensi_'.now()->format('Ymd_His').'.'.$ext,
            'private'
        );

        $pengajuan->update(['file_absensi_seminar' => $path]);

        return back()->with('success', 'Absensi seminar berhasil diupload. Mahasiswa kini dapat melihatnya.');
    }

    /**
     * Download absensi seminar proposal (oleh admin atau mahasiswa).
     */
    public function downloadAbsensi(PengajuanSurat $pengajuan): StreamedResponse
    {
        abort_unless($pengajuan->file_absensi_seminar, 404, 'Absensi belum tersedia.');
        abort_unless(Storage::disk('private')->exists($pengajuan->file_absensi_seminar), 404, 'File tidak ditemukan.');

        $nama = 'absensi_seminar_'.$pengajuan->mahasiswa->nim.'.'.pathinfo($pengajuan->file_absensi_seminar, PATHINFO_EXTENSION);

        return Storage::disk('private')->download($pengajuan->file_absensi_seminar, $nama);
    }
}
