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

class PengajuanSeminarForm extends Component
{
    use WithFileUploads;

    public PengajuanJudul $pengajuanJudul;

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

        // Guard: tolak jika seminar sudah aktif (bukan ditolak)
        $mahasiswaId = auth()->user()->mahasiswa?->id;
        $seminarAktif = PengajuanSurat::where('mahasiswa_id', $mahasiswaId)
            ->where('jenis_surat', 'seminar_proposal')
            ->whereNotIn('status', ['ditolak'])
            ->exists();

        abort_if($seminarAktif, 403, 'Anda sudah memiliki pengajuan seminar proposal yang aktif.');

        $this->pengajuanJudul = $pengajuanJudul;
    }

    public function submit(): void
    {
        $this->validate();

        $mahasiswa = Auth::user()->mahasiswa;

        // Guard: seminar aktif belum ada
        $aktif = PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
            ->where('jenis_surat', 'seminar_proposal')
            ->whereNotIn('status', ['ditolak'])
            ->exists();

        if ($aktif) {
            $this->addError('fileBerkas', 'Anda sudah memiliki pengajuan seminar proposal yang masih aktif.');

            return;
        }

        $pengajuan = PengajuanSurat::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => 'seminar_proposal',
            'pengajuan_judul_id' => $this->pengajuanJudul->id,
            'data_form' => [],
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
                    'berkas/'.$mahasiswa->id.'/seminar/'.$pengajuan->id,
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
            'catatan' => 'Pengajuan Seminar Proposal disubmit oleh mahasiswa.',
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);

        session()->flash('success', 'Pengajuan Seminar Proposal berhasil dikirim. Kaprodi akan meninjau berkas dan menetapkan jadwal seminar.');

        $this->redirectRoute('mahasiswa.riwayat.index');
    }

    public function render(): View
    {
        return view('livewire.mahasiswa.pengajuan-seminar-form');
    }
}
