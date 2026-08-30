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

    /**
     * Cover proposal yang sudah direvisi dan ditandatangani pembimbing + penguji.
     * Wajib diupload sebagai syarat pengajuan izin penelitian.
     */
    #[Validate('required|file|mimes:pdf|max:10240')]
    public $fileCoverProposal = null;

    protected $messages = [
        'fileCoverProposal.required' => 'Cover proposal yang sudah ditandatangani wajib diupload.',
        'fileCoverProposal.mimes' => 'File cover proposal harus berformat PDF.',
        'fileCoverProposal.max' => 'Ukuran file maksimal 10 MB.',
    ];

    /**
     * Seminar proposal yang sudah selesai + absensi sudah diupload admin.
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
     * Bisa ajukan jika seminar selesai DAN absensi sudah diupload admin.
     */
    #[Computed]
    public function bisaAjukan(): bool
    {
        return $this->seminarSelesai !== null
            && $this->seminarSelesai->file_absensi_seminar !== null;
    }

    /**
     * Sudah punya izin penelitian aktif.
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
        $pengajuanJudul = PengajuanJudul::where('mahasiswa_id', $mahasiswa->id)
            ->where('status', 'disetujui')
            ->with('dosenPembimbing')
            ->first();

        $pengajuan = PengajuanSurat::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => 'izin_penelitian',
            'pengajuan_judul_id' => $pengajuanJudul?->id,
            'data_form' => [
                'judul_penelitian' => $pengajuanJudul?->judul ?? '',
                'bidang_penelitian' => $pengajuanJudul?->bidang_kajian ?? '',
                'seminar_id' => $seminar?->id,
            ],
            'status' => 'diajukan',
        ]);

        $ext = $this->fileCoverProposal->getClientOriginalExtension();
        $namaAsli = $this->fileCoverProposal->getClientOriginalName();
        $path = 'berkas/'.$mahasiswa->nim.'/izin_penelitian/'.Str::uuid().'.'.$ext;

        Storage::disk('private')->put($path, file_get_contents($this->fileCoverProposal->getRealPath()));

        BerkasPengajuan::create([
            'pengajuan_type' => PengajuanSurat::class,
            'pengajuan_id' => $pengajuan->id,
            'label' => 'Cover Proposal (TTD Pembimbing & Penguji)',
            'path_file' => $path,
            'nama_asli' => $namaAsli,
        ]);

        StatusHistory::create([
            'model_type' => PengajuanSurat::class,
            'model_id' => $pengajuan->id,
            'status_lama' => null,
            'status_baru' => 'diajukan',
            'catatan' => 'Pengajuan Izin Penelitian disubmit oleh mahasiswa.',
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
}
