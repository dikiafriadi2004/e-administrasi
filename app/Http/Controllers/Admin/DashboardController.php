<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\PengajuanSurat;
use App\Models\Pengaturan;
use App\Models\User;
use App\Services\RasioDosenService;
use Carbon\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        private readonly RasioDosenService $rasioService
    ) {}

    public function index(): View
    {
        // Surat masuk = semua jenis surat yang ditangani admin, baru diajukan & belum diproses
        $suratMasuk = PengajuanSurat::where('status', 'diajukan')
            ->whereIn('jenis_surat', ['aktif_kuliah', 'izin_magang', 'rekomendasi_magang', 'izin_penelitian'])
            ->count();

        // Sidang yang baru diajukan — butuh verifikasi berkas
        $sidangPerluVerifikasi = PengajuanSurat::where('jenis_surat', 'sidang_skripsi')
            ->where('status', 'diajukan')
            ->where('berkas_diverifikasi', false)
            ->count();

        // Seminar/sidang disetujui Kaprodi tapi jadwal belum ditetapkan Admin
        $menungguJadwal = PengajuanSurat::whereIn('jenis_surat', ['seminar_proposal', 'sidang_skripsi'])
            ->where('status', 'disetujui')
            ->whereNull('tanggal_jadwal')
            ->count();

        // Jadwal yang perlu dibuatkan surat undangan (sudah disetujui Kaprodi, belum ada DOCX)
        $jadwalMenungguSurat = PengajuanSurat::whereIn('jenis_surat', ['seminar_proposal', 'sidang_skripsi'])
            ->where('status', 'disetujui')
            ->whereNotNull('tanggal_jadwal')
            ->whereNull('file_docx')
            ->count();

        // Surat undangan yang sudah digenerate, menunggu di-TTD dan di-scan
        $jadwalMenungguScan = PengajuanSurat::whereIn('jenis_surat', ['seminar_proposal', 'sidang_skripsi'])
            ->where('status', 'menunggu_ttd')
            ->whereNull('file_scan')
            ->count();

        // Cek apakah semester_aktif sudah diperbarui dalam 5 bulan terakhir
        $semesterRecord = Pengaturan::find('semester_aktif');
        $peringatanSemester = false;
        $terakhirUpdateSemester = null;

        if ($semesterRecord) {
            $bulanSejakUpdate = Carbon::parse($semesterRecord->updated_at)->diffInMonths(now());
            if ($bulanSejakUpdate >= 5) {
                $peringatanSemester = true;
                $terakhirUpdateSemester = Carbon::parse($semesterRecord->updated_at)
                    ->locale('id')->isoFormat('D MMMM Y');
            }
        }

        return view('admin.dashboard', [
            'totalMahasiswaAktif' => User::where('role', 'mahasiswa')->where('is_active', true)->count(),
            'totalDosen' => Dosen::count(),
            'suratMasuk' => $suratMasuk,
            'sidangPerluVerifikasi' => $sidangPerluVerifikasi,
            'menungguJadwal' => $menungguJadwal,
            'jadwalMenungguSurat' => $jadwalMenungguSurat,
            'jadwalMenungguScan' => $jadwalMenungguScan,
            'suratBulanIni' => PengajuanSurat::whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)->count(),
            'peringatanSemester' => $peringatanSemester,
            'terakhirUpdateSemester' => $terakhirUpdateSemester,
            'semesterAktif' => Pengaturan::nilai('semester_aktif', '—'),
            'tahunAkademik' => Pengaturan::nilai('tahun_akademik', '—'),
        ]);
    }

    public function rasio(): View
    {
        $tahunAktif = $this->rasioService->getTahunAktif();
        $tahunDipilih = request('tahun', $tahunAktif);
        $tahunTersedia = $this->rasioService->getTahunTersedia();

        if (! in_array($tahunDipilih, $tahunTersedia, true)) {
            $tahunDipilih = $tahunAktif;
        }

        $cacheKey = "rasio_dosen_{$tahunDipilih}";
        $rasio = Cache::remember($cacheKey, 60, fn () => $this->rasioService->getRingkasanRasio($tahunDipilih));

        return view('admin.dashboard-rasio', compact('rasio', 'tahunDipilih', 'tahunAktif', 'tahunTersedia'));
    }
}
