<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use App\Models\PengajuanSurat;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PengajuanSurat>
 */
class PengajuanSuratFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mahasiswa_id' => Mahasiswa::factory(),
            'jenis_surat' => 'aktif_kuliah',
            'pengajuan_judul_id' => null,
            'data_form' => [
                'keperluan' => fake()->sentence(4),
                'tujuan_instansi' => fake()->company(),
            ],
            'nomor_surat' => null,
            'dosen_penguji_id' => null,
            'status' => 'diajukan',
            'catatan_penolakan' => null,
            'file_docx' => null,
            'file_pdf' => null,
            'file_scan' => null,
            'file_pendukung' => null,
            'nama_file_pendukung' => null,
            'generated_at' => null,
        ];
    }

    public function aktifKuliah(): static
    {
        return $this->state([
            'jenis_surat' => 'aktif_kuliah',
            'data_form' => [
                'keperluan' => fake()->sentence(4),
                'tujuan_instansi' => fake()->company(),
            ],
        ]);
    }

    public function seminarProposal(): static
    {
        return $this->state([
            'jenis_surat' => 'seminar_proposal',
            'data_form' => ['tanggal_rencana' => now()->addDays(14)->format('Y-m-d')],
        ]);
    }

    public function sidangSkripsi(): static
    {
        return $this->state([
            'jenis_surat' => 'sidang_skripsi',
            'data_form' => [
                'tanggal_rencana' => now()->addDays(30)->format('Y-m-d'),
                'waktu_rencana' => '09.00 WIB',
                'tempat' => 'Ruang Sidang A',
            ],
        ]);
    }

    public function diverifikasi(): static
    {
        return $this->state(['status' => 'diverifikasi']);
    }

    public function menungguTTD(): static
    {
        return $this->state(['status' => 'menunggu_ttd']);
    }

    public function selesai(): static
    {
        return $this->state(['status' => 'selesai']);
    }

    public function ditolak(): static
    {
        return $this->state([
            'status' => 'ditolak',
            'catatan_penolakan' => fake()->sentence(),
        ]);
    }

    public function denganNomor(): static
    {
        return $this->state(['nomor_surat' => '001/UN-XX/FAK/TI/VIII/'.date('Y')]);
    }

    public function sudahGenerate(): static
    {
        return $this->state([
            'file_docx' => 'surat/1/test.docx',
            'file_pdf' => 'surat/1/test.pdf',
            'generated_at' => now(),
        ]);
    }

    public function adaScan(): static
    {
        return $this->state([
            'file_scan' => 'surat/1/scan_test.pdf',
            'status' => 'sudah_ditandatangani',
        ]);
    }
}
