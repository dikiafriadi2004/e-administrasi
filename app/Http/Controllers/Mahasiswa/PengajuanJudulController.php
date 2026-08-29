<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use Illuminate\Http\Response;
use Illuminate\View\View;

class PengajuanJudulController extends Controller
{
    public function create(): View|Response
    {
        $mahasiswa = auth()->user()->mahasiswa;

        // Guard: cegah duplikasi pengajuan aktif
        $aktif = PengajuanJudul::where('mahasiswa_id', $mahasiswa->id)
            ->whereNotIn('status', ['ditolak'])
            ->first();

        if ($aktif) {
            return view('mahasiswa.pengajuan.terkunci', [
                'pesan' => 'Anda sudah memiliki pengajuan judul yang masih aktif.',
                'linkLabel' => 'Lihat Status Pengajuan',
                'linkUrl' => route('mahasiswa.riwayat.index'),
            ]);
        }

        return view('mahasiswa.pengajuan.judul.create');
    }

    /** Edit judul — boleh saat: ditolak, diajukan, atau disetujui (revisi mandiri) */
    public function edit(PengajuanJudul $pengajuanJudul): View|Response
    {
        abort_unless(
            $pengajuanJudul->mahasiswa_id === auth()->user()->mahasiswa?->id,
            403
        );

        // Jika sudah ada sidang aktif, tidak boleh revisi judul
        $sidangAktif = PengajuanSurat::where('mahasiswa_id', auth()->user()->mahasiswa?->id)
            ->where('jenis_surat', 'sidang_skripsi')
            ->whereNotIn('status', ['ditolak'])
            ->exists();

        if ($sidangAktif) {
            return view('mahasiswa.pengajuan.terkunci', [
                'pesan' => 'Judul tidak dapat direvisi karena pengajuan sidang skripsi sudah aktif.',
                'linkLabel' => 'Kembali ke Riwayat',
                'linkUrl' => route('mahasiswa.riwayat.index'),
            ]);
        }

        // Status yang boleh diedit: diajukan, ditolak, atau disetujui (revisi mandiri setelah seminar)
        if (! in_array($pengajuanJudul->status, ['diajukan', 'ditolak', 'disetujui'])) {
            return view('mahasiswa.pengajuan.terkunci', [
                'pesan' => 'Judul tidak dapat diedit pada status saat ini.',
                'linkLabel' => 'Kembali ke Riwayat',
                'linkUrl' => route('mahasiswa.riwayat.index'),
            ]);
        }

        return view('mahasiswa.pengajuan.judul.edit', [
            'pengajuan' => $pengajuanJudul,
            'isRevisiMandiri' => $pengajuanJudul->status === 'disetujui',
        ]);
    }

    public function show(PengajuanJudul $pengajuanJudul): View
    {
        abort_unless(
            $pengajuanJudul->mahasiswa_id === auth()->user()->mahasiswa?->id,
            403
        );

        $pengajuanJudul->load(['dosenPembimbing', 'dosenPembimbing2', 'berkas', 'statusHistories.changedBy']);

        return view('mahasiswa.pengajuan.judul.show', [
            'pengajuan' => $pengajuanJudul,
        ]);
    }
}
