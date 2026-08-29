<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use Illuminate\View\View;

class RiwayatController extends Controller
{
    public function index(): View
    {
        $mahasiswa = auth()->user()->mahasiswa;
        $perPage = (int) min(max((int) request('perPage', 10), 5), 100);

        // Pengajuan akademik — ditangani Kaprodi
        $pengajuanJudul = PengajuanJudul::where('mahasiswa_id', $mahasiswa->id)
            ->with('dosenPembimbing', 'dosenPembimbing2', 'berkas')
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'judul')
            ->withQueryString();

        $pengajuanSeminar = PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis_surat', 'seminar_proposal')
            ->with('pengajuanJudul.dosenPembimbing', 'dosenPenguji', 'dosenPenguji2', 'berkas')
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'seminar')
            ->withQueryString();

        $pengajuanSidang = PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis_surat', 'sidang_skripsi')
            ->with('dosenPenguji', 'dosenPenguji2', 'berkas')
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'sidang')
            ->withQueryString();

        // Surat — ditangani Admin (termasuk magang dan penelitian)
        $pengajuanSurat = PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
            ->whereIn('jenis_surat', [
                'aktif_kuliah',
                'undangan_penguji',
                'izin_magang',
                'rekomendasi_magang',
                'izin_penelitian',
            ])
            ->with('berkas')
            ->orderByDesc('created_at')
            ->paginate($perPage, ['*'], 'surat')
            ->withQueryString();

        return view('mahasiswa.riwayat.index', compact(
            'pengajuanJudul',
            'pengajuanSeminar',
            'pengajuanSidang',
            'pengajuanSurat',
            'perPage'
        ));
    }
}
