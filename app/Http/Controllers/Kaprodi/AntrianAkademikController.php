<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\BerkasPengajuan;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Services\PengajuanStateService;
use App\Services\RasioDosenService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Kaprodi mengelola SEMUA pengajuan akademik:
 *   - Pengajuan Judul Skripsi    (langsung dari mahasiswa)
 *   - Pengajuan Seminar Proposal (langsung dari mahasiswa)
 *   - Pengajuan Sidang Skripsi   (langsung dari mahasiswa)
 *
 * Alur: diajukan → disetujui (atau ditolak)
 * Kaprodi TIDAK mengelola surat.
 */
class AntrianAkademikController extends Controller
{
    public function __construct(
        private readonly PengajuanStateService $stateService,
        private readonly RasioDosenService $rasioService
    ) {}

    /** Antrian semua pengajuan akademik yang belum diputuskan (status: diajukan). */
    public function index(): View
    {
        $perPage = (int) min(max((int) request('perPage', 10), 5), 100);

        $pengajuanJudul = PengajuanJudul::where('status', 'diajukan')
            ->with('mahasiswa.user')
            ->orderBy('created_at')
            ->paginate($perPage, ['*'], 'judul')
            ->withQueryString();

        $pengajuanSeminar = PengajuanSurat::where('jenis_surat', 'seminar_proposal')
            ->where('status', 'diajukan')
            ->with('mahasiswa.user', 'pengajuanJudul')
            ->orderBy('created_at')
            ->paginate($perPage, ['*'], 'seminar')
            ->withQueryString();

        $pengajuanSidang = PengajuanSurat::where('jenis_surat', 'sidang_skripsi')
            ->where('status', 'diajukan')
            ->with('mahasiswa.user', 'pengajuanJudul.dosenPembimbing')
            ->orderBy('created_at')
            ->paginate($perPage, ['*'], 'sidang')
            ->withQueryString();

        // Riwayat yang sudah diproses (disetujui/ditolak) — untuk referensi Kaprodi
        $riwayatSeminar = PengajuanSurat::where('jenis_surat', 'seminar_proposal')
            ->whereIn('status', ['disetujui', 'menunggu_ttd', 'sudah_ditandatangani', 'selesai', 'ditolak'])
            ->with('mahasiswa.user', 'dosenPenguji', 'dosenPenguji2')
            ->orderByDesc('updated_at')
            ->paginate($perPage, ['*'], 'rwyseminar')
            ->withQueryString();

        $riwayatSidang = PengajuanSurat::where('jenis_surat', 'sidang_skripsi')
            ->whereIn('status', ['disetujui', 'menunggu_ttd', 'sudah_ditandatangani', 'selesai', 'ditolak'])
            ->with('mahasiswa.user', 'dosenPenguji', 'dosenPenguji2')
            ->orderByDesc('updated_at')
            ->paginate($perPage, ['*'], 'rwysidang')
            ->withQueryString();

        return view('kaprodi.akademik.index', compact(
            'pengajuanJudul',
            'pengajuanSeminar',
            'pengajuanSidang',
            'riwayatSeminar',
            'riwayatSidang',
            'perPage'
        ));
    }

    // ─── Judul Skripsi ────────────────────────────────────────────────────────

    public function showJudul(PengajuanJudul $pengajuan): View
    {
        $pengajuan->load(['mahasiswa.user', 'dosenPembimbing', 'dosenPembimbing2', 'berkas', 'statusHistories.changedBy']);
        $dosenTerurut = $this->rasioService->getDaftarDosenTerurut('pembimbing');

        return view('kaprodi.akademik.show-judul', compact('pengajuan', 'dosenTerurut'));
    }

    /** Setujui judul + tetapkan 1 pembimbing: diajukan → disetujui */
    public function setujuiJudul(Request $request, PengajuanJudul $pengajuan): RedirectResponse
    {
        $request->validate([
            'dosen_pembimbing_id' => ['required', 'exists:dosens,id'],
        ], [
            'dosen_pembimbing_id.required' => 'Pilih dosen pembimbing sebelum menyetujui.',
        ]);

        $pengajuan->update([
            'dosen_pembimbing_id' => $request->dosen_pembimbing_id,
            'dosen_pembimbing_2_id' => null,
        ]);
        $this->stateService->setujuiJudul($pengajuan, auth()->user());

        return redirect()->route('kaprodi.akademik.index')
            ->with('success', 'Judul disetujui dan dosen pembimbing ditetapkan.');
    }

    // ─── Seminar Proposal ────────────────────────────────────────────────────

    public function showSeminar(PengajuanSurat $pengajuan): View
    {
        $pengajuan->load([
            'mahasiswa.user',
            'pengajuanJudul.dosenPembimbing',
            'dosenPenguji',
            'dosenPenguji2',
            'berkas',
            'statusHistories.changedBy',
        ]);

        $pembimbingId = $pengajuan->pengajuanJudul?->dosen_pembimbing_id;
        $dosenTerurut = $this->rasioService->getDaftarDosenTerurut('penguji', $pembimbingId);

        return view('kaprodi.akademik.show-seminar', compact('pengajuan', 'dosenTerurut'));
    }

    /** Setujui seminar proposal + tetapkan 2 penguji (jadwal ditetapkan Admin) */
    public function setujuiSeminar(Request $request, PengajuanSurat $pengajuan): RedirectResponse
    {
        $request->validate([
            'dosen_penguji_id' => ['required', 'exists:dosens,id'],
            'dosen_penguji_2_id' => ['required', 'exists:dosens,id', 'different:dosen_penguji_id'],
            'catatan_kaprodi' => ['nullable', 'string', 'max:1000'],
        ], [
            'dosen_penguji_id.required' => 'Pilih dosen penguji 1 sebelum menyetujui.',
            'dosen_penguji_2_id.required' => 'Pilih dosen penguji 2 sebelum menyetujui.',
            'dosen_penguji_2_id.different' => 'Penguji 2 tidak boleh sama dengan Penguji 1.',
        ]);

        $pembimbingId = $pengajuan->pengajuanJudul?->dosen_pembimbing_id;

        if ($pembimbingId && $request->dosen_penguji_id == $pembimbingId) {
            return back()->with('error', 'Dosen penguji 1 tidak boleh sama dengan dosen pembimbing.');
        }

        if ($request->dosen_penguji_2_id == $pembimbingId) {
            return back()->with('error', 'Dosen penguji 2 tidak boleh sama dengan dosen pembimbing.');
        }

        $pengajuan->update([
            'dosen_penguji_id' => $request->dosen_penguji_id,
            'dosen_penguji_2_id' => $request->dosen_penguji_2_id,
            'catatan_kaprodi' => $request->catatan_kaprodi,
        ]);

        $this->stateService->setujuiPengajuanAkademik($pengajuan, auth()->user());

        return redirect()->route('kaprodi.akademik.index')
            ->with('success', 'Seminar proposal disetujui dan penguji ditetapkan. Admin akan menentukan jadwal.');
    }

    // ─── Sidang Skripsi ───────────────────────────────────────────────────────

    public function showSidang(PengajuanSurat $pengajuan): View
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

        $pembimbingId = $pengajuan->pengajuanJudul?->dosen_pembimbing_id;
        $dosenTerurut = $this->rasioService->getDaftarDosenTerurut('penguji', $pembimbingId);

        // Auto-fill: ambil penguji dari seminar proposal sebelumnya untuk referensi
        $seminarSebelumnya = PengajuanSurat::where('mahasiswa_id', $pengajuan->mahasiswa_id)
            ->where('jenis_surat', 'seminar_proposal')
            ->whereNotNull('dosen_penguji_id')
            ->latest()
            ->first();

        return view('kaprodi.akademik.show-sidang', compact(
            'pengajuan',
            'dosenTerurut',
            'seminarSebelumnya'
        ));
    }

    /** Setujui sidang + tetapkan 2 penguji (jadwal ditetapkan Admin) */
    public function setujuiSidang(Request $request, PengajuanSurat $pengajuan): RedirectResponse
    {
        $request->validate([
            'dosen_penguji_id' => ['required', 'exists:dosens,id'],
            'dosen_penguji_2_id' => ['required', 'exists:dosens,id', 'different:dosen_penguji_id'],
            'catatan_kaprodi' => ['nullable', 'string', 'max:1000'],
        ], [
            'dosen_penguji_id.required' => 'Pilih dosen penguji 1 sebelum menyetujui.',
            'dosen_penguji_2_id.required' => 'Pilih dosen penguji 2 sebelum menyetujui.',
            'dosen_penguji_2_id.different' => 'Penguji 2 tidak boleh sama dengan Penguji 1.',
        ]);

        $pembimbingId = $pengajuan->pengajuanJudul?->dosen_pembimbing_id;

        if (in_array($request->dosen_penguji_id, array_filter([$pembimbingId]))) {
            return back()->with('error', 'Dosen penguji 1 tidak boleh sama dengan dosen pembimbing.');
        }

        if (in_array($request->dosen_penguji_2_id, array_filter([$pembimbingId]))) {
            return back()->with('error', 'Dosen penguji 2 tidak boleh sama dengan dosen pembimbing.');
        }

        $pengajuan->update([
            'dosen_penguji_id' => $request->dosen_penguji_id,
            'dosen_penguji_2_id' => $request->dosen_penguji_2_id,
            'catatan_kaprodi' => $request->catatan_kaprodi,
        ]);
        $this->stateService->setujuiPengajuanAkademik($pengajuan, auth()->user());

        return redirect()->route('kaprodi.akademik.index')
            ->with('success', 'Pengajuan sidang disetujui dan dosen penguji ditetapkan. Admin akan menentukan jadwal.');
    }

    // ─── Tolak (semua jenis akademik) ────────────────────────────────────────

    public function tolakJudul(Request $request, PengajuanJudul $pengajuan): RedirectResponse
    {
        return $this->tolakPengajuan($request, $pengajuan);
    }

    public function tolakSeminar(Request $request, PengajuanSurat $pengajuan): RedirectResponse
    {
        return $this->tolakPengajuan($request, $pengajuan);
    }

    public function tolakSidang(Request $request, PengajuanSurat $pengajuan): RedirectResponse
    {
        return $this->tolakPengajuan($request, $pengajuan);
    }

    private function tolakPengajuan(Request $request, mixed $pengajuan): RedirectResponse
    {
        $request->validate([
            'catatan_penolakan' => ['required', 'string', 'min:10', 'max:1000'],
        ], [
            'catatan_penolakan.required' => 'Alasan penolakan wajib diisi.',
            'catatan_penolakan.min' => 'Alasan penolakan minimal 10 karakter.',
        ]);

        $this->stateService->tolak($pengajuan, auth()->user(), $request->catatan_penolakan);

        return redirect()->route('kaprodi.akademik.index')
            ->with('success', 'Pengajuan ditolak.');
    }

    /** Download berkas syarat mahasiswa (untuk kaprodi) */
    public function downloadBerkas(BerkasPengajuan $berkas): StreamedResponse
    {
        abort_unless(Storage::disk('private')->exists($berkas->path_file), 404, 'File tidak ditemukan.');

        return Storage::disk('private')->download($berkas->path_file, $berkas->nama_asli);
    }
}
