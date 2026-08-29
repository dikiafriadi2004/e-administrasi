# Tasks — Milestone 5: Alur Judul → Dosen → Proposal → Sidang

Prasyarat: Milestone 1–4 selesai.

---

## Fase 1: RasioDosenService

- [ ] **TASK-1.1** Buat `RasioDosenService`:
  `php artisan make:class Services/RasioDosenService`
  Implementasi `getDaftarDosenTerurut(string $konteks, ?int $excludeDosenId)`
  menggunakan `Dosen::withCount([...])` dengan constraint status aktif,
  diurutkan ascending berdasarkan jumlah bimbingan/pengujian lalu nama.

- [ ] **TASK-1.2** Tambahkan method `getRingkasanRasio()` di service yang
  sama — dipakai dashboard Milestone 6.

- [ ] **TASK-1.3** Tambahkan relasi `pengajuanJudul` dan `pengajuanSuratPenguji`
  ke model `Dosen` jika belum ada.

- [ ] **TASK-1.4** Uji query via tinker: pastikan urutan dosen benar dengan
  data seed, dan tidak ada N+1 query (gunakan `DB::getQueryLog()`).

---

## Fase 2: Penentuan Dosen Pembimbing (Kaprodi)

- [ ] **TASK-2.1** Buat `Kaprodi\TetapkanPembimbingController`:
  `php artisan make:controller Kaprodi/TetapkanPembimbingController`
  Method `store`:
  - Validasi pengajuan judul status `diverifikasi`
  - Validasi `dosen_id` ada di tabel dosens
  - Simpan `dosen_pembimbing_id`
  - Panggil `PengajuanStateService->setujuiJudul()`

- [ ] **TASK-2.2** Perbarui view `kaprodi/antrian/show-judul.blade.php`:
  - Tambahkan section "Pilih Dosen Pembimbing" yang muncul saat status
    pengajuan adalah `diverifikasi`
  - Tampilkan tabel dosen terurut (data dari `RasioDosenService`)
  - Kolom: nama, NIP, jumlah bimbingan aktif, kapasitas, status (badge Penuh)
  - Radio button / select untuk pilih satu dosen
  - Jika dosen kapasitas penuh: tampilkan pesan konfirmasi via Alpine.js
    sebelum submit
  - Tombol "Setujui Judul & Tetapkan Pembimbing"

- [ ] **TASK-2.3** Daftarkan route di `routes/kaprodi.php`.

- [ ] **TASK-2.4** Uji: buka detail pengajuan judul sebagai Kaprodi, pilih
  pembimbing, submit. Verifikasi `dosen_pembimbing_id` tersimpan,
  status berubah ke `disetujui`, history tercatat.

- [ ] **TASK-2.5** Uji: setelah judul disetujui, login sebagai mahasiswa
  yang bersangkutan → form seminar proposal harus terbuka (tidak terkunci
  lagi).

---

## Fase 3: Penentuan Dosen Penguji (Kaprodi)

- [ ] **TASK-3.1** Buat `Kaprodi\TetapkanPengujiController`:
  `php artisan make:controller Kaprodi/TetapkanPengujiController`
  Method `store`:
  - Validasi pengajuan surat jenis `sidang_skripsi` status `diverifikasi`
  - Validasi `dosen_id` berbeda dari `dosen_pembimbing_id` di
    `pengajuan_judul` terkait
  - Simpan `dosen_penguji_id`
  - Panggil `PengajuanStateService->terimaSurat()`

- [ ] **TASK-3.2** Buat view `kaprodi/antrian/show-surat-sidang.blade.php`
  (versi khusus sidang skripsi):
  - Tampilkan data judul, pembimbing (read-only dari pengajuan_judul)
  - Section pilih dosen penguji dengan tabel terurut dari RasioDosenService
  - Dosen pembimbing otomatis di-exclude dari pilihan
  - Kolom: nama, NIP, jumlah pengujian aktif, status penuh

- [ ] **TASK-3.3** Daftarkan route di `routes/kaprodi.php`.

- [ ] **TASK-3.4** Uji: coba pilih dosen pembimbing yang sama sebagai
  penguji → harus error validasi. Pilih dosen berbeda → berhasil.

---

## Fase 4: Kuatkan Guard Alur Bertahap

- [ ] **TASK-4.1** Pastikan `Mahasiswa\PengajuanSuratController@createSeminar`
  memiliki guard yang merespons dengan 403 (bukan hanya view terkunci)
  jika request adalah POST langsung tanpa status judul `disetujui`.

- [ ] **TASK-4.2** Tambahkan guard serupa di `@createSidang` untuk cek
  seminar proposal status `selesai`.

- [ ] **TASK-4.3** Tambahkan guard "satu pengajuan aktif" di `store` method
  untuk setiap jenis pengajuan — cegah duplikasi via POST manipulation.

- [ ] **TASK-4.4** Uji bypass: gunakan Postman atau curl untuk POST ke
  endpoint store pengajuan seminar tanpa judul disetujui → harus dapat 403.

---

## Fase 5: Uji Alur Lengkap End-to-End

- [ ] **TASK-5.1** Uji alur judul → pembimbing → seminar → sidang → penguji
  secara berurutan dengan akun mahasiswa + admin + kaprodi. Verifikasi
  setiap status transition benar dan history tercatat.

- [ ] **TASK-5.2** Uji rasio dosen: buat beberapa pengajuan judul dengan
  pembimbing yang sama, buka halaman tetapkan pembimbing untuk pengajuan
  lain → dosen tersebut harus muncul dengan jumlah bimbingan yang benar
  di urutan lebih bawah.

- [ ] **TASK-5.3** Uji kapasitas penuh: set `kapasitas_maksimal = 1` untuk
  satu dosen via tinker, buat 1 bimbingan aktif, buka halaman pilih
  pembimbing → dosen tersebut harus tampil dengan badge "Penuh".

- [ ] **TASK-5.4** Uji race condition sederhana: buat dua tab browser dan
  submit penentuan pembimbing berbeda hampir bersamaan → pastikan tidak ada
  duplikasi atau data corrupt.

---

## Checklist Definition of Done

- [ ] Daftar dosen di halaman penentuan pembimbing/penguji terurut dari
      beban terkecil.
- [ ] Pembimbing tersimpan dan status judul berubah ke `disetujui`.
- [ ] Penguji tidak bisa sama dengan pembimbing pada sidang yang sama.
- [ ] Guard alur bertahap bekerja di level controller (bukan hanya UI).
- [ ] Setelah judul disetujui, form seminar terbuka; setelah seminar selesai,
      form sidang terbuka.
- [ ] RasioDosenService tidak menghasilkan N+1 query.
