<?php

namespace App\Http\Controllers\Mahasiswa;

use App\Http\Controllers\Controller;
use App\Models\BerkasPengajuan;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Models\StatusHistory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PengajuanSuratController extends Controller
{
    /** Tampilkan form Surat Aktif Kuliah. */
    public function createAktifKuliah(): View
    {
        return view('mahasiswa.pengajuan.aktif-kuliah.create');
    }

    /** POST — simpan pengajuan aktif kuliah */
    public function storeAktifKuliah(Request $request): RedirectResponse
    {
        $request->validate([
            'keperluan' => ['required', 'string', 'max:255'],
            'tujuanInstansi' => ['nullable', 'string', 'max:255'],
        ], ['keperluan.required' => 'Keperluan surat wajib dipilih.']);

        $mahasiswa = auth()->user()->mahasiswa;
        $keperluan = $request->keperluan === 'lainnya'
            ? trim($request->keperluanManual ?? '')
            : $request->keperluan;

        $pengajuan = PengajuanSurat::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => 'aktif_kuliah',
            'data_form' => ['keperluan' => $keperluan, 'tujuan_instansi' => $request->tujuanInstansi],
            'status' => 'diajukan',
        ]);

        StatusHistory::create([
            'model_type' => PengajuanSurat::class,
            'model_id' => $pengajuan->id,
            'status_lama' => null, 'status_baru' => 'diajukan',
            'catatan' => 'Pengajuan Surat Aktif Kuliah disubmit.',
            'changed_by' => auth()->id(), 'created_at' => now(),
        ]);

        return redirect()->route('mahasiswa.riwayat.index')
            ->with('success', 'Pengajuan Surat Aktif Kuliah berhasil dikirim.');
    }

    /** Tampilkan form Surat Izin Magang. */
    public function createIzinMagang(): View
    {
        return view('mahasiswa.pengajuan.izin-magang.create');
    }

    /** POST — simpan pengajuan izin magang */
    public function storeIzinMagang(Request $request): RedirectResponse
    {
        $request->validate([
            'namaInstansi' => ['required', 'string', 'max:255'],
            'alamatInstansi' => ['required', 'string', 'max:500'],
            'tanggalMulai' => ['required', 'date'],
            'tanggalSelesai' => ['required', 'date', 'after_or_equal:tanggalMulai'],
            'fileSuratInstansi' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $mahasiswa = auth()->user()->mahasiswa;

        $pengajuan = PengajuanSurat::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => 'izin_magang',
            'data_form' => [
                'nama_instansi' => $request->namaInstansi,
                'alamat_instansi' => $request->alamatInstansi,
                'tanggal_mulai' => $request->tanggalMulai,
                'tanggal_selesai' => $request->tanggalSelesai,
            ],
            'status' => 'diajukan',
        ]);

        if ($request->hasFile('fileSuratInstansi') && $request->file('fileSuratInstansi')->isValid()) {
            $file = $request->file('fileSuratInstansi');
            $path = $file->storeAs('berkas/'.$mahasiswa->nim.'/magang', Str::uuid().'.'.$file->extension(), 'private');
            BerkasPengajuan::create(['pengajuan_type' => PengajuanSurat::class, 'pengajuan_id' => $pengajuan->id, 'label' => 'Surat dari Instansi', 'path_file' => $path, 'nama_asli' => $file->getClientOriginalName()]);
        }

        StatusHistory::create(['model_type' => PengajuanSurat::class, 'model_id' => $pengajuan->id, 'status_lama' => null, 'status_baru' => 'diajukan', 'catatan' => 'Pengajuan Izin Magang disubmit.', 'changed_by' => auth()->id(), 'created_at' => now()]);

        return redirect()->route('mahasiswa.riwayat.index')->with('success', 'Pengajuan Izin Magang berhasil dikirim.');
    }

    /** Tampilkan form Surat Rekomendasi Magang. */
    public function createRekomendasiMagang(): View
    {
        return view('mahasiswa.pengajuan.rekomendasi-magang.create');
    }

    /** POST — simpan pengajuan rekomendasi magang */
    public function storeRekomendasiMagang(Request $request): RedirectResponse
    {
        $request->validate([
            'namaInstansi' => ['required', 'string', 'max:255'],
            'alamatInstansi' => ['required', 'string', 'max:500'],
            'fileSuratInstansi' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $mahasiswa = auth()->user()->mahasiswa;

        $pengajuan = PengajuanSurat::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => 'rekomendasi_magang',
            'data_form' => ['nama_instansi' => $request->namaInstansi, 'alamat_instansi' => $request->alamatInstansi],
            'status' => 'diajukan',
        ]);

        if ($request->hasFile('fileSuratInstansi') && $request->file('fileSuratInstansi')->isValid()) {
            $file = $request->file('fileSuratInstansi');
            $path = $file->storeAs('berkas/'.$mahasiswa->nim.'/rekomendasi_magang', Str::uuid().'.'.$file->extension(), 'private');
            BerkasPengajuan::create(['pengajuan_type' => PengajuanSurat::class, 'pengajuan_id' => $pengajuan->id, 'label' => 'Surat dari Instansi', 'path_file' => $path, 'nama_asli' => $file->getClientOriginalName()]);
        }

        StatusHistory::create(['model_type' => PengajuanSurat::class, 'model_id' => $pengajuan->id, 'status_lama' => null, 'status_baru' => 'diajukan', 'catatan' => 'Pengajuan Rekomendasi Magang disubmit.', 'changed_by' => auth()->id(), 'created_at' => now()]);

        return redirect()->route('mahasiswa.riwayat.index')->with('success', 'Pengajuan Rekomendasi Magang berhasil dikirim.');
    }

    /** Tampilkan form Surat Izin Penelitian. */
    public function createIzinPenelitian(): View
    {
        return view('mahasiswa.pengajuan.izin-penelitian.create');
    }

    /** POST — simpan pengajuan izin penelitian */
    public function storeIzinPenelitian(Request $request): RedirectResponse
    {
        $mahasiswa = auth()->user()->mahasiswa;

        $seminar = PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis_surat', 'seminar_proposal')
            ->whereIn('status', ['disetujui', 'menunggu_ttd', 'sudah_ditandatangani', 'selesai'])
            ->whereNotNull('file_absensi_seminar')
            ->latest()->first();

        if (! $seminar) {
            return redirect()->route('mahasiswa.riwayat.index')
                ->with('error', 'Seminar proposal harus selesai dan absensi sudah diupload admin.');
        }

        $izinAktif = PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis_surat', 'izin_penelitian')
            ->whereNotIn('status', ['ditolak'])->exists();

        if ($izinAktif) {
            return redirect()->route('mahasiswa.riwayat.index')->with('error', 'Sudah ada pengajuan izin penelitian aktif.');
        }

        $request->validate(['fileCoverProposal' => ['required', 'file', 'mimes:pdf', 'max:10240']]);

        $pengajuanJudul = PengajuanJudul::where('mahasiswa_id', $mahasiswa->id)->where('status', 'disetujui')->first();

        $pengajuan = PengajuanSurat::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => 'izin_penelitian',
            'pengajuan_judul_id' => $pengajuanJudul?->id,
            'data_form' => ['judul_penelitian' => $pengajuanJudul?->judul ?? '', 'bidang_penelitian' => $pengajuanJudul?->bidang_kajian ?? '', 'seminar_id' => $seminar->id],
            'status' => 'diajukan',
        ]);

        $file = $request->file('fileCoverProposal');
        $path = $file->storeAs('berkas/'.$mahasiswa->nim.'/izin_penelitian', Str::uuid().'.'.$file->extension(), 'private');
        BerkasPengajuan::create(['pengajuan_type' => PengajuanSurat::class, 'pengajuan_id' => $pengajuan->id, 'label' => 'Cover Proposal (TTD Pembimbing & Penguji)', 'path_file' => $path, 'nama_asli' => $file->getClientOriginalName()]);

        StatusHistory::create(['model_type' => PengajuanSurat::class, 'model_id' => $pengajuan->id, 'status_lama' => null, 'status_baru' => 'diajukan', 'catatan' => 'Pengajuan Izin Penelitian disubmit.', 'changed_by' => auth()->id(), 'created_at' => now()]);

        return redirect()->route('mahasiswa.riwayat.index')->with('success', 'Pengajuan Izin Penelitian berhasil dikirim.');
    }

    /** POST — simpan pengajuan sidang skripsi */
    public function storeSidang(Request $request): RedirectResponse
    {
        $mahasiswa = auth()->user()->mahasiswa;
        $judulDisetujui = $this->getJudulDisetujui();

        if (! $judulDisetujui) {
            return redirect()->route('mahasiswa.riwayat.index')->with('error', 'Judul skripsi harus disetujui terlebih dahulu.');
        }

        if (! $this->seminarSudahSelesai()) {
            return redirect()->route('mahasiswa.riwayat.index')->with('error', 'Seminar Proposal harus disetujui Kaprodi terlebih dahulu.');
        }

        $sidangAktif = PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis_surat', 'sidang_skripsi')->whereNotIn('status', ['ditolak'])->exists();

        if ($sidangAktif) {
            return redirect()->route('mahasiswa.riwayat.index')->with('error', 'Sudah ada pengajuan sidang aktif.');
        }

        $request->validate([
            'tanggalRencana' => ['nullable', 'date', 'after:today'],
            'fileBerkas.*' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ]);

        $pengajuan = PengajuanSurat::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => 'sidang_skripsi',
            'pengajuan_judul_id' => $judulDisetujui->id,
            'data_form' => ['tanggal_rencana' => $request->tanggalRencana ?: null],
            'status' => 'diajukan',
        ]);

        foreach ($request->file('fileBerkas', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $path = $file->storeAs('berkas/'.$mahasiswa->nim.'/sidang_skripsi', Str::uuid().'.'.$file->extension(), 'private');
            BerkasPengajuan::create(['pengajuan_type' => PengajuanSurat::class, 'pengajuan_id' => $pengajuan->id, 'label' => 'Berkas Syarat', 'path_file' => $path, 'nama_asli' => $file->getClientOriginalName()]);
        }

        StatusHistory::create(['model_type' => PengajuanSurat::class, 'model_id' => $pengajuan->id, 'status_lama' => null, 'status_baru' => 'diajukan', 'catatan' => 'Pengajuan Sidang Skripsi disubmit.', 'changed_by' => auth()->id(), 'created_at' => now()]);

        return redirect()->route('mahasiswa.riwayat.index')->with('success', 'Pengajuan Sidang Skripsi berhasil dikirim.');
    }

    /** POST — simpan pengajuan seminar proposal */
    public function storeSeminar(Request $request): RedirectResponse
    {
        $mahasiswa = auth()->user()->mahasiswa;

        // Guard: judul harus disetujui
        $judulDisetujui = $this->getJudulDisetujui();
        if (! $judulDisetujui) {
            return redirect()->route('mahasiswa.riwayat.index')
                ->with('error', 'Judul skripsi harus disetujui terlebih dahulu.');
        }

        // Guard: tidak boleh ada seminar aktif
        $seminarAktif = PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis_surat', 'seminar_proposal')
            ->whereNotIn('status', ['ditolak'])
            ->exists();

        if ($seminarAktif) {
            return redirect()->route('mahasiswa.riwayat.index')
                ->with('error', 'Anda sudah memiliki pengajuan seminar proposal yang aktif.');
        }

        $request->validate([
            'fileBerkas.*' => ['nullable', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ], [
            'fileBerkas.*.mimes' => 'File harus berformat PDF, DOC, atau DOCX.',
            'fileBerkas.*.max' => 'Ukuran file maksimal 10 MB.',
        ]);

        $pengajuan = PengajuanSurat::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => 'seminar_proposal',
            'pengajuan_judul_id' => $judulDisetujui->id,
            'data_form' => [],
            'status' => 'diajukan',
        ]);

        foreach ($request->file('fileBerkas', []) as $file) {
            if (! $file || ! $file->isValid()) {
                continue;
            }
            $path = $file->storeAs(
                'berkas/'.$mahasiswa->nim.'/seminar_proposal',
                Str::uuid().'.'.$file->extension(),
                'private'
            );
            BerkasPengajuan::create([
                'pengajuan_type' => PengajuanSurat::class,
                'pengajuan_id' => $pengajuan->id,
                'label' => 'Berkas Syarat',
                'path_file' => $path,
                'nama_asli' => $file->getClientOriginalName(),
            ]);
        }

        StatusHistory::create([
            'model_type' => PengajuanSurat::class,
            'model_id' => $pengajuan->id,
            'status_lama' => null,
            'status_baru' => 'diajukan',
            'catatan' => 'Pengajuan Seminar Proposal disubmit oleh mahasiswa.',
            'changed_by' => auth()->id(),
            'created_at' => now(),
        ]);

        return redirect()->route('mahasiswa.riwayat.index')
            ->with('success', 'Pengajuan Seminar Proposal berhasil dikirim. Kaprodi akan meninjau berkas.');
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
                'disetujui' => 'Seminar proposal Anda sudah disetujui Kaprodi. Admin sedang menyiapkan jadwal.',
                'menunggu_ttd' => 'Surat undangan seminar sedang diproses (menunggu tanda tangan).',
                'sudah_ditandatangani' => 'Surat undangan seminar sudah tersedia. Silakan download dari riwayat.',
                'selesai' => 'Seminar proposal Anda sudah selesai. Silakan lanjutkan ke sidang skripsi.',
                default => 'Seminar proposal Anda sudah diproses.',
            };

            $sudahSelesai = in_array($seminarAktif->status, ['disetujui', 'menunggu_ttd', 'sudah_ditandatangani', 'selesai']);

            return view('mahasiswa.pengajuan.terkunci', [
                'pesan' => $pesanStatus,
                'linkLabel' => 'Lihat Riwayat Pengajuan',
                'linkUrl' => route('mahasiswa.riwayat.index'),
                'sudahSelesai' => $sudahSelesai,
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
                'diajukan' => 'Pengajuan sidang skripsi Anda sedang menunggu verifikasi Admin.',
                'disetujui' => 'Sidang skripsi Anda sudah disetujui Kaprodi. Admin sedang menyiapkan jadwal.',
                'menunggu_ttd' => 'Surat undangan sidang sedang diproses (menunggu tanda tangan).',
                'sudah_ditandatangani' => 'Surat undangan sidang sudah tersedia. Silakan download dari riwayat.',
                'selesai' => 'Sidang skripsi Anda sudah selesai.',
                default => 'Sidang skripsi Anda sudah diproses.',
            };

            $sudahSelesai = in_array($sidangAktif->status, ['disetujui', 'menunggu_ttd', 'sudah_ditandatangani', 'selesai']);

            return view('mahasiswa.pengajuan.terkunci', [
                'pesan' => $pesanStatus,
                'linkLabel' => 'Lihat Riwayat Pengajuan',
                'linkUrl' => route('mahasiswa.riwayat.index'),
                'sudahSelesai' => $sudahSelesai,
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

    /** Download absensi seminar proposal milik mahasiswa (syarat izin penelitian). */
    public function downloadAbsensi(PengajuanSurat $pengajuanSurat): StreamedResponse
    {
        // Pastikan hanya pemilik yang bisa download
        abort_unless(
            $pengajuanSurat->mahasiswa_id === auth()->user()->mahasiswa?->id,
            403
        );
        abort_unless($pengajuanSurat->jenis_surat === 'seminar_proposal', 404);
        abort_unless($pengajuanSurat->file_absensi_seminar, 404, 'Absensi belum tersedia.');
        abort_unless(Storage::disk('private')->exists($pengajuanSurat->file_absensi_seminar), 404, 'File tidak ditemukan.');

        $ext = pathinfo($pengajuanSurat->file_absensi_seminar, PATHINFO_EXTENSION);
        $nama = 'absensi_seminar_'.$pengajuanSurat->mahasiswa->nim.'.'.$ext;

        return Storage::disk('private')->download($pengajuanSurat->file_absensi_seminar, $nama);
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
