# Laporan Perbaikan Bug — E-Administrasi Prodi

**Dikerjakan oleh:** Agent perbaikan  
**Tanggal:** Iterasi pertama  
**Referensi:** `plan-perbaikan.md`

---

## Ringkasan Perubahan

### BUG-01 (KRITIS): `use ErrorException;` hilang di bootstrap/app.php
- **File:** `bootstrap/app.php`
- **Bug:** Kelas `ErrorException` dipakai di closure exception handler (baris 47) tanpa import. PHP akan throw `Error: Class "ErrorException" not found` saat handler tersebut dipanggil, yang justru crash app alih-alih menangani error.
- **Perubahan:** Ditambahkan `use ErrorException;` di blok use statements (setelah `use App\Http\Middleware\EnsureMahasiswa;`).

---

### INC-05: Route conflict `mahasiswa/import` vs `Route::resource`
- **File:** `routes/admin.php`
- **Bug:** `Route::resource('mahasiswa', ...)` didefinisikan sebelum route `mahasiswa/import`, sehingga Laravel mencocokkan `/admin/mahasiswa/import` sebagai `{mahasiswa}` = "import" (model binding), menghasilkan 404.
- **Perubahan:** Blok tiga route `mahasiswa/import` (GET create, POST store, GET template) dipindahkan ke ATAS `Route::resource(...)`.

---

### BUG-07: `static $no = 0` di ArsipSuratExport
- **File:** `app/Exports/ArsipSuratExport.php`
- **Bug:** Variabel `static $no` dalam method `map()` bertahan lintas-instance selama proses hidup. Export dua kali berturut-turut akan menghasilkan nomor urut yang salah (misal: 11, 12, 13 bukan 1, 2, 3).
- **Perubahan:** Ditambahkan instance property `private int $no = 0;` di level class. Di dalam `map()`, diganti `static $no = 0; $no++` → `$this->no++`, dan return value `$no` → `$this->no`.

---

### BUG-03: N+1 query statistik di Kaprodi/ArsipSuratController
- **File:** `app/Http/Controllers/Kaprodi/ArsipSuratController.php`
- **Bug:** 4 query `COUNT()` terpisah untuk menghitung statistik status surat — 4 round-trip ke database untuk data yang bisa diambil sekaligus.
- **Perubahan:** Diganti dengan 1 query `selectRaw('status, COUNT(*) as total')->groupBy('status')->pluck('total', 'status')`, lalu di-map ke array format yang sama.

---

### BUG-02: Race condition pada generate surat
- **File:** `app/Http/Controllers/Admin/GenerateSuratController.php`
- **Bug:** Tidak ada pengecekan apakah surat sudah pernah digenerate. Double-click atau retry browser bisa membuat dua file DOCX berbeda dan meng-insert history status ganda.
- **Perubahan:** Ditambahkan guard check setelah `$surat->refresh()`: jika `$surat->file_docx` sudah ada dan filenya exist di storage, return early dengan flash `warning` tanpa generate ulang.

---

### BUG-04: Double-escape di TestGenerateSurat command
- **File:** `app/Console/Commands/TestGenerateSurat.php`
- **Bug:** `htmlspecialchars((string) $value)` dipanggil sebelum `$processor->setValue()`. PHPWord sudah escape XML secara internal, sehingga `&` menjadi `&amp;amp;` di output DOCX.
- **Perubahan:** Dihapus `htmlspecialchars()` — sekarang hanya `(string) $value` yang diteruskan.

---

### SEC-06: Tambah validasi MIME magic bytes untuk upload PDF
- **Files yang diubah:**
  - `app/Http/Controllers/Admin/GenerateSuratController.php` — `file_scan`
  - `app/Http/Controllers/Admin/JadwalController.php` — `file_undangan`
  - `app/Http/Controllers/Kaprodi/UploadScanController.php` — `file_scan`
  - `app/Http/Controllers/Mahasiswa/PengajuanSuratController.php` — `fileCoverProposal`
- **Bug:** `mimes:pdf` hanya mengecek ekstensi file, bukan isi sebenarnya. File apapun yang di-rename menjadi `.pdf` bisa lolos.
- **Perubahan:** Ditambahkan `mimetypes:application/pdf` berdampingan dengan `mimes:pdf` pada setiap rule validasi yang hanya menerima PDF murni. Upload yang menerima kombinasi (`mimes:pdf,jpg,jpeg,png`) tidak diubah.

---

### SEC-01 + SEC-02: Hapus sisa Breeze yang tidak dipakai
- **Files yang dihapus:**
  - `app/Http/Controllers/ProfileController.php`
  - `app/Http/Controllers/Auth/RegisteredUserController.php`
  - `app/Http/Requests/ProfileUpdateRequest.php`
  - `resources/views/profile/edit.blade.php`
  - `resources/views/profile/partials/delete-user-form.blade.php`
  - `resources/views/profile/partials/update-password-form.blade.php`
  - `resources/views/profile/partials/update-profile-information-form.blade.php`
  - `resources/views/auth/register.blade.php`
- **Verifikasi:** Tidak ada satu pun route di `routes/web.php`, `routes/auth.php`, `routes/admin.php`, `routes/kaprodi.php`, atau `routes/mahasiswa.php` yang mereferensikan controller/view ini.

---

### INC-04: Hapus dead views dari Laravel skeleton
- **Files yang dihapus:**
  - `resources/views/welcome.blade.php`
  - `resources/views/dashboard.blade.php`
- **Verifikasi:** Route `/` di `routes/web.php` hanya redirect ke role dashboard — tidak pernah merender view `welcome`. Tidak ada route yang merender `dashboard` view.

---

### INC-01: Hapus dead views antrian lama
- **Files yang dihapus:**
  - `resources/views/admin/antrian/index.blade.php`
  - `resources/views/admin/antrian/show-judul.blade.php`
  - `resources/views/admin/antrian/show-surat.blade.php`
- **Verifikasi:** Grep ke semua Admin controller tidak menemukan referensi ke view `admin.antrian.index`, `admin.antrian.show-judul`, atau `admin.antrian.show-surat`. Subdirektori `resources/views/admin/antrian/judul/` TIDAK dihapus — masih dipakai oleh `AntrianJudulController`.

---

## Verifikasi Akhir

- `php -l bootstrap/app.php` → No syntax errors ✅
- `php -l app/Http/Controllers/Admin/GenerateSuratController.php` → No syntax errors ✅
- `php -l app/Http/Controllers/Kaprodi/ArsipSuratController.php` → No syntax errors ✅
- `php -l app/Exports/ArsipSuratExport.php` → No syntax errors ✅
- `php artisan route:list` → No errors, `admin/mahasiswa/import` muncul sebelum `admin/mahasiswa/{mahasiswa}` ✅
- `vendor/bin/pint --dirty` → Format difix di `JadwalController.php`, `ArsipSuratController.php`, `routes/admin.php` ✅

---

## Catatan

- `use ErrorException;` di `bootstrap/app.php` menghasilkan PHP lint warning "non-compound name has no effect" karena `ErrorException` adalah built-in PHP class yang selalu tersedia. Warning ini tidak mempengaruhi fungsionalitas — Laravel tetap bisa menangkap exception tersebut dengan benar.
- Fitur `keluar_prodi` tidak dimasukkan ke perbaikan ini (bukan bug, melainkan fitur baru yang belum diimplementasi).
