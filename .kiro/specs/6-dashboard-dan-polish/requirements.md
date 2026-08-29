# Requirements — Milestone 6: Dashboard & Polish

## Overview

Milestone terakhir memoles aplikasi menjadi production-ready: dashboard
rasio dosen yang informatif, arsip riwayat semua surat untuk Admin/Kaprodi,
import massal mahasiswa dari Excel, dan hardening keseluruhan aplikasi
(security, edge case, UX).

---

## Functional Requirements

### REQ-DASH-001: Dashboard Rasio Dosen
- **WHEN** Admin atau Kaprodi membuka halaman dashboard rasio,
  **THE SYSTEM SHALL** menampilkan tabel yang memuat semua dosen aktif
  beserta jumlah bimbingan aktif dan jumlah penugasan penguji aktif
  masing-masing, dihitung secara real-time.

- **THE SYSTEM SHALL** menampilkan kolom: Nama Dosen, NIP, Jumlah
  Bimbingan Aktif, Jumlah Jadi Penguji Aktif, Kapasitas Maks, Status
  Kapasitas (Tersedia/Penuh).

- **WHEN** Admin melihat dashboard rasio,
  **THE SYSTEM SHALL** menampilkan data read-only tanpa tombol aksi.

- **WHEN** Kaprodi melihat dashboard rasio,
  **THE SYSTEM SHALL** menampilkan data yang sama dengan konteks penggunaan
  aktif (sebagai alat bantu keputusan saat menentukan pembimbing/penguji).

### REQ-DASH-002: Statistik Ringkasan di Dashboard Utama
- **WHEN** Admin membuka dashboard utama,
  **THE SYSTEM SHALL** menampilkan kartu statistik: total pengajuan masuk
  hari ini, total pengajuan menunggu verifikasi, total surat aktif bulan
  ini, dan total mahasiswa aktif.

- **WHEN** Kaprodi membuka dashboard utama,
  **THE SYSTEM SHALL** menampilkan kartu statistik: total pengajuan di
  antrian, total judul disetujui bulan ini, dan ringkasan rasio dosen
  (siapa yang paling tersedia).

- **WHEN** Mahasiswa membuka dashboard,
  **THE SYSTEM SHALL** menampilkan status pengajuan aktif miliknya
  (judul dan surat yang sedang dalam proses).

### REQ-ARSIP-001: Arsip / Riwayat Semua Surat
- **WHEN** Admin membuka halaman arsip surat,
  **THE SYSTEM SHALL** menampilkan semua `pengajuan_surat` dari seluruh
  mahasiswa yang pernah dibuat sistem, dapat difilter berdasarkan:
  jenis surat, status, rentang tanggal, dan nama/NIM mahasiswa.

- **WHEN** Admin mencari dengan filter tertentu,
  **THE SYSTEM SHALL** menampilkan hasil yang relevan dengan paginasi
  (20 item per halaman).

- **THE SYSTEM SHALL** menyediakan link download dokumen (docx, pdf, scan)
  dari halaman arsip untuk Admin.

### REQ-IMPORT-001: Import Massal Mahasiswa dari Excel
- **WHEN** Admin mengupload file Excel dengan kolom (NIM, Nama, Email,
  Angkatan) yang valid,
  **THE SYSTEM SHALL** memproses setiap baris: membuat akun User +
  Mahasiswa untuk baris valid yang belum terdaftar.

- **WHEN** ditemukan baris dengan NIM atau Email yang sudah ada di database,
  **THE SYSTEM SHALL** melewati baris tersebut dan mencatatnya sebagai
  "dilewati (duplikat)" tanpa menghentikan proses baris lain.

- **WHEN** ditemukan baris dengan data tidak valid (format email salah,
  NIM kosong, angkatan bukan 4 digit),
  **THE SYSTEM SHALL** melewati baris tersebut dan mencatatnya sebagai
  "gagal" dengan alasan spesifik.

- **WHEN** proses selesai,
  **THE SYSTEM SHALL** menampilkan ringkasan: jumlah berhasil dibuat,
  jumlah dilewati, jumlah gagal beserta daftar baris yang gagal dan
  alasannya.

- **IF** file bukan format Excel (.xlsx, .xls) atau ukuran melebihi 5 MB,
  **THEN** sistem menolak dan menampilkan pesan error sebelum diproses.

### REQ-POLISH-001: Validasi & Error Handling Menyeluruh
- **THE SYSTEM SHALL** menampilkan pesan error yang informatif (bukan
  stack trace) untuk semua kondisi error yang bisa terjadi di production:
  LibreOffice gagal, file tidak ditemukan, database error.

- **THE SYSTEM SHALL** menggunakan Laravel's exception handler untuk
  menangkap dan memformat error secara konsisten.

### REQ-POLISH-002: Notifikasi Flash Message
- **THE SYSTEM SHALL** menampilkan flash message sukses (hijau) atau
  error (merah) setelah setiap aksi penting (submit form, verifikasi,
  upload, dll) menggunakan session flash.

### REQ-POLISH-003: Responsive UI
- **THE SYSTEM SHALL** menampilkan semua halaman dengan layout yang
  dapat digunakan di layar mobile (min-width 375px) dan desktop.

---

## Non-Functional Requirements

- **NFR-DASH-001:** Halaman dashboard dengan query rasio harus di-cache
  selama 60 detik untuk menghindari query berat berulang.
- **NFR-IMPORT-001:** Proses import Excel dengan 500 baris harus selesai
  dalam < 30 detik; gunakan chunk processing jika diperlukan.
- **NFR-POLISH-001:** Semua halaman harus lolos PHP `vendor/bin/pint`
  dan tidak ada error di `php artisan route:list`.
- **NFR-POLISH-002:** Tidak ada `dd()`, `dump()`, atau `var_dump()` yang
  tertinggal di kode production.
