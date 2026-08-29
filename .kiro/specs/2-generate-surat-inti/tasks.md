# Tasks — Milestone 2: Generate Surat Inti

Urutan sekuensial. Milestone ini membuktikan satu jenis surat (Surat Aktif
Kuliah) jalan end-to-end sebelum form pengajuan dibangun.

Prasyarat: Milestone 1 selesai (tabel `users`, `mahasiswas`, `dosens` sudah ada).

---

## Fase 1: Install Dependency & Konfigurasi

- [ ] **TASK-1.1** Install PHPWord:
  `composer require phpoffice/phpword`.
  Verifikasi dengan `composer show phpoffice/phpword`.

- [ ] **TASK-1.2** Buat file konfigurasi surat:
  `php artisan make:config surat` — atau buat manual `config/surat.php`.
  Isi: `kode_institusi`, `kode_fakultas`, `kode_prodi`, `libreoffice_path`,
  `nama_kaprodi`, `nip_kaprodi`.

- [ ] **TASK-1.3** Tambahkan variabel ke `.env` dan `.env.example`:
  ```
  LIBREOFFICE_PATH="C:\Program Files\LibreOffice\program\soffice.exe"
  SURAT_KODE_INSTITUSI=UN-XX
  SURAT_KODE_FAKULTAS=FAK
  SURAT_KODE_PRODI=TI
  SURAT_NAMA_KAPRODI=
  SURAT_NIP_KAPRODI=
  ```

- [ ] **TASK-1.4** Verifikasi LibreOffice bisa dipanggil dari command line:
  jalankan `soffice --version` (atau path yang dikonfigurasi). Pastikan
  tidak ada error.

---

## Fase 2: Database — Tabel Baru

- [ ] **TASK-2.1** Buat migration tabel `pengajuan_surat`:
  `php artisan make:migration create_pengajuan_surat_table`
  Kolom: id, mahasiswa_id, jenis_surat (enum), pengajuan_judul_id (nullable),
  data_form (json), nomor_surat (varchar unique nullable), dosen_penguji_id
  (nullable), status (enum), catatan_penolakan (text nullable), file_docx,
  file_pdf, file_scan (semua varchar nullable), generated_at (timestamp
  nullable), created_at, updated_at.

- [ ] **TASK-2.2** Buat migration tabel `templates_surat`:
  `php artisan make:migration create_templates_surat_table`
  Kolom: id, jenis_surat (enum), path_file (varchar), versi (smallint),
  is_aktif (boolean default true), created_at, updated_at.

- [ ] **TASK-2.3** Buat migration tabel `nomor_surat_counters`:
  `php artisan make:migration create_nomor_surat_counters_table`
  Kolom: id, jenis_surat (enum), tahun (year), counter (int unsigned
  default 0). Unique index pada (jenis_surat, tahun).

- [ ] **TASK-2.4** Buat migration tabel `pengajuan_judul` (stub — diisi
  detail di Milestone 5, tapi foreign key dari `pengajuan_surat` butuh
  tabel ini ada):
  `php artisan make:migration create_pengajuan_judul_table`
  Kolom minimal: id, mahasiswa_id, judul, bidang_kajian, ringkasan (text),
  dosen_pembimbing_id (nullable), status (enum), catatan_penolakan (nullable),
  created_at, updated_at.

- [ ] **TASK-2.5** Jalankan `php artisan migrate` dan verifikasi semua tabel
  terbuat.

---

## Fase 3: Model & Service Classes

- [ ] **TASK-3.1** Buat model `PengajuanSurat`:
  `php artisan make:model PengajuanSurat`
  Isi `$fillable`, cast `data_form` ke `array`, relasi `belongsTo` ke
  `Mahasiswa`, `Dosen` (penguji), `PengajuanJudul`, `TemplateSurat`.

- [ ] **TASK-3.2** Buat model `TemplateSurat`:
  `php artisan make:model TemplateSurat`
  Isi `$fillable`, scope `aktif()` yang filter `is_aktif = true`.

- [ ] **TASK-3.3** Buat model `NomorSuratCounter`:
  `php artisan make:model NomorSuratCounter`
  Isi `$fillable`.

- [ ] **TASK-3.4** Buat model `PengajuanJudul`:
  `php artisan make:model PengajuanJudul`
  Isi `$fillable`, relasi ke `Mahasiswa` dan `Dosen`.

- [ ] **TASK-3.5** Buat exception class:
  `php artisan make:exception SuratGenerationException`

- [ ] **TASK-3.6** Buat `NomorSuratService`:
  `php artisan make:class Services/NomorSuratService`
  Implementasi method `generate(string $jenisSurat): string` dengan
  `DB::transaction` dan `lockForUpdate()` pada `NomorSuratCounter`.
  Format output: `001/UN-XX/FAK/TI/VIII/2026`.

- [ ] **TASK-3.7** Buat `SuratGeneratorService`:
  `php artisan make:class Services/SuratGeneratorService`
  Implementasi method:
  - `generate(PengajuanSurat $pengajuan): array` — alur lengkap (load template,
    isi placeholder, simpan docx, konversi pdf, update DB).
  - `convertToPdf(string $docxPath): string` — panggil LibreOffice headless.
  - `buildPlaceholders(PengajuanSurat $pengajuan): array` — kumpulkan semua
    nilai placeholder dari DB sesuai jenis surat.
  Inject `NomorSuratService` via constructor.

---

## Fase 4: Template Docx — Buat File Template

- [ ] **TASK-4.1** Buat folder `storage/app/private/templates/` jika belum ada.
  Tambahkan `.gitignore` untuk mengabaikan isi folder ini dari git (file
  template bisa besar dan berisi kop institusi).

- [ ] **TASK-4.2** Buat template `aktif_kuliah_v1.docx` di Microsoft Word
  sesuai panduan desain di `design.md`:
  - Kop surat (Header Word): logo + nama institusi/prodi/alamat
  - Body: paragraf surat keterangan aktif kuliah dengan placeholder
    `${nomor_surat}`, `${tanggal_surat}`, `${nama_mahasiswa}`, `${nim}`,
    `${angkatan}`, `${keperluan}`, `${tujuan_instansi}`
  - Area tanda tangan: nama dan NIP kaprodi (`${nama_kaprodi}`, `${nip_kaprodi}`)
  - Footer: alamat lengkap prodi

- [ ] **TASK-4.3** Buat template `seminar_proposal_v1.docx` dengan placeholder
  tambahan: `${judul_skripsi}`, `${bidang_kajian}`, `${nama_pembimbing}`,
  `${nip_pembimbing}`, `${tanggal_seminar}`.

- [ ] **TASK-4.4** Buat template `sidang_skripsi_v1.docx` dengan placeholder
  tambahan: `${nama_penguji}`, `${nip_penguji}`, `${tanggal_sidang}`,
  `${waktu_sidang}`, `${tempat_sidang}`.

- [ ] **TASK-4.5** Buat template `undangan_penguji_v1.docx` — versi surat
  yang ditujukan ke dosen penguji secara langsung.

- [ ] **TASK-4.6** Simpan semua template ke `storage/app/private/templates/`
  dan seed record-nya ke tabel `templates_surat` via seeder atau Artisan
  command.

---

## Fase 5: Artisan Command untuk Test Generate

- [ ] **TASK-5.1** Buat Artisan command:
  `php artisan make:command TestGenerateSurat`
  Signature: `surat:test-generate {jenis : aktif_kuliah|seminar_proposal|sidang_skripsi|undangan_penguji}`
  Command ini membuat `PengajuanSurat` dummy (tanpa menyimpan ke DB
  production) dan memanggil `SuratGeneratorService::generate()`, lalu
  mencetak path output ke console.

- [ ] **TASK-5.2** Jalankan `php artisan surat:test-generate aktif_kuliah`
  dan verifikasi:
  - File `.docx` terbuat di `storage/app/private/surat/test/`
  - File `.pdf` terbuat di folder yang sama
  - Buka kedua file secara manual, periksa semua placeholder terganti
  - Layout tidak pecah dengan data dummy panjang (nama 50 karakter)

---

## Fase 6: Controller Admin — Kelola Template & Generate

- [ ] **TASK-6.1** Buat `Admin\TemplateSuratController`:
  `php artisan make:controller Admin/TemplateSuratController`
  Method: `index` (daftar template per jenis), `upload` (form upload),
  `store` (simpan template baru + update is_aktif).

- [ ] **TASK-6.2** Buat `Admin\GenerateSuratController`:
  `php artisan make:controller Admin/GenerateSuratController`
  Method: `generate(PengajuanSurat $surat)` — trigger generate,
  `download(PengajuanSurat $surat, string $tipe)` — stream file ke browser
  dengan otorisasi.

- [ ] **TASK-6.3** Buat Policy `PengajuanSuratPolicy`:
  `php artisan make:policy PengajuanSuratPolicy --model=PengajuanSurat`
  Isi method `download` — mahasiswa hanya bisa download miliknya sendiri;
  admin dan kaprodi bisa download semua.

- [ ] **TASK-6.4** Buat views sederhana untuk admin:
  - `admin/template-surat/index.blade.php` — daftar template aktif per jenis
  - `admin/template-surat/upload.blade.php` — form upload template baru

- [ ] **TASK-6.5** Daftarkan route di `routes/admin.php`:
  - `GET  /admin/template-surat` → `TemplateSuratController@index`
  - `POST /admin/template-surat` → `TemplateSuratController@store`
  - `POST /admin/pengajuan/{surat}/generate` → `GenerateSuratController@generate`
  - `GET  /admin/pengajuan/{surat}/download/{tipe}` → `GenerateSuratController@download`

---

## Fase 7: Uji End-to-End Manual

- [ ] **TASK-7.1** Login sebagai Admin, buka halaman daftar template.
  Verifikasi keempat jenis surat terdaftar dengan path yang benar.

- [ ] **TASK-7.2** Buat record `PengajuanSurat` dummy via tinker untuk jenis
  `aktif_kuliah`, trigger generate via UI admin, download docx dan pdf,
  buka kedua file — pastikan tidak ada placeholder tersisa dan nomor surat
  terformat benar.

- [ ] **TASK-7.3** Trigger generate dua kali untuk pengajuan yang sama,
  verifikasi file lama tidak tertimpa (ada dua set file dengan UUID berbeda)
  dan DB menunjuk ke file terbaru.

- [ ] **TASK-7.4** Coba akses file storage langsung via URL browser (bypass
  controller) — harus mendapat 404, bukan file terbuka.

- [ ] **TASK-7.5** Matikan LibreOffice sementara (rename binary), trigger
  generate, verifikasi error tercatat di log dan UI menampilkan pesan
  error yang informatif (bukan stack trace mentah).

---

## Checklist Definition of Done

- [ ] Template keempat jenis surat tersimpan di server dan terdaftar di DB.
- [ ] Generate Surat Aktif Kuliah menghasilkan `.docx` dan `.pdf` valid.
- [ ] Tidak ada placeholder tersisa di dokumen hasil generate.
- [ ] Nomor surat berformat benar dan unik.
- [ ] File tidak bisa diakses langsung via URL publik.
- [ ] Error LibreOffice tercatat di log, tidak expose stack trace ke user.
- [ ] Re-generate tidak menimpa file lama.
