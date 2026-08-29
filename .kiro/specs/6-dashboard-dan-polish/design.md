# Design — Milestone 6: Dashboard & Polish

## Dashboard Rasio Dosen

### Query dengan Caching

Dashboard rasio menggunakan `RasioDosenService::getRingkasanRasio()` yang
sudah ada dari M5, dibungkus cache:

```php
// Admin\DashboardController@rasio
public function rasio(): View
{
    $rasio = Cache::remember('rasio_dosen', 60, function () {
        return $this->rasioService->getRingkasanRasio();
    });

    return view('admin.dashboard.rasio', compact('rasio'));
}
```

Cache key `rasio_dosen` di-invalidate setiap kali ada perubahan
`dosen_pembimbing_id` atau `dosen_penguji_id` (bisa via model observer
atau dipanggil manual di `PengajuanStateService` setelah transisi).

### Tampilan Tabel Rasio

```
┌──────────────────────────────────────────────────────────────────┐
│  Dashboard Rasio Dosen Pembimbing & Penguji                      │
├──────────────────┬─────────┬────────────┬──────────┬────────────┤
│ Nama Dosen       │  NIP    │ Bimbingan  │ Penguji  │ Status     │
├──────────────────┼─────────┼────────────┼──────────┼────────────┤
│ Dr. Andi         │ 1234... │     2      │    1     │ Tersedia   │
│ Prof. Budi       │ 5678... │     3      │    2     │ Tersedia   │
│ Dr. Citra        │ 9012... │     5      │    3     │ ⚠ Penuh   │
└──────────────────┴─────────┴────────────┴──────────┴────────────┘
  Terakhir diperbarui: 2 menit lalu
```

---

## Dashboard Utama — Kartu Statistik

### Admin Dashboard

```php
// Admin\DashboardController@index
public function index(): View
{
    return view('admin.dashboard.index', [
        'pengajuanHariIni'       => PengajuanSurat::whereDate('created_at', today())->count(),
        'menungguVerifikasi'     => PengajuanSurat::where('status', 'diajukan')->count()
                                  + PengajuanJudul::where('status', 'diajukan')->count(),
        'suratAktifBulanIni'    => PengajuanSurat::whereMonth('created_at', now()->month)
                                       ->whereYear('created_at', now()->year)->count(),
        'totalMahasiswaAktif'   => User::where('role', 'mahasiswa')
                                       ->where('is_active', true)->count(),
    ]);
}
```

### Kaprodi Dashboard

```php
// Kaprodi\DashboardController@index
public function index(): View
{
    $topTersedia = Cache::remember('top_dosen_tersedia', 60, function () {
        return $this->rasioService->getDaftarDosenTerurut('pembimbing')->take(3);
    });

    return view('kaprodi.dashboard.index', [
        'antrianCount'    => PengajuanSurat::where('status', 'diverifikasi')->count()
                          + PengajuanJudul::where('status', 'diverifikasi')->count(),
        'judulDisetujui'  => PengajuanJudul::where('status', 'disetujui')
                                ->whereMonth('updated_at', now()->month)->count(),
        'topTersedia'     => $topTersedia,
    ]);
}
```

### Mahasiswa Dashboard

```php
// Mahasiswa\DashboardController@index
public function index(): View
{
    $mahasiswa = auth()->user()->mahasiswa;

    return view('mahasiswa.dashboard', [
        'judulAktif' => PengajuanJudul::where('mahasiswa_id', $mahasiswa->id)
                            ->whereNotIn('status', ['ditolak'])->first(),
        'suratAktif' => PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)
                            ->whereNotIn('status', ['ditolak', 'selesai'])
                            ->latest()->get(),
    ]);
}
```

---

## Arsip Surat (Admin)

### Controller

```php
// Admin\ArsipSuratController@index
public function index(Request $request): View
{
    $query = PengajuanSurat::with(['mahasiswa.user', 'pengajuanJudul'])
        ->latest();

    if ($request->filled('jenis')) {
        $query->where('jenis_surat', $request->jenis);
    }
    if ($request->filled('status')) {
        $query->where('status', $request->status);
    }
    if ($request->filled('q')) {
        $query->whereHas('mahasiswa.user', fn($q) =>
            $q->where('name', 'like', "%{$request->q}%")
        )->orWhereHas('mahasiswa', fn($q) =>
            $q->where('nim', 'like', "%{$request->q}%")
        );
    }
    if ($request->filled('dari') && $request->filled('sampai')) {
        $query->whereBetween('created_at', [
            $request->dari . ' 00:00:00',
            $request->sampai . ' 23:59:59',
        ]);
    }

    $surat = $query->paginate(20)->withQueryString();

    return view('admin.arsip.index', compact('surat'));
}
```

---

## Import Massal Mahasiswa dari Excel

### Dependency

```bash
composer require maatwebsite/excel
```

### Class Import

```php
// app/Imports/MahasiswaImport.php

class MahasiswaImport implements ToCollection, WithHeadingRow, WithValidation
{
    public array $berhasil = [];
    public array $dilewati = [];   // duplikat
    public array $gagal    = [];   // validasi gagal

    public function collection(Collection $rows): void
    {
        foreach ($rows as $index => $row) {
            $baris = $index + 2; // baris Excel (1-indexed header, data mulai baris 2)

            // Cek duplikat NIM atau email
            if (User::where('email', $row['email'])->exists()
                || Mahasiswa::where('nim', $row['nim'])->exists()
            ) {
                $this->dilewati[] = ['baris' => $baris, 'nim' => $row['nim'], 'alasan' => 'Duplikat NIM/email'];
                continue;
            }

            // Validasi manual per baris
            $validator = Validator::make($row->toArray(), [
                'nim'      => 'required|string|max:20',
                'nama'     => 'required|string|max:255',
                'email'    => 'required|email|max:255',
                'angkatan' => 'required|digits:4',
            ]);

            if ($validator->fails()) {
                $this->gagal[] = [
                    'baris'  => $baris,
                    'nim'    => $row['nim'] ?? '-',
                    'alasan' => implode(', ', $validator->errors()->all()),
                ];
                continue;
            }

            DB::transaction(function () use ($row) {
                $user = User::create([
                    'name'     => $row['nama'],
                    'email'    => $row['email'],
                    'password' => bcrypt($row['nim']),  // password default = NIM
                    'role'     => 'mahasiswa',
                    'is_active' => true,
                ]);
                Mahasiswa::create([
                    'user_id'  => $user->id,
                    'nim'      => $row['nim'],
                    'angkatan' => $row['angkatan'],
                ]);
            });

            $this->berhasil[] = $row['nim'];
        }
    }
}
```

### Format Header Excel yang Diterima

| nim | nama | email | angkatan |
|-----|------|-------|----------|
| 2021001 | Budi Santoso | budi@mail.com | 2021 |

Header case-insensitive (`WithHeadingRow` normalize ke snake_case).

---

## Polish: Flash Message Layout

Tambahkan komponen Blade `resources/views/components/flash-message.blade.php`:

```blade
@if (session('success'))
    <div class="mb-4 rounded-lg bg-green-100 px-4 py-3 text-sm text-green-800">
        {{ session('success') }}
    </div>
@endif

@if (session('error'))
    <div class="mb-4 rounded-lg bg-red-100 px-4 py-3 text-sm text-red-800">
        {{ session('error') }}
    </div>
@endif
```

Include di `layouts/app.blade.php` tepat setelah tag `<main>`.

---

## Polish: Exception Handler

Di `bootstrap/app.php`, tambahkan handling untuk exception kustom:

```php
->withExceptions(function (Exceptions $exceptions) {
    $exceptions->render(function (SuratGenerationException $e, Request $request) {
        if ($request->expectsJson()) {
            return response()->json(['message' => $e->getMessage()], 500);
        }
        return back()->with('error', $e->getMessage());
    });

    $exceptions->render(function (InvalidStateTransitionException $e, Request $request) {
        return back()->with('error', 'Aksi tidak valid: ' . $e->getMessage());
    });
})
```

---

## File Kode Baru di Milestone Ini

```
app/
├── Imports/
│   └── MahasiswaImport.php
└── Http/Controllers/
    ├── Admin/
    │   ├── ArsipSuratController.php
    │   ├── MahasiswaImportController.php
    │   └── DashboardController.php         (perbarui method index + rasio)
    └── Kaprodi/
        └── DashboardController.php         (perbarui method index)

resources/views/
├── admin/
│   ├── dashboard/
│   │   ├── index.blade.php     ← kartu statistik
│   │   └── rasio.blade.php     ← tabel rasio dosen
│   ├── arsip/
│   │   └── index.blade.php     ← tabel semua surat + filter
│   └── mahasiswa/
│       └── import.blade.php    ← form upload + hasil ringkasan
├── kaprodi/
│   └── dashboard/
│       └── index.blade.php
├── mahasiswa/
│   └── dashboard.blade.php     (perbarui dengan status aktif)
└── components/
    └── flash-message.blade.php
```
