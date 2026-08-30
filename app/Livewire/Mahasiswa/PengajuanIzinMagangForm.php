<?php

namespace App\Livewire\Mahasiswa;

use App\Models\BerkasPengajuan;
use App\Models\PengajuanSurat;
use App\Models\StatusHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;
use Livewire\WithFileUploads;

class PengajuanIzinMagangForm extends Component
{
    use WithFileUploads;

    #[Validate('required|string|max:255')]
    public string $namaInstansi = '';

    #[Validate('required|string|max:500')]
    public string $alamatInstansi = '';

    #[Validate('required|date')]
    public string $tanggalMulai = '';

    #[Validate('required|date|after_or_equal:tanggalMulai')]
    public string $tanggalSelesai = '';

    /**
     * File surat pengajuan magang dari instansi (opsional).
     * Mahasiswa bisa upload surat resmi dari instansi sebagai bukti pengajuan.
     */
    #[Validate('nullable|file|mimes:pdf,doc,docx|max:5120')]
    public $fileSuratInstansi = null;

    protected $messages = [
        'namaInstansi.required' => 'Nama instansi tujuan magang wajib diisi.',
        'alamatInstansi.required' => 'Alamat instansi wajib diisi.',
        'tanggalMulai.required' => 'Tanggal mulai magang wajib diisi.',
        'tanggalSelesai.required' => 'Tanggal selesai magang wajib diisi.',
        'tanggalSelesai.after_or_equal' => 'Tanggal selesai harus setelah atau sama dengan tanggal mulai.',
        'fileSuratInstansi.mimes' => 'File surat harus berformat PDF, DOC, atau DOCX.',
        'fileSuratInstansi.max' => 'Ukuran file maksimal 5 MB.',
    ];

    public function submit(): void
    {
        $this->validate();

        $mahasiswa = Auth::user()->mahasiswa;

        $pengajuan = PengajuanSurat::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => 'izin_magang',
            'data_form' => [
                'nama_instansi' => $this->namaInstansi,
                'alamat_instansi' => $this->alamatInstansi,
                'tanggal_mulai' => $this->tanggalMulai,
                'tanggal_selesai' => $this->tanggalSelesai,
            ],
            'status' => 'diajukan',
        ]);

        // Upload surat dari instansi jika ada
        if ($this->fileSuratInstansi) {
            $ext = $this->fileSuratInstansi->getClientOriginalExtension();
            $namaAsli = $this->fileSuratInstansi->getClientOriginalName();
            $path = "berkas/{$mahasiswa->nim}/magang/".Str::uuid().".{$ext}";

            Storage::disk('private')->put($path, file_get_contents($this->fileSuratInstansi->getRealPath()));

            BerkasPengajuan::create([
                'pengajuan_type' => PengajuanSurat::class,
                'pengajuan_id' => $pengajuan->id,
                'label' => 'Surat Pengajuan dari Instansi',
                'path_file' => $path,
                'nama_asli' => $namaAsli,
            ]);
        }

        StatusHistory::create([
            'model_type' => PengajuanSurat::class,
            'model_id' => $pengajuan->id,
            'status_lama' => null,
            'status_baru' => 'diajukan',
            'catatan' => 'Pengajuan Surat Izin Magang disubmit oleh mahasiswa.',
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);

        session()->flash('success', 'Pengajuan Surat Izin Magang berhasil dikirim. Admin akan segera memproses surat Anda.');

        $this->redirectRoute('mahasiswa.riwayat.index');
    }

    public function render(): View
    {
        return view('livewire.mahasiswa.pengajuan-izin-magang-form');
    }
}
