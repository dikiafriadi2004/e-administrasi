<?php

namespace App\Models;

use Database\Factories\DosenFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Dosen extends Model
{
    /** @use HasFactory<DosenFactory> */
    use HasFactory;

    protected $fillable = [
        'nama',
        'nip',
        'kapasitas_maksimal',
    ];

    protected function casts(): array
    {
        return [
            'kapasitas_maksimal' => 'integer',
        ];
    }

    /**
     * Apakah dosen sudah mencapai kapasitas maksimal bimbingan.
     * Membutuhkan withCount('pengajuanJudul') agar jumlah_bimbingan tersedia.
     */
    public function isKapasitasPenuh(): bool
    {
        if ($this->kapasitas_maksimal === null) {
            return false;
        }

        $jumlahBimbingan = $this->jumlah_bimbingan ?? 0;

        return $jumlahBimbingan >= $this->kapasitas_maksimal;
    }

    public function pengajuanJudul(): HasMany
    {
        return $this->hasMany(PengajuanJudul::class, 'dosen_pembimbing_id');
    }

    public function pengajuanSuratPenguji(): HasMany
    {
        return $this->hasMany(PengajuanSurat::class, 'dosen_penguji_id');
    }

    public function pengajuanSuratPenguji2(): HasMany
    {
        return $this->hasMany(PengajuanSurat::class, 'dosen_penguji_2_id');
    }
}
