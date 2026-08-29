# Design — Milestone 2: Generate Surat Inti

## Dependencies yang Perlu Diinstall

```bash
composer require phpoffice/phpword
```

LibreOffice headless harus terinstall di server Linux (production):
```bash
sudo apt-get install -y libreoffice --no-install-recommends
```

Untuk development lokal di Windows (Herd), LibreOffice Windows installer
bisa digunakan. Path dikonfigurasi via `.env`:
```
LIBREOFFICE_PATH="C:\Program Files\LibreOffice\program\soffice.exe"
# Di Linux production:
# LIBREOFFICE_PATH=/usr/bin/soffice
```

---

## Struktur Folder Template & File Generate

```
storage/app/private/
├── templates/
│   ├── aktif_kuliah_v1.docx
│   ├── seminar_proposal_v1.docx
│   ├── sidang_skripsi_v1.docx
│   └── undangan_penguji_v1.docx
└── surat/
    └── {pengajuan_id}/          ← satu folder per pengajuan
        ├── {uuid}.docx           ← file_docx (belum TTD)
        ├── {uuid}.pdf            ← file_pdf  (belum TTD)
        └── scan_{uuid}.pdf       ← file_scan (diupload belakangan)
```

Semua path di bawah `storage/app/private/` **tidak dapat diakses langsung**
via URL publik. Download selalu melalui controller yang memeriksa
otorisasi terlebih dahulu.

---

## Database Schema

### Tabel `templates_surat`

```
templates_surat
├── id              BIGINT UNSIGNED PK AUTO_INCREMENT
├── jenis_surat     ENUM('aktif_kuliah','seminar_proposal','sidang_skripsi','undangan_penguji') NOT NULL
├── path_file       VARCHAR(500) NOT NULL        ← relatif ke storage/app/private/
├── versi           SMALLINT UNSIGNED DEFAULT 1
├── is_aktif        BOOLEAN DEFAULT true          ← hanya satu aktif per jenis
├── created_at      TIMESTAMP
└── updated_at      TIMESTAMP
```

### Tabel `pengajuan_surat`

```
pengajuan_surat
├── id                  BIGINT UNSIGNED PK AUTO_INCREMENT
├── mahasiswa_id        BIGINT UNSIGNED FK → mahasiswas.id
├── jenis_surat         ENUM('aktif_kuliah','seminar_proposal','sidang_skripsi','undangan_penguji')
├── pengajuan_judul_id  BIGINT UNSIGNED NULL FK → pengajuan_judul.id
├── data_form           JSON NOT NULL              ← data isian form pengajuan
├── nomor_surat         VARCHAR(100) NULL UNIQUE   ← diisi saat generate
├── dosen_penguji_id    BIGINT UNSIGNED NULL FK → dosens.id
├── status              ENUM('diajukan','diverifikasi','menunggu_ttd','sudah_ditandatangani','selesai','ditolak') DEFAULT 'diajukan'
├── catatan_penolakan   TEXT NULL
├── file_docx           VARCHAR(500) NULL
├── file_pdf            VARCHAR(500) NULL
├── file_scan           VARCHAR(500) NULL
├── generated_at        TIMESTAMP NULL
├── created_at          TIMESTAMP
└── updated_at          TIMESTAMP
```

### Tabel `nomor_surat_counters`

Untuk menghindari race condition pada penomoran surat:

```
nomor_surat_counters
├── id          BIGINT UNSIGNED PK AUTO_INCREMENT
├── jenis_surat ENUM('aktif_kuliah','seminar_proposal','sidang_skripsi','undangan_penguji')
├── tahun       YEAR NOT NULL
├── counter     INT UNSIGNED DEFAULT 0
├── UNIQUE(jenis_surat, tahun)
```

---

## Service Layer

Semua logika generate terpusat di satu service:

```
app/Services/
└── SuratGeneratorService.php
```

### Interface Service

```php
class SuratGeneratorService
{
    /**
     * Entry point utama — generate docx + pdf untuk satu pengajuan.
     * Mengembalikan array ['docx' => path, 'pdf' => path].
     */
    public function generate(PengajuanSurat $pengajuan): array

    /**
     * Hanya konversi docx → pdf (bisa dipanggil ulang).
     */
    public function convertToPdf(string $docxPath): string

    /**
     * Generate nomor surat berikutnya (thread-safe via DB lock).
     */
    public function generateNomorSurat(string $jenisSurat): string
}
```

---

## Alur Generate Dokumen — Detail

```
Controller menerima request generate
            │
            ▼
1. Load PengajuanSurat + relasi (mahasiswa, judul, pembimbing, penguji)
            │
            ▼
2. Ambil TemplateSurat aktif untuk jenis_surat tersebut
   → Jika tidak ada template aktif: throw exception, tampilkan error ke admin
            │
            ▼
3. Buat direktori storage/app/private/surat/{pengajuan_id}/ jika belum ada
            │
            ▼
4. Buat nama file unik: $filename = Str::uuid() . '.docx'
   Salin template ke path tujuan (tidak mengedit file template asli)
            │
            ▼
5. Buka salinan dengan PHPWord TemplateProcessor:
   $processor = new TemplateProcessor($targetDocxPath);
            │
            ▼
6. Isi semua placeholder (lihat tabel placeholder di bawah)
   $processor->setValue('nama_mahasiswa', $mahasiswa->user->name);
   ...
   $processor->saveAs($targetDocxPath);
            │
            ▼
7. Generate nomor surat (jika belum ada):
   → DB transaction + SELECT ... FOR UPDATE pada nomor_surat_counters
   → Increment counter, format nomor, simpan ke pengajuan_surat.nomor_surat
            │
            ▼
8. Konversi docx → pdf via LibreOffice headless:
   $pdfPath = $this->convertToPdf($targetDocxPath);
            │
            ├── Berhasil → simpan path ke file_docx & file_pdf di DB
            │              set generated_at = now()
            └── Gagal    → log error lengkap, throw SuratGenerationException
```

---

## Implementasi Konversi Docx → PDF

```php
private function convertToPdf(string $docxAbsPath): string
{
    $outputDir   = dirname($docxAbsPath);
    $libreOfficePath = config('app.libreoffice_path',
        env('LIBREOFFICE_PATH', 'soffice'));

    $command = sprintf(
        '%s --headless --convert-to pdf --outdir %s %s 2>&1',
        escapeshellcmd($libreOfficePath),
        escapeshellarg($outputDir),
        escapeshellarg($docxAbsPath)
    );

    $output   = [];
    $exitCode = 0;
    exec($command, $output, $exitCode);

    if ($exitCode !== 0) {
        Log::error('LibreOffice conversion failed', [
            'command'   => $command,
            'exit_code' => $exitCode,
            'output'    => implode("\n", $output),
        ]);
        throw new SuratGenerationException(
            'Konversi PDF gagal. Periksa log untuk detail.'
        );
    }

    // LibreOffice menghasilkan file dengan nama sama, ekstensi .pdf
    $pdfPath = $outputDir . DIRECTORY_SEPARATOR
             . pathinfo($docxAbsPath, PATHINFO_FILENAME) . '.pdf';

    if (! file_exists($pdfPath)) {
        throw new SuratGenerationException(
            'File PDF tidak ditemukan setelah konversi.'
        );
    }

    return $pdfPath;
}
```

> **Catatan timeout:** Tambahkan `set_time_limit(60)` di controller sebelum
> memanggil generate, atau jadikan job queue di milestone selanjutnya jika
> server production lambat.

---

## Placeholder PHPWord — Daftar Lengkap

Format placeholder: `${nama_placeholder}` (sintaks standar PHPWord).

### Placeholder Universal (semua jenis surat)
| Placeholder | Sumber Data |
|---|---|
| `${nomor_surat}` | `pengajuan_surat.nomor_surat` |
| `${tanggal_surat}` | tanggal generate, format Indonesia (mis. "13 Agustus 2026") |
| `${nama_mahasiswa}` | `users.name` via mahasiswa |
| `${nim}` | `mahasiswas.nim` |
| `${angkatan}` | `mahasiswas.angkatan` |
| `${nama_kaprodi}` | config atau dari tabel `users` role kaprodi |
| `${nip_kaprodi}` | config |

### Placeholder Surat Aktif Kuliah
| Placeholder | Sumber Data |
|---|---|
| `${keperluan}` | `pengajuan_surat.data_form.keperluan` |
| `${tujuan_instansi}` | `pengajuan_surat.data_form.tujuan_instansi` |

### Placeholder Seminar Proposal & Sidang Skripsi
| Placeholder | Sumber Data |
|---|---|
| `${judul_skripsi}` | `pengajuan_judul.judul` |
| `${bidang_kajian}` | `pengajuan_judul.bidang_kajian` |
| `${nama_pembimbing}` | `dosens.nama` via pembimbing |
| `${nip_pembimbing}` | `dosens.nip` via pembimbing |
| `${tanggal_seminar}` | `pengajuan_surat.data_form.tanggal_rencana` |

### Placeholder Tambahan Sidang Skripsi & Undangan Penguji
| Placeholder | Sumber Data |
|---|---|
| `${nama_penguji}` | `dosens.nama` via penguji |
| `${nip_penguji}` | `dosens.nip` via penguji |
| `${tanggal_sidang}` | `pengajuan_surat.data_form.tanggal_rencana` |
| `${waktu_sidang}` | `pengajuan_surat.data_form.waktu_rencana` |
| `${tempat_sidang}` | `pengajuan_surat.data_form.tempat` |

---

## Format Penomoran Surat

```
{urutan_3digit}/{kode_institusi}/{kode_fakultas}/{kode_prodi}/{bulan_romawi}/{tahun}

Contoh: 001/UN-XX/FAK/TI/VIII/2026
```

Kode institusi, fakultas, prodi dikonfigurasi di `config/surat.php`:

```php
return [
    'kode_institusi' => env('SURAT_KODE_INSTITUSI', 'UN-XX'),
    'kode_fakultas'  => env('SURAT_KODE_FAKULTAS', 'FAK'),
    'kode_prodi'     => env('SURAT_KODE_PRODI', 'TI'),
    'libreoffice_path' => env('LIBREOFFICE_PATH', 'soffice'),
    'nama_kaprodi'   => env('SURAT_NAMA_KAPRODI', ''),
    'nip_kaprodi'    => env('SURAT_NIP_KAPRODI', ''),
];
```

Counter per jenis surat per tahun, reset ke 1 setiap tahun baru.
Akses ke tabel `nomor_surat_counters` menggunakan `DB::transaction` dengan
`lockForUpdate()` untuk mencegah duplikasi nomor pada request bersamaan.

---

## Struktur File Kode

```
app/
├── Services/
│   ├── SuratGeneratorService.php
│   └── NomorSuratService.php
├── Exceptions/
│   └── SuratGenerationException.php
├── Models/
│   ├── TemplateSurat.php
│   ├── PengajuanSurat.php
│   └── NomorSuratCounter.php
└── Http/Controllers/
    └── Admin/
        ├── TemplateSuratController.php   ← upload/kelola template
        └── GenerateSuratController.php   ← trigger generate + download
```

---

## Desain Template Docx — Panduan Pembuatan

Template dibuat manual di Microsoft Word, lalu disimpan sebagai `.docx`.
Panduan agar render tidak pecah:

1. **Kop surat** — gunakan Header Word bawaan (`Insert > Header`) agar
   muncul konsisten di semua halaman. Masukkan logo sebagai gambar inline
   (bukan floating) supaya PHPWord tidak menggeser posisi.

2. **Font** — Times New Roman 12pt untuk body, 14pt bold untuk judul surat.
   Jangan gunakan font yang tidak tersedia di server LibreOffice.

3. **Margin** — atas 3cm, bawah 2.5cm, kiri 3cm, kanan 2.5cm (standar
   surat dinas Indonesia).

4. **Line spacing** — 1.5 untuk body paragraf, single untuk blok alamat.

5. **Placeholder** — tulis langsung di posisinya di body dokumen, jangan
   taruh di text box atau shape (PHPWord tidak bisa mengaksesnya). Format:
   `${nama_placeholder}` tanpa spasi di dalam kurung.

6. **Tanda tangan** — buat tabel tak berborder 2 kolom di bagian bawah:
   kolom kiri kosong, kolom kanan berisi "Kepala Program Studi" → baris
   kosong 4 baris → `${nama_kaprodi}` → `NIP. ${nip_kaprodi}`.

7. **Footer** — gunakan Footer Word bawaan, isi alamat + kontak prodi.
   Gunakan teks statis (bukan placeholder) karena data ini jarang berubah.

8. **Uji render wajib** — setelah template jadi, jalankan
   `php artisan surat:test-generate {jenis}` dengan data dummy terpanjang
   (nama 50 karakter, judul 2 baris) sebelum dianggap production-ready.

---

## Controller Download (Otorisasi)

File tidak boleh diakses langsung. Controller download harus:

```php
public function download(PengajuanSurat $surat, string $tipe): StreamedResponse
{
    // 1. Cek hak akses: mahasiswa hanya bisa download miliknya sendiri
    Gate::authorize('download-surat', $surat);

    // 2. Tentukan path berdasarkan tipe
    $path = match($tipe) {
        'docx' => $surat->file_docx,
        'pdf'  => $surat->file_pdf,
        'scan' => $surat->file_scan,
        default => abort(404),
    };

    if (! $path || ! Storage::disk('private')->exists($path)) {
        abort(404, 'File tidak tersedia.');
    }

    // 3. Stream file ke browser
    return Storage::disk('private')->download($path);
}
```
