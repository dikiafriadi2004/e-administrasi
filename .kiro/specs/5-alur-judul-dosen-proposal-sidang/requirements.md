# Requirements — Milestone 5: Alur Judul → Dosen → Proposal → Sidang

## Overview

Milestone ini melengkapi alur akademik utama: penentuan dosen pembimbing
dan penguji oleh Kaprodi berbasis data rasio bimbingan real-time, serta
mengunci alur pengajuan mahasiswa agar mengikuti urutan yang benar
(judul disetujui → seminar proposal → sidang skripsi).

---

## Functional Requirements

### REQ-RASIO-001: Kalkulasi Rasio Dosen
- **WHEN** sistem perlu menampilkan daftar dosen untuk penentuan pembimbing
  atau penguji,
  **THE SYSTEM SHALL** menghitung jumlah bimbingan aktif (pengajuan judul
  dengan status bukan `ditolak` yang memiliki `dosen_pembimbing_id`) dan
  jumlah penugasan penguji aktif (pengajuan surat sidang skripsi dengan
  status bukan `ditolak` yang memiliki `dosen_penguji_id`) untuk setiap
  dosen secara real-time dari database.

- **THE SYSTEM SHALL NOT** menyimpan rasio sebagai kolom di tabel dosen —
  harus selalu dihitung saat dibutuhkan agar akurat.

### REQ-RASIO-002: Urutan Otomatis Dosen
- **WHEN** Kaprodi membuka layar penentuan dosen pembimbing atau penguji,
  **THE SYSTEM SHALL** menampilkan daftar dosen diurutkan dari yang paling
  sedikit beban (jumlah bimbingan/penugasan terkecil terlebih dahulu).

- **IF** dua dosen memiliki jumlah beban yang sama,
  **THEN** diurutkan berdasarkan nama secara alfabetis.

### REQ-RASIO-003: Kapasitas Maksimal Dosen
- **IF** dosen memiliki `kapasitas_maksimal` yang diset (tidak null) dan
  jumlah bimbingan aktifnya sudah mencapai kapasitas tersebut,
  **THEN** dosen tersebut ditampilkan dengan tanda visual "Penuh" di
  daftar pilihan, tetapi Kaprodi tetap bisa memilihnya sebagai override
  (dengan konfirmasi tambahan).

### REQ-PEMBIMBING-001: Penentuan Dosen Pembimbing
- **WHEN** Kaprodi membuka detail pengajuan judul yang sudah `diverifikasi`
  dan memilih seorang dosen sebagai pembimbing lalu mengkonfirmasi,
  **THE SYSTEM SHALL** menyimpan `dosen_pembimbing_id` ke record
  `pengajuan_judul`, mengubah status menjadi `disetujui`, dan mencatat
  perubahan di `status_histories`.

- **THE SYSTEM SHALL** mewajibkan pemilihan dosen pembimbing sebagai
  bagian dari proses persetujuan judul — tidak bisa setujui judul tanpa
  memilih pembimbing.

### REQ-PEMBIMBING-002: Penentuan Dosen Penguji
- **WHEN** Kaprodi membuka detail pengajuan sidang skripsi yang sudah
  `diverifikasi` dan memilih seorang dosen sebagai penguji lalu
  mengkonfirmasi,
  **THE SYSTEM SHALL** menyimpan `dosen_penguji_id` ke record
  `pengajuan_surat` sidang skripsi dan mengubah status menjadi
  `menunggu_ttd`.

- **THE SYSTEM SHALL** mencegah pemilihan dosen pembimbing yang sama
  sebagai penguji pada sidang skripsi yang bersangkutan.

### REQ-ALUR-001: Lock Pengajuan Bertahap
- **WHEN** mahasiswa mencoba mengakses form seminar proposal sebelum
  pengajuan judulnya berstatus `disetujui`,
  **THE SYSTEM SHALL** menampilkan halaman terkunci (diimplementasikan
  di M3, dikuatkan di sini dengan guard di level controller/route).

- **WHEN** mahasiswa mencoba mengakses form sidang skripsi sebelum
  pengajuan surat seminar proposalnya berstatus `selesai`,
  **THE SYSTEM SHALL** menampilkan halaman terkunci.

- **IF** mahasiswa mencoba bypass guard via direct URL atau manipulasi
  POST request,
  **THE SYSTEM SHALL** mengembalikan 403 Forbidden.

### REQ-ALUR-002: Data Auto-Terisi Konsisten
- **WHEN** mahasiswa membuka form seminar proposal,
  **THE SYSTEM SHALL** mengambil judul dan nama dosen pembimbing dari
  `pengajuan_judul` yang disetujui — tidak perlu input ulang dan tidak
  bisa diubah dari form.

- **WHEN** mahasiswa membuka form sidang skripsi,
  **THE SYSTEM SHALL** mengambil judul dan dosen pembimbing dari
  `pengajuan_judul` yang sama.

### REQ-ALUR-003: Satu Pengajuan Aktif per Tahap
- **THE SYSTEM SHALL** memastikan hanya ada satu pengajuan aktif per
  tahap per mahasiswa:
  - Maks 1 `pengajuan_judul` aktif (status bukan `ditolak`)
  - Maks 1 `pengajuan_surat` aktif per jenis surat (status bukan `ditolak`)

---

## Non-Functional Requirements

- **NFR-RASIO-001:** Query rasio dosen harus menggunakan `withCount` atau
  subquery yang efisien — tidak boleh N+1 query di halaman daftar dosen.
- **NFR-RASIO-002:** Halaman penentuan dosen harus load dalam < 2 detik
  bahkan dengan 50+ dosen di database.
- **NFR-ALUR-001:** Guard "satu pengajuan aktif" diterapkan di level
  database (unique constraint atau query check) dan di layer controller,
  bukan hanya di UI.
