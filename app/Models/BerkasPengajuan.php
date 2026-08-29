<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class BerkasPengajuan extends Model
{
    protected $table = 'berkas_pengajuan';

    protected $fillable = [
        'pengajuan_type',
        'pengajuan_id',
        'label',
        'path_file',
        'nama_asli',
    ];

    /** Relasi polymorphic ke PengajuanJudul atau PengajuanSurat */
    public function pengajuan(): MorphTo
    {
        return $this->morphTo();
    }
}
