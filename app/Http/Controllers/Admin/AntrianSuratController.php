<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PengajuanSurat;
use App\Services\NomorSuratService;
use App\Services\PengajuanStateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin mengelola SURAT yang diajukan mahasiswa.
 *
 * Jenis surat yang dikelola admin:
 *   - aktif_kuliah       (mahasiswa ajukan → admin generate → kaprodi TTD → admin upload scan)
 *   - izin_magang        (mahasiswa ajukan → admin buat surat rekomendasi → upload scan)
 *   - rekomendasi_magang (mahasiswa ajukan → admin buat surat rekomendasi → upload scan)
 *   - izin_penelitian    (mahasiswa ajukan → admin buat surat → upload scan)
 *
 * Admin TIDAK mengelola pengajuan akademik (judul/seminar/sidang) — itu urusan Kaprodi.
 */
class AntrianSuratController extends Controller
{
    private const JENIS_SURAT_LABEL = [
        'aktif_kuliah' => 'Surat Aktif Kuliah',
        'seminar_proposal' => 'Seminar Proposal',
        'sidang_skripsi' => 'Sidang Skripsi',
        'undangan_penguji' => 'Undangan Penguji',
        'izin_magang' => 'Izin Magang / PKL',
        'rekomendasi_magang' => 'Rekomendasi Magang',
        'izin_penelitian' => 'Izin Penelitian',
    ];

    /** Jenis surat yang ditangani admin (dari mahasiswa) */
    private const JENIS_DITANGANI_ADMIN = [
        'aktif_kuliah',
        'izin_magang',
        'rekomendasi_magang',
        'izin_penelitian',
    ];

    public function __construct(
        private readonly PengajuanStateService $stateService,
        private readonly NomorSuratService $nomorService,
    ) {}

    /** Daftar surat yang diajukan mahasiswa, dikelompokkan per status. */
    public function index(): View
    {
        $perPage = (int) min(max((int) request('perPage', 10), 5), 100);
        // Surat yang baru masuk (menunggu diproses admin)
        $suratMasuk = PengajuanSurat::where('status', 'diajukan')
            ->whereIn('jenis_surat', self::JENIS_DITANGANI_ADMIN)
            ->with('mahasiswa.user')
            ->orderBy('created_at')
            ->paginate($perPage, ['*'], 'masuk')
            ->withQueryString();

        // Surat yang sedang dalam proses (generate / menunggu TTD)
        $suratProses = PengajuanSurat::whereIn('status', ['menunggu_ttd', 'sudah_ditandatangani'])
            ->whereIn('jenis_surat', array_merge(self::JENIS_DITANGANI_ADMIN, ['undangan_penguji']))
            ->with('mahasiswa.user')
            ->orderByDesc('updated_at')
            ->paginate($perPage, ['*'], 'proses')
            ->withQueryString();

        return view('admin.surat.index', compact('suratMasuk', 'suratProses'));
    }

    /** Detail satu surat. */
    public function show(PengajuanSurat $surat): View
    {
        $surat->load([
            'mahasiswa.user',
            'mahasiswa',
            'pengajuanJudul.dosenPembimbing',
            'pengajuanJudul.dosenPembimbing2',
            'dosenPenguji',
            'dosenPenguji2',
            'berkas',
            'statusHistories.changedBy',
        ]);

        return view('admin.surat.show', [
            'surat' => $surat,
            'jenisLabel' => self::JENIS_SURAT_LABEL[$surat->jenis_surat] ?? $surat->jenis_surat,
            'nomorSuffix' => $this->nomorService->getSuffix(),
        ]);
    }

    /** Admin tolak surat yang diajukan mahasiswa (sebelum generate). */
    public function tolak(Request $request, PengajuanSurat $surat): RedirectResponse
    {
        $request->validate([
            'catatan_penolakan' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'catatan_penolakan.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $this->stateService->tolak($surat, auth()->user(), $request->catatan_penolakan);

        return redirect()->route('admin.surat.index')
            ->with('success', 'Pengajuan surat ditolak.');
    }

    /** Tandai selesai setelah scan diupload. */
    public function selesaikan(PengajuanSurat $surat): RedirectResponse
    {
        $this->stateService->selesaikan($surat, auth()->user());

        return back()->with('success', 'Surat ditandai selesai. Mahasiswa dapat mengunduh scan.');
    }
}
