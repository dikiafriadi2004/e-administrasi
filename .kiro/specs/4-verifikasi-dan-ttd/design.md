# Design — Milestone 4: Verifikasi Admin & Upload Scan TTD

## State Machine Lengkap

### State Machine `pengajuan_surat`

```
[diajukan]
    │
    ├─ Admin verifikasi  ──────────────────→ [diverifikasi]
    │                                               │
    ├─ Admin tolak       ──→ [ditolak]      Kaprodi terima
    │                                               │
    │                                               ▼
    │                                        [menunggu_ttd]
    │                                               │
    │                                  Admin/Kaprodi upload scan
    │                                               │
    │                                               ▼
    │                                    [sudah_ditandatangani]
    │                                               │
    │                                  Admin/Kaprodi tandai selesai
    │                                               │
    │                                               ▼
    │                                          [selesai]
    │
    └─ Kaprodi tolak (dari diverifikasi) ──→ [ditolak]
```

### State Machine `pengajuan_judul`

```
[diajukan]
    │
    ├─ Admin verifikasi  ──→ [diverifikasi]
    │                               │
    ├─ Admin/Kaprodi tolak ─→ [ditolak]
    │                               │
    │                         Kaprodi setujui
    │                               │
    │                               ▼
    │                          [disetujui]   ← membuka akses seminar
    │
    └─ Admin tolak  ──→ [ditolak]
```

Transisi yang tidak valid harus di-guard di `PengajuanStateService`.

---

## Service: PengajuanStateService

Semua transisi status dilakukan melalui satu service terpusat agar
tidak ada perubahan status yang bypass pencatatan history:

```php
// app/Services/PengajuanStateService.php

class PengajuanStateService
{
    /**
     * Verifikasi admin — diajukan → diverifikasi
     */
    public function verifikasi(Model $pengajuan, User $actor): void

    /**
     * Tolak pengajuan — status apapun → ditolak
     */
    public function tolak(Model $pengajuan, User $actor, string $catatan): void

    /**
     * Kaprodi setujui judul — diverifikasi → disetujui (khusus PengajuanJudul)
     */
    public function setujuiJudul(PengajuanJudul $judul, User $actor): void

    /**
     * Kaprodi terima surat — diverifikasi → menunggu_ttd
     */
    public function terimaSurat(PengajuanSurat $surat, User $actor): void

    /**
     * Upload scan — menunggu_ttd → sudah_ditandatangani
     */
    public function uploadScan(PengajuanSurat $surat, User $actor, string $scanPath): void

    /**
     * Tandai selesai — sudah_ditandatangani → selesai
     */
    public function selesaikan(PengajuanSurat $surat, User $actor): void

    /**
     * Helper: catat ke status_histories
     */
    private function recordHistory(
        Model $pengajuan,
        string $statusLama,
        string $statusBaru,
        User $actor,
        ?string $catatan = null
    ): void
}
```

Setiap method memvalidasi transisi yang sah — jika status tidak valid
untuk aksi tersebut, lempar `InvalidStateTransitionException`.

---

## Controller Structure

```
app/Http/Controllers/
├── Admin/
│   ├── AntriannPengajuanController.php   ← index, show, verifikasi, tolak
│   ├── GenerateSuratController.php       ← generate (sudah dari M2), upload scan
│   └── BuatSuratLangsungController.php   ← buat surat tanpa pengajuan mahasiswa
└── Kaprodi/
    ├── AntrianPengajuanController.php    ← index, show, terima, tolak
    └── UploadScanController.php          ← upload scan (opsional, bisa dari admin)
```

### Contoh: Admin Verifikasi

```php
// Admin\AntrianPengajuanController@verifikasi
public function verifikasi(Request $request, string $jenis, int $id): RedirectResponse
{
    $pengajuan = $jenis === 'judul'
        ? PengajuanJudul::findOrFail($id)
        : PengajuanSurat::findOrFail($id);

    // Guard: hanya bisa verifikasi jika status 'diajukan'
    if ($pengajuan->status !== 'diajukan') {
        abort(422, 'Status pengajuan tidak valid untuk aksi ini.');
    }

    $this->stateService->verifikasi($pengajuan, auth()->user());

    return redirect()->back()->with('success', 'Pengajuan berhasil diverifikasi.');
}
```

### Contoh: Upload Scan

```php
// Admin\GenerateSuratController@uploadScan
public function uploadScan(Request $request, PengajuanSurat $surat): RedirectResponse
{
    $request->validate([
        'file_scan' => 'required|file|mimes:pdf|max:10240',
    ]);

    // Guard: harus status menunggu_ttd
    if ($surat->status !== 'menunggu_ttd') {
        abort(422, 'Surat belum dalam status menunggu TTD.');
    }

    $namaFile = Str::uuid() . '.pdf';
    $path = $request->file('file_scan')->storeAs(
        'surat/' . $surat->id,
        $namaFile,
        'private'
    );

    $this->stateService->uploadScan($surat, auth()->user(), $path);

    return redirect()->back()->with('success', 'Scan berhasil diupload.');
}
```

---

## Views Structure

```
resources/views/
├── admin/
│   ├── antrian/
│   │   ├── index.blade.php         ← tabel semua pengajuan diajukan
│   │   ├── show-judul.blade.php    ← detail judul + tombol verifikasi/tolak
│   │   └── show-surat.blade.php    ← detail surat + tombol verifikasi/tolak/generate/upload-scan
│   └── buat-surat/
│       ├── index.blade.php         ← pilih jenis surat
│       └── create.blade.php        ← form buat langsung
└── kaprodi/
    └── antrian/
        ├── index.blade.php         ← tabel pengajuan diverifikasi
        ├── show-judul.blade.php    ← detail + tombol setujui/tolak
        └── show-surat.blade.php    ← detail + tombol terima/tolak/generate/upload-scan
```

---

## Form Tolak (Modal)

Penolakan membutuhkan alasan yang wajib diisi. Gunakan modal sederhana
(Tailwind + Alpine.js minimal, atau Livewire) yang muncul saat tombol
"Tolak" diklik, dengan textarea catatan alasan dan tombol konfirmasi.

Alpine.js sudah tersedia via CDN atau install:
```bash
npm install alpinejs
```

Atau cukup gunakan `<details>` / inline form tanpa JS jika ingin simpel.

---

## Notifikasi Status di Halaman Mahasiswa

Ketika Admin/Kaprodi mengubah status, mahasiswa yang bersangkutan akan
melihat badge status terbaru saat membuka riwayat (tidak ada real-time
push — cukup refresh halaman). Catatan penolakan ditampilkan dalam
kotak berwarna merah di detail pengajuan.

---

## Buat Surat Langsung (Admin)

Form sederhana dengan field:
- Pilih mahasiswa (dropdown search by NIM/nama)
- Jenis surat
- Data tambahan sesuai jenis (keperluan, tanggal, dst)

Submit langsung ke `BuatSuratLangsungController@store` yang:
1. Membuat `PengajuanSurat` dengan status langsung `menunggu_ttd`
   (skip verifikasi karena Admin yang buat sendiri)
2. Memanggil `SuratGeneratorService::generate()`
3. Redirect ke halaman detail surat yang baru dibuat

---

## Routes Tambahan

```php
// routes/admin.php
Route::prefix('antrian')->name('antrian.')->group(function () {
    Route::get('/', [AntrianPengajuanController::class, 'index'])->name('index');
    Route::get('/judul/{id}', [AntrianPengajuanController::class, 'showJudul'])->name('judul.show');
    Route::get('/surat/{surat}', [AntrianPengajuanController::class, 'showSurat'])->name('surat.show');
    Route::post('/judul/{id}/verifikasi', [AntrianPengajuanController::class, 'verifikasi'])->name('judul.verifikasi');
    Route::post('/surat/{surat}/verifikasi', [AntrianPengajuanController::class, 'verifikasi'])->name('surat.verifikasi');
    Route::post('/judul/{id}/tolak', [AntrianPengajuanController::class, 'tolak'])->name('judul.tolak');
    Route::post('/surat/{surat}/tolak', [AntrianPengajuanController::class, 'tolak'])->name('surat.tolak');
    Route::post('/surat/{surat}/upload-scan', [GenerateSuratController::class, 'uploadScan'])->name('surat.upload-scan');
    Route::post('/surat/{surat}/selesai', [AntrianPengajuanController::class, 'selesaikan'])->name('surat.selesai');
});

Route::prefix('buat-surat')->name('buat-surat.')->group(function () {
    Route::get('/', [BuatSuratLangsungController::class, 'index'])->name('index');
    Route::get('/create', [BuatSuratLangsungController::class, 'create'])->name('create');
    Route::post('/', [BuatSuratLangsungController::class, 'store'])->name('store');
});

// routes/kaprodi.php
Route::prefix('antrian')->name('antrian.')->group(function () {
    Route::get('/', [AntrianPengajuanController::class, 'index'])->name('index');
    Route::get('/judul/{id}', [AntrianPengajuanController::class, 'showJudul'])->name('judul.show');
    Route::get('/surat/{surat}', [AntrianPengajuanController::class, 'showSurat'])->name('surat.show');
    Route::post('/judul/{id}/setujui', [AntrianPengajuanController::class, 'setujui'])->name('judul.setujui');
    Route::post('/judul/{id}/tolak', [AntrianPengajuanController::class, 'tolak'])->name('judul.tolak');
    Route::post('/surat/{surat}/terima', [AntrianPengajuanController::class, 'terima'])->name('surat.terima');
    Route::post('/surat/{surat}/tolak', [AntrianPengajuanController::class, 'tolak'])->name('surat.tolak');
    Route::post('/surat/{surat}/upload-scan', [UploadScanController::class, 'store'])->name('surat.upload-scan');
    Route::post('/surat/{surat}/selesai', [AntrianPengajuanController::class, 'selesaikan'])->name('surat.selesai');
});
```
