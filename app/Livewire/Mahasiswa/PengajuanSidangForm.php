<?php

namespace App\Livewire\Mahasiswa;

use App\Models\BerkasPengajuan;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Models\StatusHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class PengajuanSidangForm extends Component
{
    use WithFileUploads;

    public PengajuanJudul $pengajuanJudul;

    /** Usulan tanggal dari mahasiswa — opsional, Kaprodi yang menetapkan jadwal resmi */
    #[Validate('nullable|date|after:today')]
    public string $tanggalRencana = '';

    /** Multiple berkas syarat */
    #[Validate('nullable|array|max:10')]
    public array $fileBerkas = [];

    public function mount(PengajuanJudul $pengajuanJudul): void
    {
        abort_unless(
            $pengajuanJudul->mahasiswa_id === auth()->user()->mahasiswa?->id
            && $pengajuanJudul->status === 'disetujui',
            403
        );

        $mahasiswaId = auth()->user()->mahasiswa?->id;

        // Seminar proposal dianggap selesai jika statusnya sudah melewati 'disetujui'
        // (menunggu_ttd = DOCX sudah dibuat, sudah_ditandatangani = scan sudah diupload, selesai = final)
        $seminarSelesai = PengajuanSurat::where('mahasiswa_id', $mahasiswaId)
            ->where('jenis_surat', 'seminar_proposal')
            ->whereIn('status', ['disetujui', 'menunggu_ttd', 'sudah_ditandatangani', 'selesai'])
            ->exists();

        abort_unless($seminarSelesai, 403, 'Seminar Proposal harus disetujui Kaprodi terlebih dahulu.');

        // Guard: blokir jika sidang sudah aktif (bukan ditolak)
        $sidangAktif = PengajuanSurat::where('mahasiswa_id', $mahasiswaId)
            ->where('jenis_surat', 'sidang_skripsi')
            ->whereNotIn('status', ['ditolak'])
            ->exists();

        abort_if($sidangAktif, 403, 'Anda sudah memiliki pengajuan sidang skripsi yang aktif.');

        $this->pengajuanJudul = $pengajuanJudul;
    }

    public function submit(): void
    {
        $this->validate();

        $mahasiswa = Auth::user()->mahasiswa;

        // Guard: sidang aktif belum ada
        $aktif = PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis_surat', 'sidang_skripsi')
            ->whereNotIn('status', ['ditolak'])
            ->exists();

        if ($aktif) {
            $this->addError('tanggalRencana', 'Anda sudah memiliki pengajuan sidang skripsi yang masih aktif.');

            return;
        }

        $pengajuan = PengajuanSurat::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => 'sidang_skripsi',
            'pengajuan_judul_id' => $this->pengajuanJudul->id,
            'data_form' => [
                // Hanya simpan usulan tanggal — waktu & tempat ditetapkan Kaprodi
                'tanggal_rencana' => $this->tanggalRencana ?: null,
            ],
            'status' => 'diajukan',
        ]);

        // Upload multiple berkas syarat
        foreach ($this->fileBerkas as $file) {
            if (! $file) {
                continue;
            }

            try {
                $namaAsli = $file->getClientOriginalName();
                $path = $file->storeAs(
                    'berkas/'.$mahasiswa->id.'/sidang/'.$pengajuan->id,
                    Str::uuid().'.'.$file->extension(),
                    'private'
                );

                BerkasPengajuan::create([
                    'pengajuan_type' => PengajuanSurat::class,
                    'pengajuan_id' => $pengajuan->id,
                    'label' => 'Berkas Syarat',
                    'path_file' => $path,
                    'nama_asli' => $namaAsli,
                ]);
            } catch (\Throwable) {
                // skip file error
            }
        }

        StatusHistory::create([
            'model_type' => PengajuanSurat::class,
            'model_id' => $pengajuan->id,
            'status_lama' => null,
            'status_baru' => 'diajukan',
            'catatan' => 'Pengajuan Sidang Skripsi disubmit oleh mahasiswa.',
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);

        session()->flash('success', 'Pengajuan Sidang Skripsi berhasil dikirim. Kaprodi akan meninjau berkas dan menetapkan jadwal.');

        $this->redirectRoute('mahasiswa.riwayat.index');
    }

    public function render(): View
    {
        return view('livewire.mahasiswa.pengajuan-sidang-form');
    }
}
