<?php

namespace Database\Factories;

use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<PengajuanJudul>
 */
class PengajuanJudulFactory extends Factory
{
    public function definition(): array
    {
        return [
            'mahasiswa_id' => Mahasiswa::factory(),
            'judul' => fake()->sentence(8),
            'bidang_kajian' => fake()->randomElement(['Rekayasa Perangkat Lunak', 'Jaringan Komputer', 'Sistem Informasi', 'Kecerdasan Buatan']),
            'ringkasan' => fake()->paragraph(3),
            'dosen_pembimbing_id' => null,
            'status' => 'diajukan',
            'catatan_penolakan' => null,
            'file_pendukung' => null,
            'nama_file_pendukung' => null,
        ];
    }

    public function diverifikasi(): static
    {
        return $this->state(['status' => 'diverifikasi']);
    }

    public function disetujui(): static
    {
        return $this->state(['status' => 'disetujui']);
    }

    public function ditolak(): static
    {
        return $this->state([
            'status' => 'ditolak',
            'catatan_penolakan' => fake()->sentence(),
        ]);
    }
}
