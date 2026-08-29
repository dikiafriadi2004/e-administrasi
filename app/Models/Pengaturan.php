<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class Pengaturan extends Model
{
    protected $table = 'pengaturan';

    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = ['key', 'value', 'label', 'grup'];

    /**
     * Ambil nilai pengaturan. Return $default jika key tidak ada.
     */
    public static function nilai(string $key, mixed $default = null): mixed
    {
        return Cache::remember("pengaturan.{$key}", 3600, function () use ($key, $default) {
            $record = static::find($key);

            return $record?->value ?? $default;
        });
    }

    /**
     * Simpan atau update nilai pengaturan, lalu hapus cache-nya.
     */
    public static function set(string $key, ?string $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        );

        Cache::forget("pengaturan.{$key}");
    }

    /**
     * Hapus cache semua pengaturan (gunakan setelah bulk update).
     */
    public static function flushCache(): void
    {
        $keys = static::pluck('key');
        foreach ($keys as $key) {
            Cache::forget("pengaturan.{$key}");
        }
    }
}
