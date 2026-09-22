<?php

namespace App\Http\Controllers\Admin;

use App\Exports\ArsipSuratExport;
use App\Http\Controllers\Controller;
use App\Models\PengajuanSurat;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class ArsipSuratController extends Controller
{
    private const JENIS_LIST = [
        'aktif_kuliah' => 'Aktif Kuliah',
        'seminar_proposal' => 'Seminar Proposal',
        'sidang_skripsi' => 'Sidang Skripsi',
        'undangan_penguji' => 'Undangan Penguji',
        'izin_magang' => 'Izin Magang / PKL',
        'rekomendasi_magang' => 'Rekomendasi Magang',
        'izin_penelitian' => 'Izin Penelitian',
        'keluar_prodi' => 'Keluar Prodi',
    ];

    private const STATUS_LIST = [
        'diajukan' => 'Diajukan',
        'diverifikasi_admin' => 'Diverifikasi Admin',
        'disetujui' => 'Disetujui',
        'menunggu_ttd' => 'Menunggu TTD',
        'sudah_ditandatangani' => 'Sudah Ditandatangani',
        'selesai' => 'Selesai',
        'ditolak' => 'Ditolak',
    ];

    public function index(Request $request): View
    {
        $request->validate([
            'jenis' => ['nullable', 'in:'.implode(',', array_keys(self::JENIS_LIST))],
            'status' => ['nullable', 'in:'.implode(',', array_keys(self::STATUS_LIST))],
            'q' => ['nullable', 'string', 'max:100'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $perPage = (int) min(max((int) request('perPage', 10), 5), 100);
        $query = PengajuanSurat::with(['mahasiswa.user'])
            ->latest();

        if ($request->filled('jenis')) {
            $query->where('jenis_surat', $request->jenis);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('q')) {
            $cari = $request->q;
            $query->where(function ($q) use ($cari) {
                $q->whereHas('mahasiswa.user', fn ($u) => $u->where('name', 'like', "%{$cari}%"))
                    ->orWhereHas('mahasiswa', fn ($m) => $m->where('nim', 'like', "%{$cari}%"))
                    ->orWhere('nomor_surat', 'like', "%{$cari}%");
            });
        }

        if ($request->filled('dari') && $request->filled('sampai')) {
            $query->whereBetween('created_at', [
                $request->dari.' 00:00:00',
                $request->sampai.' 23:59:59',
            ]);
        }

        $surat = $query->paginate($perPage)->withQueryString();

        return view('admin.arsip.index', [
            'surat' => $surat,
            'jenisList' => self::JENIS_LIST,
            'statusList' => self::STATUS_LIST,
        ]);
    }

    /**
     * Export arsip surat ke file Excel (.xlsx),
     * mengikuti filter yang sama dengan halaman index.
     */
    public function export(Request $request): BinaryFileResponse
    {
        $request->validate([
            'jenis' => ['nullable', 'in:'.implode(',', array_keys(self::JENIS_LIST))],
            'status' => ['nullable', 'in:'.implode(',', array_keys(self::STATUS_LIST))],
            'q' => ['nullable', 'string', 'max:100'],
            'dari' => ['nullable', 'date'],
            'sampai' => ['nullable', 'date', 'after_or_equal:dari'],
        ]);

        $export = new ArsipSuratExport(
            jenis: $request->jenis,
            status: $request->status,
            q: $request->q,
            dari: $request->dari,
            sampai: $request->sampai,
        );

        $namaFile = 'arsip-surat-'.now()->format('Ymd_His').'.xlsx';

        return Excel::download($export, $namaFile);
    }
}
