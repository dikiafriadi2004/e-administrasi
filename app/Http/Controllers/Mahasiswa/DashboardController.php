<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $mahasiswa = auth()->user()->mahasiswa;

        $judulAktif = null;
        $suratAktif = collect();
        $jadwalMendatang = collect();

        if ($mahasiswa) {
            $judulAktif = PengajuanJudul::where('mahasiswa_id', $mahasiswa->id)
                ->whereNotIn('status', ['ditolak'])
                ->with('dosenPembimbing', 'dosenPembimbing2')
                ->latest()
                ->first();

            $suratAktif = PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
                ->whereNotIn('status', ['ditolak', 'selesai'])
                ->latest()
                ->get();

            // Jadwal seminar/sidang yang sudah ditetapkan kaprodi
            // Termasuk menunggu_ttd agar jadwal tidak hilang saat DOCX sedang diproses
            $jadwalMendatang = PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
                ->whereIn('jenis_surat', ['seminar_proposal', 'sidang_skripsi'])
                ->whereNotNull('tanggal_jadwal')
                ->whereIn('status', ['disetujui', 'menunggu_ttd', 'sudah_ditandatangani', 'selesai'])
                ->orderBy('tanggal_jadwal')
                ->get();
        }

        return view('mahasiswa.dashboard', compact('judulAktif', 'suratAktif', 'jadwalMendatang'));
    }
}
