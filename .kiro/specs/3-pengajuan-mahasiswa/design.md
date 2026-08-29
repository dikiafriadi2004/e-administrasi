# Design — Milestone 3: Alur Pengajuan Mahasiswa

## Stack Tambahan di Milestone Ini

| Komponen | Teknologi |
|---|---|
| Form interaktif + preview | Livewire v3 |
| Upload file | Laravel Storage (disk `private`) |
| Styling form | TailwindCSS v4 |

Install Livewire jika belum ada:
```bash
composer require livewire/livewire
```

---

## Database — Kolom Tambahan

### Tabel `pengajuan_judul`

Sudah dibuat stub di Milestone 2. Tambahkan kolom yang belum ada via migration
alter jika diperlukan:

```
pengajuan_judul
├── id                    BIGINT UNSIGNED PK
├── mahasiswa_id          BIGINT UNSIGNED FK → mahasiswas.id
├── judul                 VARCHAR(500) NOT NULL
├── bidang_kajian         VARCHAR(255) NOT NULL
├── ringkasan             TEXT NOT NULL
├── dosen_pembimbing_id   BIGINT UNSIGNED NULL FK → dosens.id
├── status                ENUM('diajukan','diverifikasi','menunggu_ttd',
│                              'disetujui','ditolak') DEFAULT 'diajukan'
├── catatan_penolakan     TEXT NULL
├── file_pendukung        VARCHAR(500) NULL    ← path dokumen pendukung awal
├── nama_file_pendukung   VARCHAR(255) NULL    ← nama asli file untuk ditampilkan
├── created_at            TIMESTAMP
└── updated_at            TIMESTAMP
```

> Status `disetujui` di `pengajuan_judul` berbeda dari state machine surat —
> judul hanya butuh disetujui Kaprodi, tidak perlu TTD fisik.

### Tabel `pengajuan_surat` — Kolom Tambahan

Tambahkan kolom untuk dokumen pendukung mahasiswa:

```
├── file_pendukung        VARCHAR(500) NULL
├── nama_file_pendukung   VARCHAR(255) NULL
├── data_form             JSON NOT NULL   ← sudah ada, pastikan mencakup semua field
```

### Tabel `status_histories` (baru)

Untuk riwayat perubahan status per pengajuan:

```
status_histories
├── id                BIGINT UNSIGNED PK AUTO_INCREMENT
├── model_type        VARCHAR(255)    ← 'PengajuanJudul' atau 'PengajuanSurat'
├── model_id          BIGINT UNSIGNED
├── status_lama       VARCHAR(50) NULL
├── status_baru       VARCHAR(50) NOT NULL
├── catatan           TEXT NULL
├── changed_by        BIGINT UNSIGNED FK → users.id
├── created_at        TIMESTAMP
```

> Polymorphic relation — satu tabel untuk semua jenis pengajuan.

---

## Arsitektur Livewire Components

```
app/Livewire/Mahasiswa/
├── PengajuanJudulForm.php      ← form + preview judul skripsi
├── PengajuanAktifKuliahForm.php ← form + preview surat aktif kuliah
├── PengajuanSeminarForm.php    ← form + preview seminar proposal
└── PengajuanSidangForm.php     ← form + preview sidang skripsi

resources/views/livewire/mahasiswa/
├── pengajuan-judul-form.blade.php
├── pengajuan-aktif-kuliah-form.blade.php
├── pengajuan-seminar-form.blade.php
└── pengajuan-sidang-form.blade.php
```

### Struktur Umum Setiap Livewire Component

```php
class PengajuanJudulForm extends Component
{
    // Form fields — public properties agar reaktif
    public string $judul = '';
    public string $bidangKajian = '';
    public string $ringkasan = '';
    public ?TemporaryUploadedFile $filePendukung = null;

    // Validation rules
    protected function rules(): array { ... }

    // Submit — simpan ke DB
    public function submit(): void { ... }

    // Render — view reaktif otomatis tiap property berubah
    public function render(): View { ... }
}
```

Panel preview di sebelah kanan form (layout 2 kolom di layar besar,
stack di mobile) — update otomatis karena Livewire reactive properties.

---

## Gate / Policy untuk Scoping Data

Tambahkan method ke `PengajuanSuratPolicy`:

```php
public function view(User $user, PengajuanSurat $surat): bool
{
    if ($user->role === 'mahasiswa') {
        return $user->mahasiswa->id === $surat->mahasiswa_id;
    }
    return in_array($user->role, ['admin', 'kaprodi']);
}

public function download(User $user, PengajuanSurat $surat): bool
{
    return $this->view($user, $surat);
}
```

Tambahkan `PengajuanJudulPolicy` dengan logika serupa.

Semua query di controller mahasiswa wajib di-scope:
```php
// Benar
$pengajuan = PengajuanSurat::where('mahasiswa_id', $mahasiswa->id)->findOrFail($id);

// Salah — tidak aman
$pengajuan = PengajuanSurat::findOrFail($id);
```

---

## Logika Lock Pengajuan Bertahap

```
Pengajuan Seminar Proposal:
  Guard: pengajuan_judul.status === 'disetujui'
         DAN belum ada pengajuan_surat seminar_proposal aktif
         (status bukan 'ditolak')

Pengajuan Sidang Skripsi:
  Guard: ada pengajuan_surat seminar_proposal dengan status 'selesai'
```

Guard diimplementasikan di controller (bukan hanya UI):

```php
// Di PengajuanController@createSeminar
$judulDisetujui = PengajuanJudul::where('mahasiswa_id', $mahasiswa->id)
    ->where('status', 'disetujui')
    ->first();

if (! $judulDisetujui) {
    return view('mahasiswa.pengajuan.terkunci', [
        'pesan' => 'Pengajuan judul skripsi harus disetujui terlebih dahulu.'
    ]);
}
```

---

## Struktur Controller

```
app/Http/Controllers/Mahasiswa/
├── DashboardController.php
├── PengajuanJudulController.php   ← create, store, show
├── PengajuanSuratController.php   ← create(jenis), store, show, download
└── RiwayatController.php          ← index (semua pengajuan milik sendiri)
```

---

## Views Structure

```
resources/views/mahasiswa/
├── dashboard.blade.php
├── pengajuan/
│   ├── judul/
│   │   ├── create.blade.php      ← embed Livewire PengajuanJudulForm
│   │   └── show.blade.php        ← detail + status
│   ├── aktif-kuliah/
│   │   ├── create.blade.php
│   │   └── show.blade.php
│   ├── seminar/
│   │   ├── create.blade.php      ← atau terkunci
│   │   └── show.blade.php
│   ├── sidang/
│   │   ├── create.blade.php      ← atau terkunci
│   │   └── show.blade.php
│   └── terkunci.blade.php        ← halaman generik "tahap ini belum terbuka"
└── riwayat/
    └── index.blade.php           ← tabel semua pengajuan + status + link download
```

---

## Upload Dokumen Pendukung

Gunakan Livewire `WithFileUploads` trait untuk upload yang lebih smooth:

```php
use Livewire\WithFileUploads;

class PengajuanJudulForm extends Component
{
    use WithFileUploads;

    public $filePendukung;  // tipe tidak di-hint agar Livewire bisa handle

    protected function rules(): array
    {
        return [
            'filePendukung' => 'nullable|file|mimes:pdf,doc,docx|max:10240',
        ];
    }

    public function submit(): void
    {
        $this->validate();

        $path = null;
        $namaAsli = null;

        if ($this->filePendukung) {
            $namaAsli = $this->filePendukung->getClientOriginalName();
            $path = $this->filePendukung->storeAs(
                'pendukung/' . auth()->id(),
                Str::uuid() . '.' . $this->filePendukung->extension(),
                'private'
            );
        }

        PengajuanJudul::create([
            'mahasiswa_id'        => auth()->user()->mahasiswa->id,
            'judul'               => $this->judul,
            'bidang_kajian'       => $this->bidangKajian,
            'ringkasan'           => $this->ringkasan,
            'file_pendukung'      => $path,
            'nama_file_pendukung' => $namaAsli,
            'status'              => 'diajukan',
        ]);

        // Catat status history
        StatusHistory::create([...]);

        session()->flash('success', 'Pengajuan judul berhasil dikirim.');
        $this->redirect(route('mahasiswa.riwayat.index'));
    }
}
```

---

## Halaman Riwayat

Tampilkan dua section terpisah:

1. **Riwayat Judul Skripsi** — tabel pengajuan judul dengan status badge
   berwarna (diajukan=kuning, diverifikasi=biru, disetujui=hijau, ditolak=merah).

2. **Riwayat Surat** — tabel pengajuan surat dengan kolom: jenis surat,
   tanggal ajukan, status, aksi (lihat detail, download docx/pdf/scan).

Tombol download hanya muncul berdasarkan kondisi:
- Download DOCX/PDF (belum TTD): tampil jika `file_docx != null` DAN `status != ditolak`
- Download Scan (sudah TTD): tampil jika `file_scan != null` (selalu tampil tanpa batas)
