<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class PengajuanSurat extends Model
{
    use HasFactory;

    protected $table = 'pengajuan_surat';

    protected $fillable = [
        'mahasiswa_id',
        'jenis_surat',
        'pengajuan_judul_id',
        'data_form',
        'nomor_surat',
        'dosen_penguji_id',
        'dosen_penguji_2_id',
        'tanggal_jadwal',
        'waktu_jadwal',
        'tempat_jadwal',
        'catatan_kaprodi',
        'status',
        'catatan_penolakan',
        'file_docx',
        'file_pdf',
        'file_scan',
        'file_pendukung',
        'nama_file_pendukung',
        'generated_at',
    ];

    protected function casts(): array
    {
        return [
            'data_form' => 'array',
            'generated_at' => 'datetime',
            'tanggal_jadwal' => 'date',
        ];
    }

    public function mahasiswa(): BelongsTo
    {
        return $this->belongsTo(Mahasiswa::class);
    }

    public function pengajuanJudul(): BelongsTo
    {
        return $this->belongsTo(PengajuanJudul::class);
    }

    public function dosenPenguji(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_penguji_id');
    }

    public function dosenPenguji2(): BelongsTo
    {
        return $this->belongsTo(Dosen::class, 'dosen_penguji_2_id');
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

    /** Apakah jadwal sudah ditetapkan kaprodi */
    public function sudahDijadwalkan(): bool
    {
        return $this->tanggal_jadwal !== null;
    }

    public function sudahAdaScan(): bool
    {
        return $this->file_scan !== null;
    }

    /**
     * Buat nama file download yang konsisten.
     * Format: {nim}_{nama_depan}_{jenis_surat}.{ext}
     * Contoh: 1215_Herman_seminar_proposal.docx
     */
    public static function namaFileDownload(PengajuanSurat $surat, string $ext): string
    {
        $nim = $surat->mahasiswa?->nim ?? 'mahasiswa';
        $nama = $surat->mahasiswa?->user?->name ?? '';
        // Ambil nama depan saja (kata pertama)
        $namaDepan = explode(' ', trim($nama))[0];
        $jenis = $surat->jenis_surat;

        $bagian = array_filter([$nim, $namaDepan, $jenis], fn ($v) => $v !== '');
        $slug = preg_replace('/[^A-Za-z0-9\-_]/', '_', implode('_', $bagian));

        return $slug.'.'.$ext;
    }

    public function sudahDigenerate(): bool
    {
        return $this->file_docx !== null && $this->file_pdf !== null;
    }
}
