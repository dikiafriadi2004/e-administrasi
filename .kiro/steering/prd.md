# PRD — Aplikasi E-Administrasi Prodi

**Versi:** 1.0
**Dikerjakan oleh:** 1 orang (self-project, proyek TA/skripsi)
**Target durasi:** ± 9–10 minggu (part-time)

---

## 1. Latar Belakang & Tujuan

Program studi masih menangani administrasi surat mahasiswa (surat aktif kuliah, pengajuan seminar proposal, pengajuan sidang skripsi) secara manual: mahasiswa datang ke admin, isi form kertas, tunggu ditandatangani, ambil hardcopy. Masalah utama yang ingin diselesaikan:

- Mahasiswa kesulitan kalau hardcopy surat hilang sebelum atau sesudah ditandatangani — harus urus ulang dari awal.
- Kaprodi tidak punya gambaran cepat soal beban bimbingan/pengujian tiap dosen saat harus menentukan pembimbing/penguji baru → potensi beban tidak merata.
- Tidak ada riwayat status pengajuan yang bisa dicek mahasiswa sendiri.

**Tujuan produk:** aplikasi web yang menangani seluruh siklus surat mahasiswa (dari pengajuan sampai dokumen final tersedia untuk didownload ulang kapan saja), dengan alur keputusan dosen pembimbing/penguji yang dibantu data rasio bimbingan real-time.

---

## 2. Tech Stack

| Layer | Teknologi |
|---|---|
| Framework | Laravel 12 |
| Auth | Laravel Breeze |
| Interaktivitas | Livewire (form dengan preview live, komponen reactive) |
| Styling | TailwindCSS |
| Database | MySQL |
| Generate dokumen | PHPWord (`TemplateProcessor`) untuk isi template docx → convert ke PDF |
| Convert docx→pdf | LibreOffice headless (`soffice --headless --convert-to pdf`) dijalankan dari server — hindari dependency ke layanan cloud berbayar |

---

## 3. Role & Hak Akses

| Role | Cara akun dibuat | Akses |
|---|---|---|
| **Mahasiswa** | Dibuat oleh Admin (input manual / import Excel per NIM). Tidak ada self-register bebas. | Menu mahasiswa saja — ajukan judul, ajukan surat, lihat riwayat & status miliknya sendiri, download dokumen miliknya sendiri. |
| **Admin** | Dibuat manual saat setup awal oleh developer. | Kelola akun mahasiswa & dosen, verifikasi pengajuan, buat surat langsung, upload hasil scan, lihat dashboard rasio (read-only), riwayat semua surat. |
| **Kepala Prodi** | Dibuat manual saat setup awal oleh developer. | Antrian pengajuan, tentukan dosen pembimbing/penguji, terima/tolak pengajuan, dashboard rasio (aktif dipakai untuk keputusan), opsional upload hasil scan. |

Setiap route dicek lewat Middleware/Gate per role — mahasiswa tidak boleh bisa mengakses data mahasiswa lain atau menu role lain, bahkan lewat manipulasi URL langsung.

---

## 4. Alur Utama (Core Flow)

Ini alur paling penting yang menyambungkan beberapa modul — harus jadi acuan desain database & state machine:

```
Mahasiswa ajukan judul skripsi
        │
        ▼
Admin verifikasi kelengkapan data
        │
        ▼
Kaprodi tentukan Dosen Pembimbing
(sistem urutkan daftar dosen dari rasio bimbingan
 paling sedikit → paling banyak, kaprodi bisa override manual)
        │
        ▼
Status: "Judul Disetujui — Dosen Ditentukan"
        │
        ▼
Mahasiswa ajukan Seminar Proposal
(judul & dosen pembimbing auto-terisi dari tahap sebelumnya,
 mahasiswa isi tanggal rencana + upload dokumen pendukung)
        │
        ▼
Admin verifikasi → Kaprodi terima/tolak
        │
        ▼ (jika diterima & seminar proposal selesai)
Mahasiswa ajukan Sidang Skripsi
(judul & pembimbing auto-terisi, upload dokumen pendukung sidang)
        │
        ▼
Kaprodi tentukan Dosen Penguji
(urutan sama: berdasarkan rasio menguji paling sedikit)
        │
        ▼
Kaprodi/Admin generate surat undangan sidang → cetak → TTD manual → scan → upload
        │
        ▼
Mahasiswa download hasil scan surat kapan saja, tanpa batas waktu
```

Untuk surat yang tidak berkaitan dengan judul (Surat Aktif Kuliah), alurnya lebih pendek: **Ajukan → Verifikasi Admin → TTD Kaprodi → Upload Scan → Selesai**, tanpa melewati tahap dosen pembimbing/penguji.

---

## 5. Daftar Fitur per Modul

### 5.1 Modul Autentikasi & Akun
- [ ] Login email/NIM + password, 3 role
- [ ] Middleware/Gate per role di setiap route
- [ ] Kelola akun mahasiswa oleh Admin (tambah/edit/nonaktifkan per NIM)
- [ ] Import massal akun mahasiswa dari Excel (dengan validasi duplikat NIM)
- [ ] Reset password standar (bawaan Breeze)

### 5.2 Modul Mahasiswa
- [ ] Ajukan judul skripsi (judul, bidang kajian, ringkasan singkat, upload dokumen pendukung awal)
- [ ] Ajukan Surat Aktif Kuliah (keperluan surat, tujuan instansi)
- [ ] Ajukan Seminar Proposal (data judul & pembimbing auto-terisi, tanggal rencana, upload dokumen pendukung) — terkunci sampai judul disetujui
- [ ] Ajukan Sidang Skripsi (data auto-terisi, checklist upload dokumen pendukung sidang)
- [ ] Preview live (Livewire) di tiap form pengajuan — ringkasan data, bukan replika kop surat
- [ ] Riwayat & status semua pengajuan milik sendiri (data mahasiswa lain tidak terlihat)
- [ ] Download surat versi belum TTD (docx & pdf) — bisa berkali-kali selama status belum ditolak
- [ ] Download hasil scan surat sudah TTD — tersedia otomatis begitu admin/kaprodi upload, tanpa batas waktu

### 5.3 Modul Admin
- [ ] Kelola akun mahasiswa (manual + import Excel)
- [ ] Kelola data dosen (termasuk kapasitas maksimal bimbingan, opsional)
- [ ] Verifikasi kelengkapan pengajuan sebelum diteruskan ke Kaprodi
- [ ] Buat surat langsung tanpa pengajuan mahasiswa (misal surat undangan dosen penguji)
- [ ] Upload hasil scan surat yang sudah ditandatangani → status otomatis berubah
- [ ] Dashboard rasio dosen (read-only)
- [ ] Riwayat semua surat yang pernah dibuat sistem (arsip/laporan)

### 5.4 Modul Kepala Prodi
- [ ] Antrian pengajuan yang sudah diverifikasi admin
- [ ] Penentuan dosen pembimbing (setelah pengajuan judul) & penguji (sebelum sidang) — daftar dosen terurut otomatis dari rasio paling sedikit, tetap bisa override manual
- [ ] Terima/tolak pengajuan dengan catatan alasan (tampil di riwayat mahasiswa)
- [ ] Dashboard rasio dosen (bimbingan & menguji, terpisah)
- [ ] Upload hasil scan (opsional, bisa didelegasikan ke admin)

### 5.5 Modul Surat & Generate Dokumen
- [ ] Template docx per jenis surat, tersimpan di server, dengan placeholder (`${nama_mahasiswa}`, `${nim}`, dst) dan kop surat resmi built-in
- [ ] Generate: ambil template → isi placeholder dari database (PHPWord `TemplateProcessor`) → simpan `.docx` → convert `.pdf`
- [ ] Tiap pengajuan menyimpan 3 file terpisah (tidak saling menimpa): `file_docx`, `file_pdf` (belum TTD), `file_scan` (sudah TTD, diisi belakangan)

### 5.6 Modul Rasio Dosen
- [ ] Hitung otomatis dari pengajuan berstatus aktif (bukan yang ditolak) per `dosen_pembimbing_id` / `dosen_penguji_id`
- [ ] Tabel: Nama Dosen | Jumlah Bimbingan | Jumlah Jadi Penguji
- [ ] Dipakai sebagai basis pengurutan otomatis di layar penentuan dosen (Kaprodi)

---

## 6. Kebutuhan Desain Template Surat — Harus Powerful, Rapi, dan Profesional

Template docx bukan sekadar "isi placeholder", tapi jadi wajah resmi prodi ke instansi luar. Kebutuhan desainnya:

- **Kop surat resmi konsisten** — logo institusi, nama prodi/fakultas/universitas, alamat, kontak, di posisi & ukuran yang sama persis di semua jenis surat.
- **Tipografi formal & konsisten** — satu font family untuk seluruh dokumen (mis. Times New Roman 12pt atau sesuai identitas kampus), heading dan body jelas terbedakan lewat bold/size, bukan warna-warni.
- **Layout rapi dengan spacing konsisten** — margin standar surat resmi (biasanya 2.5–3 cm tiap sisi), line spacing 1.15–1.5, alignment justify untuk paragraf isi.
- **Nomor surat otomatis** — format baku sesuai standar penomoran surat institusi (mis. `001/UN-XX/FAK/PRODI/VIII/2026`), digenerate otomatis, tidak diinput manual per surat.
- **Tabel & checklist rapi** — untuk surat yang butuh daftar (kelengkapan sidang, dsb), tabel dengan border tipis dan padding cukup, bukan mentah dari copy-paste.
- **Ruang tanda tangan yang jelas** — area kosong dengan nama & NIP di bawahnya, format sudah siap cetak-TTD-scan tanpa perlu edit ulang layout.
- **Footer resmi** — alamat lengkap, nomor telepon/email prodi, di semua halaman.
- **Modular per jenis surat** — satu base style (kop, footer, font) dipakai ulang, tapi tiap jenis surat (Aktif Kuliah, Seminar Proposal, Sidang Skripsi, Undangan Penguji) punya body paragraf sendiri sesuai kebutuhan.
- **Template harus tetap bisa diedit Admin/Kaprodi lewat Word biasa** — placeholder pakai sintaks PHPWord standar (`${...}`), tidak butuh developer untuk revisi kecil (ganti kalimat, ganti logo, dsb) di kemudian hari.
- **Uji render**: sebelum dipakai produksi, tiap template dicoba generate dengan data dummy terpanjang yang mungkin (nama panjang, judul skripsi 2 baris) supaya layout tidak pecah/overflow.

---

## 7. Skema Database (Ringkasan Entitas)

- `users` — id, nim/nip, nama, email, password, role
- `mahasiswas` — user_id, nim, angkatan, status_aktif
- `dosens` — id, nama, nip, kapasitas_maksimal (nullable)
- `pengajuan_judul` — mahasiswa_id, judul, bidang_kajian, ringkasan, dosen_pembimbing_id (nullable sampai ditentukan), status, catatan_penolakan
- `pengajuan_surat` — mahasiswa_id, jenis_surat (aktif_kuliah/seminar_proposal/sidang_skripsi), pengajuan_judul_id (nullable, terisi kalau terkait judul), data_form (json), dosen_penguji_id (nullable), status, file_docx, file_pdf, file_scan, catatan_penolakan, created_at
- `templates_surat` — jenis_surat, path_file_template, versi
- `rasio_dosen` (view/query, bukan tabel fisik) — dihitung dari `pengajuan_judul` + `pengajuan_surat` yang aktif

Status pengajuan (baik judul maupun surat) mengikuti state machine:
`Diajukan → Diverifikasi Admin → Menunggu TTD Kaprodi → Sudah Ditandatangani → Selesai`, atau `Ditolak` (dengan alasan) di titik mana pun sebelum selesai.

---

## 8. Roadmap / Milestone

| Milestone | Minggu | Output |
|---|---|---|
| 1. Fondasi | 1–2 | Laravel + Breeze + 3 role + middleware, kelola akun mahasiswa manual |
| 2. Inti generate surat | 3–4 | Template docx + PHPWord + convert PDF, 1 jenis surat jalan end-to-end |
| 3. Alur pengajuan mahasiswa | 5–6 | Form + preview Livewire, riwayat status, download docx/pdf belum-TTD |
| 4. Verifikasi & TTD | 6–7 | Verifikasi admin, upload scan, status otomatis, download scan mahasiswa |
| 5. Alur judul → dosen → proposal → sidang | 7–9 | Rasio dosen + sorting otomatis, penentuan pembimbing/penguji |
| 6. Polish | 9–10 | Dashboard rasio, riwayat semua surat, import Excel, testing menyeluruh |

---

## 9. Di Luar Scope (MVP)

- Notifikasi email/WhatsApp otomatis tiap perubahan status
- Tanda tangan elektronik bersertifikat (tetap manual: cetak-TTD-scan-upload)
- Self-registrasi mahasiswa tanpa verifikasi admin
- Approval berjenjang lebih dari satu tingkat (mis. TTD kaprodi + dekan)

---

## 10. Definition of Done (per fitur)

Sebuah fitur dianggap selesai kalau:
1. Bisa diakses hanya oleh role yang berhak (dicek langsung dengan akun role lain, bukan cuma sembunyikan menu di UI)
2. Data yang ditampilkan/diedit sudah discope ke user yang login (mahasiswa tidak bisa lihat data mahasiswa lain lewat URL manipulation)
3. Untuk fitur generate dokumen: hasil docx & pdf sudah dicek manual render-nya (tidak ada placeholder tersisa, layout tidak pecah)
4. Status berubah sesuai state machine yang benar, tidak ada state yang bisa diloncati dari UI
