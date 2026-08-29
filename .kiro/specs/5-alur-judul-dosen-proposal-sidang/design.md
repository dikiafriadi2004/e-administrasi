# Design — Milestone 5: Alur Judul → Dosen → Proposal → Sidang

## Kalkulasi Rasio Dosen — Query Design

Rasio tidak disimpan di tabel, selalu dihitung on-the-fly. Gunakan
Eloquent `withCount` dengan constraint untuk efisiensi:

```php
// app/Services/RasioDosenService.php

public function getDaftarDosenTerurut(string $konteks = 'pembimbing'): Collection
{
    // Konteks 'pembimbing': hitung dari pengajuan_judul aktif
    // Konteks 'penguji':    hitung dari pengajuan_surat sidang aktif

    return Dosen::query()
        ->withCount([
            // Jumlah bimbingan aktif (pengajuan judul bukan ditolak)
            'pengajuanJudul as jumlah_bimbingan' => fn($q) =>
                $q->whereNotIn('status', ['ditolak']),

            // Jumlah penugasan penguji aktif
            'pengajuanSuratPenguji as jumlah_pengujian' => fn($q) =>
                $q->whereNotIn('status', ['ditolak']),
        ])
        ->orderBy(
            $konteks === 'pembimbing' ? 'jumlah_bimbingan' : 'jumlah_pengujian'
        )
        ->orderBy('nama')
        ->get();
}
```

---

## Model Relations yang Dibutuhkan

### Model `Dosen`

```php
class Dosen extends Model
{
    // Relasi ke pengajuan_judul sebagai pembimbing
    public function pengajuanJudul(): HasMany
    {
        return $this->hasMany(PengajuanJudul::class, 'dosen_pembimbing_id');
    }

    // Relasi ke pengajuan_surat sebagai penguji
    public function pengajuanSuratPenguji(): HasMany
    {
        return $this->hasMany(PengajuanSurat::class, 'dosen_penguji_id');
    }

    // Helper: apakah kapasitas penuh?
    public function isKapasitasPenuh(): bool
    {
        if ($this->kapasitas_maksimal === null) {
            return false;
        }
        return ($this->jumlah_bimbingan ?? $this->pengajuanJudul()
            ->whereNotIn('status', ['ditolak'])->count())
            >= $this->kapasitas_maksimal;
    }
}
```

---

## Alur Penentuan Pembimbing (Detail)

```
Kaprodi buka detail pengajuan judul (status: diverifikasi)
                    │
                    ▼
Sistem load daftar dosen via RasioDosenService::getDaftarDosenTerurut('pembimbing')
- Tampilkan dalam tabel: Nama | NIP | Bimbingan Aktif | Kapasitas | Status
- Dosen dengan kapasitas penuh diberi badge merah "Penuh"
                    │
                    ▼
Kaprodi pilih satu dosen (radio button atau select)
→ Jika dosen "Penuh": tampilkan konfirmasi "Kapasitas dosen ini sudah penuh.
  Tetap pilih?" sebelum submit
                    │
                    ▼
Kaprodi submit form (POST)
                    │
                    ▼
Controller:
1. Validasi pengajuan_judul.status === 'diverifikasi'
2. Simpan dosen_pembimbing_id ke pengajuan_judul
3. Panggil stateService->setujuiJudul() → status jadi 'disetujui'
4. Catat status_history
5. Redirect ke antrian dengan flash success
```

---

## Alur Penentuan Penguji (Detail)

```
Kaprodi buka detail pengajuan sidang (status: diverifikasi)
                    │
                    ▼
Sistem load daftar dosen via RasioDosenService::getDaftarDosenTerurut('penguji')
- EXCLUDE dosen_pembimbing_id dari pengajuan_judul terkait
  (pembimbing tidak boleh jadi penguji sidang yang sama)
                    │
                    ▼
Kaprodi pilih dosen penguji dan submit
                    │
                    ▼
Controller:
1. Validasi pengajuan_surat.status === 'diverifikasi'
2. Validasi dosen yang dipilih !== dosen_pembimbing_id
3. Simpan dosen_penguji_id ke pengajuan_surat
4. Panggil stateService->terimaSurat() → status jadi 'menunggu_ttd'
5. Catat status_history
```

---

## Service: RasioDosenService

```
app/Services/RasioDosenService.php
```

```php
class RasioDosenService
{
    /**
     * Daftar dosen dengan rasio bimbingan/pengujian, terurut otomatis.
     * @param  string  $konteks  'pembimbing' | 'penguji'
     * @param  int|null  $excludeDosenId  untuk exclude pembimbing saat pilih penguji
     */
    public function getDaftarDosenTerurut(
        string $konteks = 'pembimbing',
        ?int $excludeDosenId = null
    ): Collection

    /**
     * Ringkasan rasio untuk dashboard (semua dosen, kedua kolom).
     * @return Collection<{nama, nip, jumlah_bimbingan, jumlah_pengujian}>
     */
    public function getRingkasanRasio(): Collection
}
```

---

## Guard "Satu Pengajuan Aktif" di Controller

Dua titik pengecekan — di form create dan saat store:

```php
// Mahasiswa\PengajuanJudulController@create & @store
private function cekPengajuanJudulAktif(Mahasiswa $mahasiswa): void
{
    $aktif = PengajuanJudul::where('mahasiswa_id', $mahasiswa->id)
        ->whereNotIn('status', ['ditolak'])
        ->exists();

    if ($aktif) {
        abort(403, 'Anda sudah memiliki pengajuan judul yang sedang aktif.');
    }
}
```

Untuk pengajuan surat, cek per jenis:

```php
private function cekPengajuanSuratAktif(Mahasiswa $mahasiswa, string $jenisSurat): void
{
    $aktif = PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
        ->where('jenis_surat', $jenisSurat)
        ->whereNotIn('status', ['ditolak'])
        ->exists();

    if ($aktif) {
        abort(403, "Anda sudah memiliki pengajuan {$jenisSurat} yang sedang aktif.");
    }
}
```

---

## Views Kaprodi — Halaman Penentuan Dosen

```
resources/views/kaprodi/antrian/
├── show-judul.blade.php   ← diperbarui: tambah section pilih pembimbing
└── show-surat-sidang.blade.php ← baru: khusus sidang, dengan pilih penguji
```

### Layout Tabel Dosen di View

```
┌─────────────────────────────────────────────────────────┐
│  Pilih Dosen Pembimbing                                 │
├────┬──────────────────┬───────┬─────────┬──────────────┤
│  # │ Nama Dosen       │ Bimb. │ Kapasit │ Status        │
├────┼──────────────────┼───────┼─────────┼──────────────┤
│  ○ │ Dr. Andi         │   2   │    5    │ Tersedia      │
│  ○ │ Prof. Budi       │   3   │    5    │ Tersedia      │
│  ○ │ Dr. Citra        │   5   │    5    │ ⚠ Penuh       │
└────┴──────────────────┴───────┴─────────┴──────────────┘
         [Setujui Judul & Tetapkan Pembimbing]
```

---

## Validasi Akhir di `PengajuanStateService`

Perbarui method `setujuiJudul` dan `terimaSurat` agar validasi pembimbing/penguji:

```php
public function setujuiJudul(PengajuanJudul $judul, User $actor): void
{
    if ($judul->status !== 'diverifikasi') {
        throw new InvalidStateTransitionException(...);
    }
    if ($judul->dosen_pembimbing_id === null) {
        throw new \DomainException('Dosen pembimbing harus dipilih sebelum menyetujui judul.');
    }
    // ... lanjutkan transisi
}

public function terimaSurat(PengajuanSurat $surat, User $actor): void
{
    // Untuk sidang skripsi, penguji wajib dipilih terlebih dahulu
    if ($surat->jenis_surat === 'sidang_skripsi' && $surat->dosen_penguji_id === null) {
        throw new \DomainException('Dosen penguji harus dipilih sebelum menerima pengajuan sidang.');
    }
    // ... lanjutkan transisi
}
```

---

## File Kode Baru di Milestone Ini

```
app/
├── Services/
│   └── RasioDosenService.php         ← baru
└── Http/Controllers/
    └── Kaprodi/
        ├── TetapkanPembimbingController.php   ← baru
        └── TetapkanPengujiController.php      ← baru
```

Routes tambahan di `routes/kaprodi.php`:

```php
Route::post('/antrian/judul/{id}/tetapkan-pembimbing',
    [TetapkanPembimbingController::class, 'store'])
    ->name('antrian.judul.tetapkan-pembimbing');

Route::post('/antrian/surat/{surat}/tetapkan-penguji',
    [TetapkanPengujiController::class, 'store'])
    ->name('antrian.surat.tetapkan-penguji');
```
