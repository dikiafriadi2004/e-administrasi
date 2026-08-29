<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Models\Pengaturan;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class RasioDosenService
{
    /**
     * Ekstrak tahun mulai dari string tahun akademik.
     * "2025/2026" → 2025, "2026" → 2026
     */
    public static function tahunMulaiDari(string $tahunAkademik): int
    {
        return (int) explode('/', $tahunAkademik)[0];
    }

    /**
     * Semua tahun akademik yang pernah ada di data (untuk filter historis).
     *
     * @return array<string> format: ["2024/2025", "2025/2026", ...]
     */
    public function getTahunTersedia(): array
    {
        $tahunJudul = PengajuanJudul::selectRaw('YEAR(created_at) as tahun')
            ->distinct()
            ->pluck('tahun');

        $tahunSurat = PengajuanSurat::selectRaw('YEAR(created_at) as tahun')
            ->distinct()
            ->pluck('tahun');

        $tahunList = $tahunJudul->merge($tahunSurat)
            ->unique()
            ->sort()
            ->values();

        // Format sebagai "YYYY/YYYY+1"
        return $tahunList->map(fn ($y) => "{$y}/".($y + 1))->toArray();
    }

    /**
     * Tahun akademik yang sedang aktif dari Pengaturan.
     * Auto-detect berdasarkan bulan jika Pengaturan belum diset atau tidak match.
     *
     * Logika: Agustus–Desember = tahun sekarang/tahun+1
     *         Januari–Juli     = tahun-1/tahun sekarang
     */
    public function getTahunAktif(): string
    {
        $ta = Pengaturan::nilai('tahun_akademik');

        if ($ta && str_contains($ta, '/')) {
            // Validasi: cek apakah tahun dari Pengaturan sesuai kalender sekarang
            $tahunMulai = self::tahunMulaiDari($ta);
            $sekarang = now();

            // Bulan >= 8 berarti tahun akademik baru dimulai
            $tahunAktifSeharusnya = $sekarang->month >= 8
                ? $sekarang->year
                : $sekarang->year - 1;

            // Gunakan dari Pengaturan hanya jika persis sama dengan kalkulasi sekarang
            if ($tahunMulai === $tahunAktifSeharusnya) {
                return $ta;
            }
        }

        // Fallback: hitung otomatis dari tanggal sekarang
        $y = now()->month >= 8 ? now()->year : now()->year - 1;

        return "{$y}/".($y + 1);
    }

    /**
     * Daftar dosen dengan rasio bimbingan/pengujian, terurut dari beban terkecil.
     * Filter berdasarkan tahun akademik (created_at dalam rentang tahun tsb).
     *
     * @param  string  $konteks  'pembimbing' | 'penguji'
     * @param  int|null  $excludeDosenId  exclude dosen tertentu
     * @param  string|null  $tahunAkademik  "2025/2026" — null = aktif dari Pengaturan
     */
    public function getDaftarDosenTerurut(
        string $konteks = 'pembimbing',
        ?int $excludeDosenId = null,
        ?string $tahunAkademik = null
    ): Collection {
        $ta = $tahunAkademik ?? $this->getTahunAktif();
        [$mulai, $akhir] = $this->rentangTahunAkademik($ta);

        $dosen = Dosen::query()
            ->withCount([
                // Bimbingan dalam tahun akademik ini
                'pengajuanJudul as jumlah_bimbingan' => fn (Builder $q) => $q
                    ->whereNotIn('status', ['ditolak'])
                    ->whereBetween('created_at', [$mulai, $akhir]),

                // Penguji 1 dalam tahun akademik ini
                'pengajuanSuratPenguji as jumlah_penguji_1' => fn (Builder $q) => $q
                    ->whereNotIn('status', ['ditolak'])
                    ->whereBetween('created_at', [$mulai, $akhir]),

                // Penguji 2 dalam tahun akademik ini
                'pengajuanSuratPenguji2 as jumlah_penguji_2' => fn (Builder $q) => $q
                    ->whereNotIn('status', ['ditolak'])
                    ->whereBetween('created_at', [$mulai, $akhir]),
            ])
            ->get()
            ->each(function ($d) {
                $d->jumlah_pengujian = $d->jumlah_penguji_1 + $d->jumlah_penguji_2;
            });

        if ($excludeDosenId) {
            $dosen = $dosen->filter(fn ($d) => $d->id !== $excludeDosenId);
        }

        $sortColumn = $konteks === 'penguji' ? 'jumlah_pengujian' : 'jumlah_bimbingan';

        return $dosen->sortBy([$sortColumn, 'nama'])->values();
    }

    /**
     * Ringkasan rasio semua dosen untuk dashboard.
     *
     * @param  string|null  $tahunAkademik  null = tahun aktif dari Pengaturan
     * @return Collection<int, Dosen>
     */
    public function getRingkasanRasio(?string $tahunAkademik = null): Collection
    {
        $ta = $tahunAkademik ?? $this->getTahunAktif();
        [$mulai, $akhir] = $this->rentangTahunAkademik($ta);

        return Dosen::query()
            ->withCount([
                'pengajuanJudul as jumlah_bimbingan' => fn (Builder $q) => $q
                    ->whereNotIn('status', ['ditolak'])
                    ->whereBetween('created_at', [$mulai, $akhir]),

                'pengajuanSuratPenguji as jumlah_penguji_1' => fn (Builder $q) => $q
                    ->whereNotIn('status', ['ditolak'])
                    ->whereBetween('created_at', [$mulai, $akhir]),

                'pengajuanSuratPenguji2 as jumlah_penguji_2' => fn (Builder $q) => $q
                    ->whereNotIn('status', ['ditolak'])
                    ->whereBetween('created_at', [$mulai, $akhir]),
            ])
            ->orderBy('nama')
            ->get()
            ->each(function ($d) {
                $d->jumlah_pengujian = $d->jumlah_penguji_1 + $d->jumlah_penguji_2;
            });
    }

    /**
     * Konversi string "2025/2026" menjadi rentang datetime.
     *
     * Tahun akademik dimulai 1 Agustus tahun pertama
     * dan berakhir 31 Juli tahun kedua.
     *
     * @return array{0: string, 1: string} [mulai, akhir]
     */
    private function rentangTahunAkademik(string $ta): array
    {
        $tahunMulai = self::tahunMulaiDari($ta);
        $tahunAkhir = $tahunMulai + 1;

        return [
            "{$tahunMulai}-08-01 00:00:00",
            "{$tahunAkhir}-07-31 23:59:59",
        ];
    }
}
