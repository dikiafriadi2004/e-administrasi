<?php

namespace App\Livewire\Mahasiswa;

use App\Models\BerkasPengajuan;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Models\StatusHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Computed;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class PengajuanIzinPenelitianForm extends Component
{
    use WithFileUploads;

    // ── Auto-fill dari pengajuan judul ───────────────────────────────────────
    public string $judulPenelitian = '';

    public string $bidangPenelitian = '';

    public string $namaPembimbing = '';

    // ── Diisi mahasiswa ──────────────────────────────────────────────────────
    #[Validate('required|string|max:255')]
    public string $namaInstansi = '';

    #[Validate('required|string|max:500')]
    public string $alamatInstansi = '';

    #[Validate('required|date')]
    public string $tanggalMulai = '';

    #[Validate('required|date|after_or_equal:tanggalMulai')]
    public string $tanggalSelesai = '';

    /**
     * Cover proposal yang sudah direvisi dan ditandatangani pembimbing.
     * Wajib diupload sebagai syarat pengajuan izin penelitian.
     */
    #[Validate('required|file|mimes:pdf|max:10240')]
    public $fileCoverProposal = null;

    protected $messages = [
        'namaInstansi.required' => 'Nama instansi / lokasi penelitian wajib diisi.',
        'alamatInstansi.required' => 'Alamat instansi wajib diisi.',
        'tanggalMulai.required' => 'Tanggal mulai penelitian wajib diisi.',
        'tanggalSelesai.required' => 'Tanggal selesai penelitian wajib diisi.',
        'tanggalSelesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        'fileCoverProposal.required' => 'Cover proposal yang sudah ditandatangani pembimbing wajib diupload.',
        'fileCoverProposal.mimes' => 'File cover proposal harus berformat PDF.',
        'fileCoverProposal.max' => 'Ukuran file maksimal 10 MB.',
    ];

    public function mount(): void
    {
        // Auto-fill dari judul yang disetujui
        $judul = $this->getPengajuanJudulDisetujui();
        if ($judul) {
            $this->judulPenelitian = $judul->judul;
            $this->bidangPenelitian = $judul->bidang_kajian ?? '';
            $this->namaPembimbing = $judul->dosenPembimbing?->nama ?? '';
        }
    }

    /**
     * Seminar proposal yang sudah selesai milik mahasiswa ini.
     * Berisi file_absensi_seminar jika admin sudah upload.
     */
    #[Computed]
    public function seminarSelesai(): ?PengajuanSurat
    {
        $mahasiswaId = Auth::user()->mahasiswa?->id;

        return PengajuanSurat::where('mahasiswa_id', $mahasiswaId)
            ->where('jenis_surat', 'seminar_proposal')
            ->whereIn('status', ['disetujui', 'menunggu_ttd', 'sudah_ditandatangani', 'selesai'])
            ->latest()
            ->first();
    }

    /**
     * Apakah mahasiswa bisa mengajukan izin penelitian.
     * Syarat: seminar selesai + absensi sudah diupload admin.
     */
    #[Computed]
    public function bisaAjukan(): bool
    {
        return $this->seminarSelesai !== null
            && $this->seminarSelesai->file_absensi_seminar !== null;
    }

    /**
     * Apakah mahasiswa sudah punya izin penelitian aktif (belum ditolak).
     */
    #[Computed]
    public function izinAktif(): ?PengajuanSurat
    {
        $mahasiswaId = Auth::user()->mahasiswa?->id;

        return PengajuanSurat::where('mahasiswa_id', $mahasiswaId)
            ->where('jenis_surat', 'izin_penelitian')
            ->whereNotIn('status', ['ditolak'])
            ->latest()
            ->first();
    }

    public function submit(): void
    {
        // Guard — cek ulang di server side
        if (! $this->bisaAjukan) {
            $this->addError('form', 'Pengajuan tidak dapat dilakukan. Pastikan seminar proposal sudah selesai dan absensi sudah diupload admin.');

            return;
        }

        if ($this->izinAktif) {
            $this->addError('form', 'Anda sudah memiliki pengajuan izin penelitian yang aktif.');

            return;
        }

        $this->validate();

        $mahasiswa = Auth::user()->mahasiswa;
        $seminar = $this->seminarSelesai;
        $pengajuanJudul = $this->getPengajuanJudulDisetujui();

        $pengajuan = PengajuanSurat::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => 'izin_penelitian',
            'pengajuan_judul_id' => $pengajuanJudul?->id,
            'data_form' => [
                'judul_penelitian' => $this->judulPenelitian,
                'bidang_penelitian' => $this->bidangPenelitian,
                'nama_instansi' => $this->namaInstansi,
                'alamat_instansi' => $this->alamatInstansi,
                'tanggal_mulai' => $this->tanggalMulai,
                'tanggal_selesai' => $this->tanggalSelesai,
                // Simpan referensi seminar untuk traceability
                'seminar_id' => $seminar?->id,
            ],
            'status' => 'diajukan',
        ]);

        // Upload cover proposal revisi (wajib)
        $ext = $this->fileCoverProposal->getClientOriginalExtension();
        $namaAsli = $this->fileCoverProposal->getClientOriginalName();
        $path = "berkas/{$mahasiswa->id}/penelitian/{$pengajuan->id}/".Str::uuid().".{$ext}";

        Storage::disk('private')->put($path, file_get_contents($this->fileCoverProposal->getRealPath()));

        BerkasPengajuan::create([
            'pengajuan_type' => PengajuanSurat::class,
            'pengajuan_id' => $pengajuan->id,
            'label' => 'Cover Proposal Revisi (TTD Pembimbing)',
            'path_file' => $path,
            'nama_asli' => $namaAsli,
        ]);

        StatusHistory::create([
            'model_type' => PengajuanSurat::class,
            'model_id' => $pengajuan->id,
            'status_lama' => null,
            'status_baru' => 'diajukan',
            'catatan' => 'Pengajuan Surat Izin Penelitian disubmit oleh mahasiswa.',
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);

        session()->flash('success', 'Pengajuan Izin Penelitian berhasil dikirim. Admin akan segera memproses surat Anda.');

        $this->redirectRoute('mahasiswa.riwayat.index');
    }

    public function render(): View
    {
        return view('livewire.mahasiswa.pengajuan-izin-penelitian-form');
    }

    private function getPengajuanJudulDisetujui(): ?PengajuanJudul
    {
        $mahasiswaId = Auth::user()->mahasiswa?->id;

        return PengajuanJudul::where('mahasiswa_id', $mahasiswaId)
            ->where('status', 'disetujui')
            ->with('dosenPembimbing')
            ->first();
    }
}
