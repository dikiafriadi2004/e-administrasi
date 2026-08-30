<?php

namespace App\Livewire\Mahasiswa;

use App\Models\BerkasPengajuan;
use App\Models\PengajuanJudul;
use App\Models\StatusHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class EditJudulForm extends Component
{
    use WithFileUploads;

    public PengajuanJudul $pengajuan;

    #[Validate('required|string|min:10|max:500')]
    public string $judul = '';

    #[Validate('required|string|min:3|max:100')]
    public string $bidangKajian = '';

    #[Validate('required|string|min:50|max:2000')]
    public string $ringkasan = '';

    /** File berkas baru (opsional) */
    #[Validate('nullable|array|max:5')]
    #[Validate('nullable|file|mimes:pdf,doc,docx|max:10240', message: 'Setiap file harus PDF/DOC/DOCX, maks 10 MB.')]
    public array $fileBerkas = [];

    public function mount(PengajuanJudul $pengajuan): void
    {
        abort_unless(
            $pengajuan->mahasiswa_id === Auth::user()->mahasiswa?->id
            && in_array($pengajuan->status, ['diajukan', 'ditolak', 'disetujui']),
            403
        );

        $this->pengajuan = $pengajuan;
        $this->judul = $pengajuan->judul;
        $this->bidangKajian = $pengajuan->bidang_kajian;
        $this->ringkasan = $pengajuan->ringkasan;
    }

    public function submit(): void
    {
        $this->validate();

        $mahasiswa = Auth::user()->mahasiswa;

        // Simpan status sebelum diubah untuk StatusHistory yang akurat
        $statusSebelumnya = $this->pengajuan->status;

        // Update data judul
        $this->pengajuan->update([
            'judul' => $this->judul,
            'bidang_kajian' => $this->bidangKajian,
            'ringkasan' => $this->ringkasan,
            'status' => 'diajukan',
        ]);

        // Upload berkas baru jika ada
        foreach ($this->fileBerkas as $file) {
            $namaAsli = $file->getClientOriginalName();
            $path = $file->storeAs(
                'berkas/'.$mahasiswa->nim.'/judul',
                Str::uuid().'.'.$file->extension(),
                'private'
            );

            BerkasPengajuan::create([
                'pengajuan_type' => PengajuanJudul::class,
                'pengajuan_id' => $this->pengajuan->id,
                'label' => 'Dokumen Pendukung',
                'path_file' => $path,
                'nama_asli' => $namaAsli,
            ]);
        }

        StatusHistory::create([
            'model_type' => PengajuanJudul::class,
            'model_id' => $this->pengajuan->id,
            'status_lama' => $statusSebelumnya,
            'status_baru' => 'diajukan',
            'catatan' => $statusSebelumnya === 'disetujui'
                ? 'Mahasiswa mengajukan revisi judul (revisi mandiri setelah seminar).'
                : 'Mahasiswa mengajukan revisi judul.',
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);

        $pesan = $statusSebelumnya === 'disetujui'
            ? 'Revisi judul berhasil diajukan. Kaprodi akan meninjau kembali dan menetapkan ulang persetujuan.'
            : 'Judul berhasil direvisi dan diajukan kembali.';

        session()->flash('success', $pesan);

        $this->redirectRoute('mahasiswa.riwayat.index');
    }

    /** Hapus berkas yang sudah tersimpan */
    public function hapusBerkas(int $berkasId): void
    {
        $berkas = BerkasPengajuan::where('id', $berkasId)
            ->where('pengajuan_type', PengajuanJudul::class)
            ->where('pengajuan_id', $this->pengajuan->id)
            ->firstOrFail();

        Storage::disk('private')->delete($berkas->path_file);
        $berkas->delete();

        $this->pengajuan->refresh();
        $this->pengajuan->load('berkas');
    }

    public function render(): View
    {
        return view('livewire.mahasiswa.edit-judul-form');
    }
}
