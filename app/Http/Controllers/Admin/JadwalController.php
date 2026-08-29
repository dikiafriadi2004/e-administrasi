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

    /** Daftar semua pengajuan seminar & sidang yang sudah dijadwalkan */
    public function index(): View
    {
        $perPage = (int) min(max((int) request('perPage', 10), 5), 100);
        $jadwal = PengajuanSurat::whereIn('jenis_surat', ['seminar_proposal', 'sidang_skripsi'])
            ->whereNotNull('tanggal_jadwal')
            ->with([
                'mahasiswa.user',
                'pengajuanJudul.dosenPembimbing',
                'pengajuanJudul.dosenPembimbing2',
                'dosenPenguji',
                'dosenPenguji2',
            ])
            ->orderBy('tanggal_jadwal')
            ->paginate($perPage)
            ->withQueryString();

        return view('admin.jadwal.index', array_merge(compact('jadwal'), ['perPage' => $perPage]));
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

        // Hapus file undangan lama jika ada
        if ($pengajuan->file_scan) {
            Storage::disk('private')->delete($pengajuan->file_scan);
        }

        $path = $request->file('file_undangan')->storeAs(
            "undangan/{$pengajuan->id}",
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
}
