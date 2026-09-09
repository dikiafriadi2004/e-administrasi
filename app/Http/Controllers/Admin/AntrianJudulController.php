<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\InvalidStateTransitionException;
use App\Http\Controllers\Controller;
use App\Models\PengajuanJudul;
use App\Services\PengajuanStateService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Admin memeriksa kelengkapan berkas pengajuan judul skripsi.
 *
 * Alur:
 *   Mahasiswa ajukan (diajukan)
 *     → Admin periksa berkas → verifikasi (diverifikasi_admin)
 *     → Kaprodi tentukan pembimbing (disetujui)
 *     atau tolak kapanpun
 */
class AntrianJudulController extends Controller
{
    public function __construct(
        private readonly PengajuanStateService $stateService,
    ) {}

    /** Daftar pengajuan judul yang masuk (status diajukan) */
    public function index(): View
    {
        $perPage = (int) min(max((int) request('perPage', 10), 5), 100);

        // Baru masuk — belum diperiksa Admin
        $antrian = PengajuanJudul::where('status', 'diajukan')
            ->with('mahasiswa.user', 'berkas')
            ->orderBy('created_at')
            ->paginate($perPage, ['*'], 'antrian')
            ->withQueryString();

        // Sudah diverifikasi Admin — menunggu Kaprodi
        $menungguKaprodi = PengajuanJudul::where('status', 'diverifikasi_admin')
            ->with('mahasiswa.user')
            ->orderBy('updated_at')
            ->paginate($perPage, ['*'], 'kaprodi')
            ->withQueryString();

        return view('admin.antrian.judul.index', compact('antrian', 'menungguKaprodi', 'perPage'));
    }

    /** Detail satu pengajuan judul */
    public function show(PengajuanJudul $pengajuan): View
    {
        $pengajuan->load([
            'mahasiswa.user',
            'berkas',
            'statusHistories.changedBy',
        ]);

        return view('admin.antrian.judul.show', compact('pengajuan'));
    }

    /**
     * Admin verifikasi berkas — diteruskan ke Kaprodi.
     * Transisi: diajukan → diverifikasi_admin
     */
    public function verifikasi(PengajuanJudul $pengajuan): RedirectResponse
    {
        try {
            $this->stateService->verifikasiJudul($pengajuan, auth()->user());
        } catch (InvalidStateTransitionException $e) {
            return back()->with('error', 'Gagal verifikasi: '.$e->getMessage());
        }

        return redirect()->route('admin.antrian-judul.index')
            ->with('success', 'Berkas dinyatakan lengkap. Pengajuan diteruskan ke Kaprodi.');
    }

    /**
     * Admin tolak pengajuan judul dengan alasan.
     */
    public function tolak(Request $request, PengajuanJudul $pengajuan): RedirectResponse
    {
        $request->validate([
            'catatan_penolakan' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'catatan_penolakan.required' => 'Alasan penolakan wajib diisi.',
            'catatan_penolakan.min' => 'Alasan penolakan minimal 10 karakter.',
        ]);

        try {
            $this->stateService->tolak($pengajuan, auth()->user(), $request->catatan_penolakan);
        } catch (InvalidStateTransitionException $e) {
            return back()->with('error', 'Gagal menolak: '.$e->getMessage());
        }

        return redirect()->route('admin.antrian-judul.index')
            ->with('success', 'Pengajuan judul ditolak.');
    }
}
