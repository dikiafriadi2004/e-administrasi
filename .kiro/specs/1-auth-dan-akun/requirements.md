# Requirements — Milestone 1: Autentikasi & Manajemen Akun

## Overview

Fondasi aplikasi e-administrasi prodi: sistem login multi-role, proteksi route
berbasis role, dan manajemen akun mahasiswa oleh Admin. Tidak ada self-register
bebas — semua akun mahasiswa dibuat oleh Admin.

---

## Functional Requirements

### REQ-AUTH-001: Login
- **WHEN** pengguna mengakses halaman login dan mengirimkan email/NIM serta
  password yang valid,
  **THE SYSTEM SHALL** mengautentikasi pengguna dan mengarahkan ke dashboard
  sesuai role-nya (mahasiswa → `/mahasiswa/dashboard`, admin → `/admin/dashboard`,
  kaprodi → `/kaprodi/dashboard`).

- **WHEN** pengguna mengirimkan kredensial yang salah atau akun tidak aktif,
  **THE SYSTEM SHALL** menampilkan pesan error yang sesuai tanpa mengungkap
  informasi spesifik akun.

- **WHEN** pengguna sudah login mencoba mengakses halaman login,
  **THE SYSTEM SHALL** mengarahkan langsung ke dashboard role yang bersangkutan.

### REQ-AUTH-002: Logout
- **WHEN** pengguna menekan tombol logout,
  **THE SYSTEM SHALL** menghapus sesi aktif dan mengarahkan ke halaman login.

### REQ-AUTH-003: Reset Password
- **WHEN** pengguna meminta reset password via email terdaftar,
  **THE SYSTEM SHALL** mengirimkan link reset password ke email tersebut
  (menggunakan mekanisme bawaan Laravel Breeze).

### REQ-AUTH-004: Proteksi Route per Role
- **WHEN** pengguna yang sudah login mencoba mengakses route yang bukan milik
  role-nya (misalnya mahasiswa mengakses `/admin/*`),
  **THE SYSTEM SHALL** mengembalikan respons 403 Forbidden.

- **WHEN** pengguna yang belum login mencoba mengakses route yang dilindungi,
  **THE SYSTEM SHALL** mengarahkan ke halaman login.

- **IF** role pengguna tidak sesuai dengan gate yang dibutuhkan suatu route,
  **THEN** sistem tidak boleh hanya menyembunyikan menu di UI — akses pada level
  HTTP pun harus diblokir.

### REQ-AUTH-005: Status Akun Aktif
- **WHEN** akun mahasiswa dinonaktifkan oleh Admin,
  **THE SYSTEM SHALL** segera mencegah login dari akun tersebut, bahkan jika
  sesi sebelumnya masih ada.

---

## Manajemen Akun Mahasiswa (oleh Admin)

### REQ-AKUN-001: Tambah Akun Mahasiswa Manual
- **WHEN** Admin mengisi form tambah mahasiswa (NIM, nama, email, angkatan,
  password awal) dan menyimpannya,
  **THE SYSTEM SHALL** membuat akun user dengan role `mahasiswa` dan data
  profil terkait, lalu menampilkan konfirmasi berhasil.

- **IF** NIM atau email sudah terdaftar,
  **THEN** sistem harus menampilkan pesan validasi duplikat dan tidak membuat
  akun baru.

### REQ-AKUN-002: Edit Akun Mahasiswa
- **WHEN** Admin mengubah data mahasiswa (nama, email, angkatan) dan menyimpan,
  **THE SYSTEM SHALL** memperbarui data tanpa mengubah NIM dan tanpa mereset
  password kecuali Admin secara eksplisit mengisi field reset password.

### REQ-AKUN-003: Nonaktifkan/Aktifkan Akun Mahasiswa
- **WHEN** Admin menonaktifkan akun mahasiswa,
  **THE SYSTEM SHALL** menandai akun sebagai tidak aktif dan mencegah login
  berikutnya; data dan riwayat pengajuan mahasiswa tersebut tetap tersimpan.

- **WHEN** Admin mengaktifkan kembali akun yang dinonaktifkan,
  **THE SYSTEM SHALL** mengizinkan login kembali untuk akun tersebut.

### REQ-AKUN-004: Import Massal Mahasiswa dari Excel
- **WHEN** Admin mengupload file Excel dengan kolom (NIM, nama, email, angkatan)
  yang valid,
  **THE SYSTEM SHALL** memproses setiap baris, membuat akun yang belum ada,
  melewati baris yang NIM/email-nya sudah terdaftar (duplikat), dan menampilkan
  ringkasan hasil (berhasil dibuat, dilewati, gagal beserta alasannya).

- **IF** file Excel tidak sesuai format (kolom kurang/salah),
  **THEN** sistem harus menolak file dan menampilkan pesan error format.

- **IF** terdapat baris dengan data tidak valid (email tidak valid, NIM kosong),
  **THEN** baris tersebut dilewati dengan dicatat di ringkasan error; baris
  valid lainnya tetap diproses.

### REQ-AKUN-005: Kelola Data Dosen (oleh Admin)
- **WHEN** Admin menambah dosen (nama, NIP, kapasitas maksimal bimbingan opsional),
  **THE SYSTEM SHALL** menyimpan data dosen yang tersedia untuk dipilih sebagai
  pembimbing/penguji.

- **WHEN** Admin mengedit data dosen,
  **THE SYSTEM SHALL** memperbarui data tanpa mempengaruhi assignment yang
  sudah ada.

---

## Non-Functional Requirements

- **NFR-AUTH-001:** Semua password disimpan menggunakan bcrypt (bawaan Laravel).
- **NFR-AUTH-002:** Setiap route yang membutuhkan autentikasi harus menggunakan
  middleware `auth` Laravel; role check menggunakan Gate atau middleware kustom.
- **NFR-AUTH-003:** Login menggunakan email (bukan NIM) sebagai identifier
  utama di kolom `email` tabel `users`; NIM disimpan di tabel `mahasiswas`
  sebagai data profil.
- **NFR-AUTH-004:** File Excel untuk import harus divalidasi ukurannya (maks 5 MB)
  sebelum diproses.
