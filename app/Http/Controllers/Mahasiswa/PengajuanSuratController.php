<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\BerkasPengajuan;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PengajuanSuratController extends Controller
{
    /** Tampilkan form Surat Aktif Kuliah. */
    public function createAktifKuliah(): View
    {
        return view('mahasiswa.pengajuan.aktif-kuliah.create');
    }

    /** Tampilkan form Surat Izin Magang. */
    public function createIzinMagang(): View
    {
        return view('mahasiswa.pengajuan.izin-magang.create');
    }

    /** Tampilkan form Surat Rekomendasi Magang. */
    public function createRekomendasiMagang(): View
    {
        return view('mahasiswa.pengajuan.rekomendasi-magang.create');
    }

    /** Tampilkan form Surat Izin Penelitian. */
    public function createIzinPenelitian(): View
    {
        return view('mahasiswa.pengajuan.izin-penelitian.create');
    }

    /** Tampilkan form Seminar Proposal (dengan guard). */
    public function createSeminar(): View|Response
    {
        $judulDisetujui = $this->getJudulDisetujui();

        if (! $judulDisetujui) {
            return view('mahasiswa.pengajuan.terkunci', [
                'pesan' => 'Pengajuan judul skripsi Anda harus disetujui dan pembimbing ditetapkan terlebih dahulu sebelum bisa mengajukan Seminar Proposal.',
                'linkLabel' => 'Lihat Riwayat Pengajuan',
                'linkUrl' => route('mahasiswa.riwayat.index'),
            ]);
        }

        // Blokir jika seminar sudah pernah diajukan dan masih aktif (bukan ditolak)
        $mahasiswaId = auth()->user()->mahasiswa?->id;
        $seminarAktif = PengajuanSurat::where('mahasiswa_id', $mahasiswaId)
            ->where('jenis_surat', 'seminar_proposal')
            ->whereNotIn('status', ['ditolak'])
            ->first();

        if ($seminarAktif) {
            $pesanStatus = match ($seminarAktif->status) {
                'diajukan' => 'Pengajuan seminar proposal Anda sedang menunggu keputusan Kaprodi.',
                'disetujui' => 'Seminar proposal Anda sudah disetujui Kaprodi. Jadwal sedang disiapkan.',
                'menunggu_ttd' => 'Surat undangan seminar sedang diproses (menunggu tanda tangan).',
                'sudah_ditandatangani' => 'Surat undangan seminar sudah tersedia. Silakan download dari riwayat.',
                'selesai' => 'Seminar proposal Anda sudah selesai. Silakan lanjutkan ke sidang skripsi.',
                default => 'Seminar proposal Anda sudah diproses.',
            };

            return view('mahasiswa.pengajuan.terkunci', [
                'pesan' => $pesanStatus,
                'linkLabel' => 'Lihat Riwayat Pengajuan',
                'linkUrl' => route('mahasiswa.riwayat.index'),
            ]);
        }

        return view('mahasiswa.pengajuan.seminar.create', [
            'pengajuanJudul' => $judulDisetujui,
        ]);
    }

    /** Tampilkan form Sidang Skripsi (dengan 2 guard). */
    public function createSidang(): View|Response
    {
        $judulDisetujui = $this->getJudulDisetujui();

        if (! $judulDisetujui) {
            return view('mahasiswa.pengajuan.terkunci', [
                'pesan' => 'Judul skripsi Anda harus disetujui terlebih dahulu.',
                'linkLabel' => 'Lihat Riwayat',
                'linkUrl' => route('mahasiswa.riwayat.index'),
            ]);
        }

        if (! $this->seminarSudahSelesai()) {
            return view('mahasiswa.pengajuan.terkunci', [
                'pesan' => 'Seminar Proposal Anda harus disetujui oleh Kaprodi terlebih dahulu sebelum bisa mengajukan Sidang Skripsi.',
                'linkLabel' => 'Lihat Riwayat',
                'linkUrl' => route('mahasiswa.riwayat.index'),
            ]);
        }

        // Blokir jika sidang sudah pernah diajukan dan masih aktif
        $mahasiswaId = auth()->user()->mahasiswa?->id;
        $sidangAktif = PengajuanSurat::where('mahasiswa_id', $mahasiswaId)
            ->where('jenis_surat', 'sidang_skripsi')
            ->whereNotIn('status', ['ditolak'])
            ->first();

        if ($sidangAktif) {
            $pesanStatus = match ($sidangAktif->status) {
                'diajukan' => 'Pengajuan sidang skripsi Anda sedang menunggu keputusan Kaprodi.',
                'disetujui' => 'Sidang skripsi Anda sudah disetujui Kaprodi. Jadwal sedang disiapkan.',
                'menunggu_ttd' => 'Surat undangan sidang sedang diproses (menunggu tanda tangan).',
                'sudah_ditandatangani' => 'Surat undangan sidang sudah tersedia. Silakan download dari riwayat.',
                'selesai' => 'Sidang skripsi Anda sudah selesai.',
                default => 'Sidang skripsi Anda sudah diproses.',
            };

            return view('mahasiswa.pengajuan.terkunci', [
                'pesan' => $pesanStatus,
                'linkLabel' => 'Lihat Riwayat Pengajuan',
                'linkUrl' => route('mahasiswa.riwayat.index'),
            ]);
        }

        return view('mahasiswa.pengajuan.sidang.create', [
            'pengajuanJudul' => $judulDisetujui,
        ]);
    }

    /** Detail satu pengajuan surat. */
    public function show(PengajuanSurat $pengajuanSurat): View
    {
        abort_unless(
            $pengajuanSurat->mahasiswa_id === auth()->user()->mahasiswa?->id,
            403
        );

        $pengajuanSurat->load([
            'pengajuanJudul.dosenPembimbing',
            'dosenPenguji',
            'statusHistories.changedBy',
        ]);

        return view('mahasiswa.pengajuan.surat.show', [
            'surat' => $pengajuanSurat,
        ]);
    }

    /** Download file surat (docx / pdf / scan). */
    public function download(PengajuanSurat $pengajuanSurat, string $tipe): StreamedResponse
    {
        abort_unless(
            $pengajuanSurat->mahasiswa_id === auth()->user()->mahasiswa?->id,
            403
        );

        $path = match ($tipe) {
            'docx' => $pengajuanSurat->file_docx,
            'pdf' => $pengajuanSurat->file_pdf,
            'scan' => $pengajuanSurat->file_scan,
            default => null,
        };

        abort_if(! $path, 404, 'Tipe file tidak valid.');

        // Cek status ditolak SEBELUM cek file exist (agar 403, bukan 404)
        if (in_array($tipe, ['docx', 'pdf']) && $pengajuanSurat->status === 'ditolak') {
            abort(403, 'Dokumen ini tidak dapat didownload karena pengajuan telah ditolak.');
        }

        abort_unless(Storage::disk('private')->exists($path), 404, 'File tidak tersedia.');

        // Format: {nim}_{nama_depan}_{jenis_surat}.ext — contoh: 1215_Herman_seminar_proposal.docx
        $pengajuanSurat->loadMissing('mahasiswa.user');
        $namaFile = PengajuanSurat::namaFileDownload($pengajuanSurat, $tipe);

        return Storage::disk('private')->download($path, $namaFile);
    }

    // ─── Private helpers ────────────────────────────────────────────────────

    /** Download berkas syarat yang diupload mahasiswa */
    public function downloadBerkas(BerkasPengajuan $berkas): StreamedResponse
    {
        // Pastikan berkas milik mahasiswa ini (via pengajuan)
        $pengajuan = $berkas->pengajuan;
        $mahasiswaId = auth()->user()->mahasiswa?->id;

        $milik = match (true) {
            $pengajuan instanceof PengajuanJudul => $pengajuan->mahasiswa_id === $mahasiswaId,
            $pengajuan instanceof PengajuanSurat => $pengajuan->mahasiswa_id === $mahasiswaId,
            default => false,
        };

        abort_unless($milik, 403);
        abort_unless(Storage::disk('private')->exists($berkas->path_file), 404, 'File tidak ditemukan.');

        return Storage::disk('private')->download($berkas->path_file, $berkas->nama_asli);
    }

    /** Ambil pengajuan judul yang disetujui milik mahasiswa yang login. */
    private function getJudulDisetujui(): ?PengajuanJudul
    {
        $mahasiswaId = auth()->user()->mahasiswa?->id;

        return PengajuanJudul::where('mahasiswa_id', $mahasiswaId)
            ->where('status', 'disetujui')
            ->with('dosenPembimbing', 'dosenPembimbing2')
            ->first();
    }

    /** Cek apakah seminar proposal mahasiswa sudah berstatus selesai/melewati kaprodi. */
    private function seminarSudahSelesai(): bool
    {
        $mahasiswaId = auth()->user()->mahasiswa?->id;

        // Seminar dianggap selesai jika sudah melewati tahap ACC Kaprodi:
        // disetujui → menunggu_ttd → sudah_ditandatangani → selesai
        return PengajuanSurat::where('mahasiswa_id', $mahasiswaId)
            ->where('jenis_surat', 'seminar_proposal')
            ->whereIn('status', ['disetujui', 'menunggu_ttd', 'sudah_ditandatangani', 'selesai'])
            ->exists();
    }
}
