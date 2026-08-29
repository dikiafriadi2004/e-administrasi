# Design — Milestone 1: Autentikasi & Manajemen Akun

## Stack yang Dipakai

| Komponen | Teknologi |
|---|---|
| Auth scaffold | Laravel Breeze (Blade + Tailwind, bukan React/Vue) |
| Role & Gate | Laravel Gates + middleware kustom per role |
| Import Excel | `maatwebsite/excel` atau `spatie/simple-excel` |
| Styling | TailwindCSS v4 |

---

## Database Schema

### Tabel `users`

```
users
├── id               BIGINT UNSIGNED PK AUTO_INCREMENT
├── name             VARCHAR(255)
├── email            VARCHAR(255) UNIQUE NOT NULL
├── email_verified_at TIMESTAMP NULL
├── password         VARCHAR(255)
├── role             ENUM('mahasiswa','admin','kaprodi') NOT NULL
├── is_active        BOOLEAN DEFAULT true
├── remember_token   VARCHAR(100)
├── created_at       TIMESTAMP
└── updated_at       TIMESTAMP
```

### Tabel `mahasiswas`

```
mahasiswas
├── id               BIGINT UNSIGNED PK AUTO_INCREMENT
├── user_id          BIGINT UNSIGNED FK → users.id (CASCADE DELETE)
├── nim              VARCHAR(20) UNIQUE NOT NULL
├── angkatan         YEAR NOT NULL
├── created_at       TIMESTAMP
└── updated_at       TIMESTAMP
```

### Tabel `dosens`

```
dosens
├── id               BIGINT UNSIGNED PK AUTO_INCREMENT
├── nama             VARCHAR(255) NOT NULL
├── nip              VARCHAR(30) UNIQUE NOT NULL
├── kapasitas_maksimal INT UNSIGNED NULL  -- null = tidak dibatasi
├── created_at       TIMESTAMP
└── updated_at       TIMESTAMP
```

> Catatan: Admin dan Kaprodi tidak memiliki tabel profil terpisah — datanya
> langsung di `users`. Dosen bukan user sistem; mereka hanya entitas data
> yang di-assign ke pengajuan.

---

## Arsitektur Role & Middleware

### Gate Definition (`AppServiceProvider`)

```php
Gate::define('is-mahasiswa', fn(User $user) => $user->role === 'mahasiswa' && $user->is_active);
Gate::define('is-admin',     fn(User $user) => $user->role === 'admin');
Gate::define('is-kaprodi',   fn(User $user) => $user->role === 'kaprodi');
```

### Middleware Kustom

Buat tiga middleware kustom agar route group bisa dideklarasikan bersih:

```
app/Http/Middleware/
├── EnsureMahasiswa.php   → abort(403) jika bukan role mahasiswa aktif
├── EnsureAdmin.php       → abort(403) jika bukan role admin
└── EnsureKaprodi.php     → abort(403) jika bukan role kaprodi
```

Registrasi di `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware) {
    $middleware->alias([
        'mahasiswa' => EnsureMahasiswa::class,
        'admin'     => EnsureAdmin::class,
        'kaprodi'   => EnsureKaprodi::class,
    ]);
})
```

### Route Groups (`routes/web.php`)

```php
// Redirect root ke login atau dashboard sesuai status
Route::get('/', fn() => auth()->check()
    ? redirect()->route(auth()->user()->role . '.dashboard')
    : redirect()->route('login')
);

// Breeze routes (login, logout, password reset)
require __DIR__ . '/auth.php';

// Mahasiswa
Route::middleware(['auth', 'mahasiswa'])->prefix('mahasiswa')->name('mahasiswa.')->group(
    base_path('routes/mahasiswa.php')
);

// Admin
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(
    base_path('routes/admin.php')
);

// Kaprodi
Route::middleware(['auth', 'kaprodi'])->prefix('kaprodi')->name('kaprodi.')->group(
    base_path('routes/kaprodi.php')
);
```

---

## Login Flow

Breeze sudah menyediakan `LoginController` standar. Modifikasi yang diperlukan:

1. **After login redirect** — Override `redirectTo()` di `AuthenticatedSessionController`
   untuk mengarahkan ke dashboard sesuai role.
2. **Active check** — Tambahkan pengecekan `is_active` di
   `App\Http\Requests\Auth\LoginRequest::authenticate()`: jika user ditemukan
   tapi `is_active = false`, lempar `ValidationException` dengan pesan
   "Akun Anda telah dinonaktifkan."

---

## Manajemen Akun Mahasiswa

### Controller

```
app/Http/Controllers/Admin/
├── MahasiswaController.php    (CRUD mahasiswa + nonaktifkan)
└── MahasiswaImportController.php  (handle upload & proses Excel)
```

### Import Excel — Alur Proses

Gunakan `maatwebsite/excel` dengan class `MahasiswaImport`:

```
Upload file Excel
      │
      ▼
Validasi format (ekstensi xlsx/xls/csv, ukuran < 5 MB)
      │
      ▼
Foreach baris di sheet:
  ├─ NIM atau email sudah ada? → catat sebagai "dilewati (duplikat)"
  ├─ Data tidak valid (email bad format, NIM kosong)? → catat sebagai "gagal"
  └─ OK → buat User (role=mahasiswa, is_active=true, password=NIM)
            + buat Mahasiswa → catat sebagai "berhasil"
      │
      ▼
Kembalikan ringkasan { berhasil: N, dilewati: N, gagal: [{baris, alasan}] }
```

Password default saat import: NIM mahasiswa (hashed). Admin wajib
menginformasikan mahasiswa untuk mengganti password pertama kali login.

### Format Kolom Excel yang Diterima

| Kolom | Tipe | Wajib |
|---|---|---|
| NIM | string | Ya |
| Nama | string | Ya |
| Email | string (email valid) | Ya |
| Angkatan | tahun 4 digit | Ya |

---

## Manajemen Dosen

### Controller

```
app/Http/Controllers/Admin/DosenController.php
```

CRUD biasa (index, create, store, edit, update). Tidak ada delete — dosen yang
sudah ter-assign ke pengajuan tidak boleh dihapus; cukup edit data jika
diperlukan. Validasi NIP unik.

---

## Views Structure

```
resources/views/
├── auth/
│   ├── login.blade.php          (Breeze, dikustomisasi)
│   └── ...                      (reset password, dll — bawaan Breeze)
├── admin/
│   ├── mahasiswa/
│   │   ├── index.blade.php
│   │   ├── create.blade.php
│   │   ├── edit.blade.php
│   │   └── import.blade.php
│   └── dosen/
│       ├── index.blade.php
│       ├── create.blade.php
│       └── edit.blade.php
├── layouts/
│   ├── app.blade.php            (layout utama dengan sidebar per role)
│   └── guest.blade.php          (layout untuk halaman auth)
└── components/
    └── ...                      (komponen Blade reusable)
```

---

## Keamanan

- Route protection dilakukan di layer middleware HTTP, bukan hanya di UI.
- `is_active = false` diperiksa saat login DAN saat middleware berjalan
  (untuk sesi yang sudah aktif sebelum dinonaktifkan).
- Mass assignment diproteksi dengan `$fillable` pada setiap model.
- Import Excel tidak mengeksekusi formula/makro — hanya membaca nilai sel.
