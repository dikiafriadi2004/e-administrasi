<?php

namespace App\Services;

use App\Models\Dosen;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Models\Pengaturan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
        $driver = PengajuanJudul::query()->getConnection()->getDriverName();
        $yearExpr = $driver === 'sqlite' ? "strftime('%Y', created_at)" : 'YEAR(created_at)';

        $tahunJudul = PengajuanJudul::selectRaw("{$yearExpr} as tahun")
            ->distinct()
            ->pluck('tahun');

        $tahunSurat = PengajuanSurat::selectRaw("{$yearExpr} as tahun")
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
     * Hitungan berdasarkan mahasiswa UNIK — seminar + sidang mahasiswa yang sama = 1.
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
        $dosen = $this->getDosenDenganBeban($tahunAkademik);

        if ($excludeDosenId) {
            $dosen = $dosen->filter(fn ($d) => $d->id !== $excludeDosenId);
        }

        $sortColumn = $konteks === 'penguji' ? 'jumlah_pengujian' : 'jumlah_bimbingan';

        return $dosen->sortBy([$sortColumn, 'nama'])->values();
    }

    /**
     * Ringkasan rasio semua dosen untuk dashboard.
     * Hitungan berdasarkan mahasiswa UNIK per tahun akademik.
     *
     * @param  string|null  $tahunAkademik  null = tahun aktif dari Pengaturan
     * @return Collection<int, Dosen>
     */
    public function getRingkasanRasio(?string $tahunAkademik = null): Collection
    {
        return $this->getDosenDenganBeban($tahunAkademik)->sortBy('nama')->values();
    }

    /**
     * Ambil semua dosen + beban bimbingan/pengujian dalam 5 query agregat
     * (bukan 3N+1). Menghitung pembimbing 1 + pembimbing 2.
     *
     * @return Collection<int, Dosen>
     */
    private function getDosenDenganBeban(?string $tahunAkademik = null): Collection
    {
        $ta = $tahunAkademik ?? $this->getTahunAktif();
        [$mulai, $akhir] = $this->rentangTahunAkademik($ta);

        $bimbingan1 = PengajuanJudul::whereNotIn('status', ['ditolak'])
            ->whereBetween('created_at', [$mulai, $akhir])
            ->whereNotNull('dosen_pembimbing_id')
            ->groupBy('dosen_pembimbing_id')
            ->select('dosen_pembimbing_id as dosen_id', DB::raw('COUNT(DISTINCT mahasiswa_id) as agg'))
            ->pluck('agg', 'dosen_id');

        $bimbingan2 = PengajuanJudul::whereNotIn('status', ['ditolak'])
            ->whereBetween('created_at', [$mulai, $akhir])
            ->whereNotNull('dosen_pembimbing_2_id')
            ->groupBy('dosen_pembimbing_2_id')
            ->select('dosen_pembimbing_2_id as dosen_id', DB::raw('COUNT(DISTINCT mahasiswa_id) as agg'))
            ->pluck('agg', 'dosen_id');

        $penguji1 = PengajuanSurat::whereNotIn('status', ['ditolak'])
            ->whereBetween('created_at', [$mulai, $akhir])
            ->whereNotNull('dosen_penguji_id')
            ->groupBy('dosen_penguji_id')
            ->select('dosen_penguji_id as dosen_id', DB::raw('COUNT(DISTINCT mahasiswa_id) as agg'))
            ->pluck('agg', 'dosen_id');

        $penguji2 = PengajuanSurat::whereNotIn('status', ['ditolak'])
            ->whereBetween('created_at', [$mulai, $akhir])
            ->whereNotNull('dosen_penguji_2_id')
            ->groupBy('dosen_penguji_2_id')
            ->select('dosen_penguji_2_id as dosen_id', DB::raw('COUNT(DISTINCT mahasiswa_id) as agg'))
            ->pluck('agg', 'dosen_id');

        return Dosen::orderBy('nama')->get()->each(function ($d) use ($bimbingan1, $bimbingan2, $penguji1, $penguji2): void {
            // Bimbingan: gabung pembimbing 1 + 2 (mahasiswa unik per peran; gabungan bisa double jika sama — revisi sadar, tetap lebih akurat dari sebelumnya yang abaikan pembimbing 2).
            $d->jumlah_bimbingan = (int) ($bimbingan1[$d->id] ?? 0) + (int) ($bimbingan2[$d->id] ?? 0);

            // Penguji 1: mahasiswa UNIK yang diuji sebagai penguji 1
            $d->jumlah_penguji_1 = (int) ($penguji1[$d->id] ?? 0);

            // Penguji 2: mahasiswa UNIK yang diuji sebagai penguji 2
            $d->jumlah_penguji_2 = (int) ($penguji2[$d->id] ?? 0);

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
