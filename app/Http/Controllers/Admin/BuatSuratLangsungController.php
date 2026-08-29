<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\SuratGenerationException;
use App\Http\Controllers\Controller;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PengajuanSurat;
use App\Models\StatusHistory;
use App\Services\NomorSuratService;
use App\Services\SuratGeneratorService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BuatSuratLangsungController extends Controller
{
    private const JENIS_TERSEDIA = [
        'aktif_kuliah' => 'Surat Aktif Kuliah',
        'seminar_proposal' => 'Seminar Proposal',
        'sidang_skripsi' => 'Sidang Skripsi',
        'undangan_penguji' => 'Undangan Penguji',
        'izin_magang' => 'Surat Izin Magang / PKL',
        'rekomendasi_magang' => 'Surat Rekomendasi Magang',
        'izin_penelitian' => 'Surat Izin Penelitian',
    ];

    public function __construct(
        private readonly SuratGeneratorService $generator,
        private readonly NomorSuratService $nomorService
    ) {}

    public function create(): View
    {
        $mahasiswas = Mahasiswa::with('user')->orderBy('created_at')->get();
        $dosens = Dosen::orderBy('nama')->get();
        $nomorSuffix = $this->nomorService->getSuffix();

        return view('admin.buat-surat.create', [
            'mahasiswas' => $mahasiswas,
            'dosens' => $dosens,
            'jenisTersedia' => self::JENIS_TERSEDIA,
            'nomorSuffix' => $nomorSuffix,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $jenisSeminarSidang = 'seminar_proposal,sidang_skripsi,undangan_penguji';

        $request->validate([
            'mahasiswa_id' => ['required', 'exists:mahasiswas,id'],
            'jenis_surat' => ['required', 'in:'.implode(',', array_keys(self::JENIS_TERSEDIA))],
            'nomor_urutan' => ['required', 'string', 'max:20'],
            // Aktif Kuliah
            'keperluan' => ['required_if:jenis_surat,aktif_kuliah', 'nullable', 'string', 'max:255'],
            'tujuan_instansi' => ['nullable', 'string', 'max:255'],
            // Seminar / Sidang / Undangan
            'tanggal_rencana' => ['required_if:jenis_surat,'.$jenisSeminarSidang, 'nullable', 'date'],
            'waktu_rencana' => ['nullable', 'string', 'max:50'],
            'tempat' => ['nullable', 'string', 'max:255'],
            'judul_skripsi' => ['required_if:jenis_surat,'.$jenisSeminarSidang, 'nullable', 'string', 'max:500'],
            // Dosen — pilih dari dropdown (ID), 1 pembimbing, 2 penguji
            'dosen_pembimbing_id' => ['required_if:jenis_surat,'.$jenisSeminarSidang, 'nullable', 'exists:dosens,id'],
            'dosen_penguji_1_id' => ['required_if:jenis_surat,'.$jenisSeminarSidang, 'nullable', 'exists:dosens,id'],
            'dosen_penguji_2_id' => ['nullable', 'exists:dosens,id'],
            // Magang & Penelitian
            'nama_instansi' => ['required_if:jenis_surat,izin_magang,rekomendasi_magang,izin_penelitian', 'nullable', 'string', 'max:255'],
            'alamat_instansi' => ['required_if:jenis_surat,izin_magang,rekomendasi_magang,izin_penelitian', 'nullable', 'string', 'max:500'],
            'tanggal_mulai' => ['required_if:jenis_surat,izin_magang,izin_penelitian', 'nullable', 'date'],
            'tanggal_selesai' => ['required_if:jenis_surat,izin_magang,izin_penelitian', 'nullable', 'date', 'after_or_equal:tanggal_mulai'],
            'judul_penelitian' => ['required_if:jenis_surat,izin_penelitian', 'nullable', 'string', 'max:500'],
            'bidang_penelitian' => ['required_if:jenis_surat,izin_penelitian', 'nullable', 'string', 'max:255'],
        ], [
            'nomor_urutan.required' => 'Nomor urutan surat wajib diisi.',
            'keperluan.required_if' => 'Keperluan surat wajib diisi.',
            'tanggal_rencana.required_if' => 'Tanggal pelaksanaan wajib diisi.',
            'judul_skripsi.required_if' => 'Judul skripsi wajib diisi.',
            'dosen_pembimbing_id.required_if' => 'Dosen pembimbing wajib dipilih.',
            'dosen_penguji_1_id.required_if' => 'Dosen penguji I wajib dipilih.',
            'nama_instansi.required_if' => 'Nama instansi wajib diisi.',
            'alamat_instansi.required_if' => 'Alamat instansi wajib diisi.',
            'tanggal_mulai.required_if' => 'Tanggal mulai wajib diisi.',
            'tanggal_selesai.required_if' => 'Tanggal selesai wajib diisi.',
            'tanggal_selesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
            'judul_penelitian.required_if' => 'Judul penelitian wajib diisi.',
            'bidang_penelitian.required_if' => 'Bidang penelitian wajib diisi.',
        ]);

        // Resolve nama & NIP dosen dari ID
        $pembimbing = $request->dosen_pembimbing_id ? Dosen::find($request->dosen_pembimbing_id) : null;
        $penguji1 = $request->dosen_penguji_1_id ? Dosen::find($request->dosen_penguji_1_id) : null;
        $penguji2 = $request->dosen_penguji_2_id ? Dosen::find($request->dosen_penguji_2_id) : null;

        $dataForm = match ($request->jenis_surat) {
            'aktif_kuliah' => [
                'keperluan' => $request->keperluan === 'lainnya'
                    ? trim($request->keperluan_manual ?? '')
                    : $request->keperluan,
                'tujuan_instansi' => $request->tujuan_instansi,
            ],
            'seminar_proposal', 'sidang_skripsi', 'undangan_penguji' => [
                'tanggal_rencana' => $request->tanggal_rencana,
                'waktu_rencana' => $request->waktu_rencana,
                'tempat' => $request->tempat,
                'judul_skripsi' => $request->judul_skripsi,
                'nama_pembimbing' => $pembimbing?->nama ?? '',
                'nama_pembimbing_1' => $pembimbing?->nama ?? '',
                'nip_pembimbing' => $pembimbing?->nip ?? '',
                'nip_pembimbing_1' => $pembimbing?->nip ?? '',
                'nama_penguji_1' => $penguji1?->nama ?? '',
                'nip_penguji_1' => $penguji1?->nip ?? '',
                'nama_penguji_2' => $penguji2?->nama ?? '',
                'nip_penguji_2' => $penguji2?->nip ?? '',
            ],
            'izin_magang' => [
                'nama_instansi' => $request->nama_instansi,
                'alamat_instansi' => $request->alamat_instansi,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
            ],
            'rekomendasi_magang' => [
                'nama_instansi' => $request->nama_instansi,
                'alamat_instansi' => $request->alamat_instansi,
            ],
            'izin_penelitian' => [
                'nama_instansi' => $request->nama_instansi,
                'alamat_instansi' => $request->alamat_instansi,
                'tanggal_mulai' => $request->tanggal_mulai,
                'tanggal_selesai' => $request->tanggal_selesai,
                'judul_penelitian' => $request->judul_penelitian,
                'bidang_penelitian' => $request->bidang_penelitian,
            ],
            default => [],
        };

        $surat = PengajuanSurat::create([
            'mahasiswa_id' => $request->mahasiswa_id,
            'jenis_surat' => $request->jenis_surat,
            'nomor_surat' => trim($request->nomor_urutan),
            'data_form' => $dataForm,
            'status' => 'menunggu_ttd',
        ]);

        StatusHistory::create([
            'model_type' => PengajuanSurat::class,
            'model_id' => $surat->id,
            'status_lama' => null,
            'status_baru' => 'menunggu_ttd',
            'catatan' => 'Surat dibuat langsung oleh admin.',
            'changed_by' => auth()->id(),
            'created_at' => now(),
        ]);

        try {
            $this->generator->generate($surat);
        } catch (SuratGenerationException $e) {
            return redirect()->route('admin.surat.show', $surat)
                ->with('warning', 'Surat dibuat, tapi generate dokumen gagal: '.$e->getMessage());
        }

        return redirect()->route('admin.surat.show', $surat)
            ->with('success', 'Surat berhasil dibuat dan digenerate. Silakan cetak dan upload scan.');
    }
}
