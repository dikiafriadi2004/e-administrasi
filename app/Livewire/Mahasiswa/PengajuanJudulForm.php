<?php

namespace App\Livewire\Mahasiswa;

use App\Models\BerkasPengajuan;
use App\Models\PengajuanJudul;
use App\Models\StatusHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class PengajuanJudulForm extends Component
{
    use WithFileUploads;

    #[Validate('required|string|min:10|max:500')]
    public string $judul = '';

    #[Validate('required|string|max:255')]
    public string $bidangKajian = '';

    #[Validate('required|string|min:50')]
    public string $ringkasan = '';

    /** Multiple berkas syarat (opsional) */
    public array $fileBerkas = [];

    protected function rules(): array
    {
        return [
            'judul' => 'required|string|min:10|max:500',
            'bidangKajian' => 'required|string|max:255',
            'ringkasan' => 'required|string|min:50',
            'fileBerkas.*' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ];
    }

    protected function messages(): array
    {
        return [
            'judul.required' => 'Judul skripsi wajib diisi.',
            'judul.min' => 'Judul skripsi minimal 10 karakter.',
            'judul.max' => 'Judul skripsi maksimal 500 karakter.',
            'bidangKajian.required' => 'Bidang kajian wajib diisi.',
            'ringkasan.required' => 'Ringkasan wajib diisi.',
            'ringkasan.min' => 'Ringkasan minimal 50 karakter.',
            'fileBerkas.*.mimes' => 'File harus berformat PDF, DOC, atau DOCX.',
            'fileBerkas.*.max' => 'Ukuran file maksimal 10 MB.',
        ];
    }

    /**
     * Validasi per-field saat blur — hanya tampilkan error setelah user keluar dari field.
     */
    public function updated(string $field): void
    {
        $this->validateOnly($field);
    }

    public function submit(): void
    {
        $this->validate();

        $mahasiswa = Auth::user()->mahasiswa;

        if (! $mahasiswa) {
            $this->addError('judul', 'Data mahasiswa tidak ditemukan. Hubungi admin.');

            return;
        }

        // Guard: tidak boleh ada pengajuan aktif
        $aktif = PengajuanJudul::where('mahasiswa_id', $mahasiswa->id)
            ->whereNotIn('status', ['ditolak'])
            ->exists();

        if ($aktif) {
            $this->addError('judul', 'Anda sudah memiliki pengajuan judul yang masih aktif. Cek riwayat pengajuan.');

            return;
        }

        $pengajuan = PengajuanJudul::create([
            'mahasiswa_id' => $mahasiswa->id,
            'judul' => $this->judul,
            'bidang_kajian' => $this->bidangKajian,
            'ringkasan' => $this->ringkasan,
            'status' => 'diajukan',
        ]);

        // Upload berkas pendukung jika ada
        foreach ($this->fileBerkas as $file) {
            if (! $file) {
                continue;
            }

            try {
                $namaAsli = $file->getClientOriginalName();
                $path = $file->storeAs(
                    'berkas/'.$mahasiswa->id.'/judul/'.$pengajuan->id,
                    Str::uuid().'.'.$file->extension(),
                    'private'
                );

                BerkasPengajuan::create([
                    'pengajuan_type' => PengajuanJudul::class,
                    'pengajuan_id' => $pengajuan->id,
                    'label' => 'Dokumen Pendukung',
                    'path_file' => $path,
                    'nama_asli' => $namaAsli,
                ]);
            } catch (\Throwable) {
                // skip file error, pengajuan tetap tersimpan
            }
        }

        StatusHistory::create([
            'model_type' => PengajuanJudul::class,
            'model_id' => $pengajuan->id,
            'status_lama' => null,
            'status_baru' => 'diajukan',
            'catatan' => 'Pengajuan judul baru disubmit oleh mahasiswa.',
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);

        session()->flash('success', 'Pengajuan judul berhasil dikirim! Kaprodi akan segera meninjau dan menetapkan dosen pembimbing.');

        $this->redirectRoute('mahasiswa.riwayat.index');
    }

    public function render(): View
    {
        return view('livewire.mahasiswa.pengajuan-judul-form');
    }
}
