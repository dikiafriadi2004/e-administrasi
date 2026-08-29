<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class TemplateSurat extends Model
{
    protected $table = 'templates_surat';

    protected $fillable = [
        'jenis_surat',
        'path_file',
        'versi',
        'is_aktif',
    ];

    protected function casts(): array
    {
        return [
            'is_aktif' => 'boolean',
            'versi' => 'integer',
        ];
    }

    /** Scope: hanya template yang aktif. */
    public function scopeAktif(Builder $query): Builder
    {
        return $query->where('is_aktif', true);
    }

    /** Scope: filter per jenis surat. */
    public function scopeJenis(Builder $query, string $jenis): Builder
    {
        return $query->where('jenis_surat', $jenis);
    }
}
