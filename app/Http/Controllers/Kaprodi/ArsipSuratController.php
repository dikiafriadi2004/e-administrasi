<?php

namespace App\Http\Controllers\Kaprodi;

use App\Http\Controllers\Controller;
use App\Models\PengajuanSurat;
use Illuminate\View\View;

/**
 * Kaprodi memantau status semua surat mahasiswa yang ditangani Admin
 * (aktif kuliah, izin magang, rekomendasi magang, izin penelitian).
 * Bersifat read-only — kaprodi hanya monitoring, tidak bisa mengubah.
 */
class ArsipSuratController extends Controller
{
    private const JENIS_DITANGANI_ADMIN = [
        'aktif_kuliah' => 'Surat Aktif Kuliah',
        'izin_magang' => 'Izin Magang / PKL',
        'rekomendasi_magang' => 'Rekomendasi Magang',
        'izin_penelitian' => 'Izin Penelitian',
    ];

    private const STATUS_LIST = [
        'diajukan' => 'Diajukan',
        'menunggu_ttd' => 'Menunggu TTD',
        'sudah_ditandatangani' => 'Sudah Ditandatangani',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
    ];

    public function index(): View
    {
        $perPage = (int) min(max((int) request('perPage', 10), 5), 100);

        $query = PengajuanSurat::with(['mahasiswa.user'])
            ->whereIn('jenis_surat', array_keys(self::JENIS_DITANGANI_ADMIN))
            ->latest();

        if (request()->filled('jenis')) {
            $query->where('jenis_surat', request('jenis'));
        }

        if (request()->filled('status')) {
            $query->where('status', request('status'));
        }

        if (request()->filled('q')) {
            $cari = request('q');
            $query->where(function ($q) use ($cari) {
                $q->whereHas('mahasiswa.user', fn ($u) => $u->where('name', 'like', "%{$cari}%"))
                    ->orWhereHas('mahasiswa', fn ($m) => $m->where('nim', 'like', "%{$cari}%"));
            });
        }

        $surat = $query->paginate($perPage)->withQueryString();

        // Statistik ringkas
        $stats = [
            'diajukan' => PengajuanSurat::whereIn('jenis_surat', array_keys(self::JENIS_DITANGANI_ADMIN))->where('status', 'diajukan')->count(),
            'menunggu_ttd' => PengajuanSurat::whereIn('jenis_surat', array_keys(self::JENIS_DITANGANI_ADMIN))->where('status', 'menunggu_ttd')->count(),
            'sudah_ditandatangani' => PengajuanSurat::whereIn('jenis_surat', array_keys(self::JENIS_DITANGANI_ADMIN))->where('status', 'sudah_ditandatangani')->count(),
            'selesai' => PengajuanSurat::whereIn('jenis_surat', array_keys(self::JENIS_DITANGANI_ADMIN))->where('status', 'selesai')->count(),
        ];

        return view('kaprodi.arsip.index', [
            'surat' => $surat,
            'stats' => $stats,
            'jenisList' => self::JENIS_DITANGANI_ADMIN,
            'statusList' => self::STATUS_LIST,
            'perPage' => $perPage,
        ]);
    }
}
