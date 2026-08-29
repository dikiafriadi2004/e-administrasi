<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PengajuanJudul extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_judul';

    protected $fillable = [
        'mahasiswa_id',
        'judul',
        'bidang_kajian',
        'ringkasan',
        'dosen_pembimbing_id',
        'dosen_pembimbing_2_id',
        'status',
        'catatan_penolakan',
        'catatan_kaprodi',
        'file_pendukung',
        'nama_file_pendukung',
    ];

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function dosenPembimbing(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_pembimbing_id');
    }

    public function dosenPembimbing2(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_pembimbing_2_id');
    }

    public function pengajuanSurat(): HasMany
    {
        return $this->hasMany(PengajuanSurat::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(StatusHistory::class, 'model_id')
            ->where('model_type', self::class)
            ->orderBy('created_at');
    }

    public function berkas(): MorphMany
    {
        return $this->morphMany(BerkasPengajuan::class, 'pengajuan');
    }

    public function isAktif(): bool
    {
        return ! in_array($this->status, ['ditolak']);
    }
}
