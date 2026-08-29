<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Services\RasioDosenService;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly RasioDosenService $rasioService
    ) {}

    public function index(): View
    {
        $topTersedia = Cache::remember('top_dosen_tersedia', 60, fn () => $this->rasioService->getDaftarDosenTerurut('pembimbing')->take(3)
        );

        // Antrian akademik = semua pengajuan judul/seminar/sidang yang masih diajukan
        $antrianJudul = PengajuanJudul::where('status', 'diajukan')->count();
        $antrianSeminar = PengajuanSurat::where('jenis_surat', 'seminar_proposal')->where('status', 'diajukan')->count();
        $antrianSidang = PengajuanSurat::where('jenis_surat', 'sidang_skripsi')->where('status', 'diajukan')->count();

        return view('kaprodi.dashboard', [
            'totalDosen' => Dosen::count(),
            'antrianCount' => $antrianJudul + $antrianSeminar + $antrianSidang,
            'antrianJudul' => $antrianJudul,
            'antrianSeminar' => $antrianSeminar,
            'antrianSidang' => $antrianSidang,
            'judulDisetujui' => PengajuanJudul::where('status', 'disetujui')
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)->count(),
            'topTersedia' => $topTersedia,
        ]);
    }

    public function rasio(): View
    {
        $tahunAktif = $this->rasioService->getTahunAktif();
        $tahunDipilih = request('tahun', $tahunAktif);
        $tahunTersedia = $this->rasioService->getTahunTersedia();

        // Pastikan tahun dipilih ada di daftar; kalau tidak, fallback ke aktif
        if (! in_array($tahunDipilih, $tahunTersedia, true)) {
            $tahunDipilih = $tahunAktif;
        }

        $cacheKey = "rasio_dosen_{$tahunDipilih}";
        $rasio = Cache::remember($cacheKey, 60, fn () => $this->rasioService->getRingkasanRasio($tahunDipilih));

        return view('kaprodi.dashboard-rasio', compact('rasio', 'tahunDipilih', 'tahunAktif', 'tahunTersedia'));
    }
}
