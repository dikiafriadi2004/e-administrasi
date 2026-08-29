<?php

namespace App\Livewire\Mahasiswa;

use App\Models\PengajuanSurat;
use App\Models\StatusHistory;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Validate;
use Livewire\Component;

class PengajuanAktifKuliahForm extends Component
{
    /** Keperluan dari pilihan dropdown */
    #[Validate('required|string')]
    public string $keperluan = '';

    /** Isi manual jika keperluan = "lainnya" */
    #[Validate('required_if:keperluan,lainnya|nullable|string|max:255')]
    public string $keperluanManual = '';

    /** Nama instansi/lembaga tujuan surat (opsional) */
    #[Validate('nullable|string|max:255')]
    public string $tujuanInstansi = '';

    /** Pilihan keperluan surat */
    public static array $pilihanKeperluan = [
        'Melamar Beasiswa' => 'Melamar Beasiswa',
        'Magang / Praktik Kerja Lapangan (PKL)' => 'Magang / Praktik Kerja Lapangan (PKL)',
        'Administrasi Perbankan / Asuransi' => 'Administrasi Perbankan / Asuransi',
        'Keperluan Akademik' => 'Keperluan Akademik',
        'Pengurusan Visa / Pertukaran Pelajar' => 'Pengurusan Visa / Pertukaran Pelajar',
        'lainnya' => 'Lainnya (isi manual)',
    ];

    /** Teks keperluan final yang masuk ke surat */
    public function getKeperluanFinalProperty(): string
    {
        if ($this->keperluan === 'lainnya') {
            return trim($this->keperluanManual);
        }

        return $this->keperluan;
    }

    public function submit(): void
    {
        $this->validate();

        $mahasiswa = Auth::user()->mahasiswa;

        $pengajuan = PengajuanSurat::create([
            'mahasiswa_id' => $mahasiswa->id,
            'jenis_surat' => 'aktif_kuliah',
            'data_form' => [
                'keperluan' => $this->keperluanFinal,
                'tujuan_instansi' => $this->tujuanInstansi,
            ],
            'status' => 'diajukan',
        ]);

        StatusHistory::create([
            'model_type' => PengajuanSurat::class,
            'model_id' => $pengajuan->id,
            'status_lama' => null,
            'status_baru' => 'diajukan',
            'catatan' => 'Pengajuan Surat Aktif Kuliah disubmit oleh mahasiswa.',
            'changed_by' => Auth::id(),
            'created_at' => now(),
        ]);

        session()->flash('success', 'Pengajuan Surat Aktif Kuliah berhasil dikirim. Admin akan segera memproses surat Anda.');

        $this->redirectRoute('mahasiswa.riwayat.index');
    }

    public function render(): View
    {
        return view('livewire.mahasiswa.pengajuan-aktif-kuliah-form', [
            'pilihanKeperluan' => self::$pilihanKeperluan,
        ]);
    }
}
