<?php

namespace Database\Seeders;

use App\Models\BerkasPengajuan;
use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Models\Pengaturan;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

/**
 * DemoSeeder — data realistis untuk demo / testing manual.
 *
 * Alur terbaru yang berlaku:
 *   Judul    : diajukan → diverifikasi_admin → disetujui (Kaprodi)
 *   Seminar  : diajukan → diverifikasi_admin → disetujui (Kaprodi)
 *   Sidang   : diajukan (berkas_diverifikasi=true) → disetujui (Kaprodi)
 *   Surat    : diajukan → menunggu_ttd → sudah_ditandatangani → selesai
 *
 * Jalankan: php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    private ?User $admin = null;

    private ?User $kaprodi = null;

    public function run(): void
    {
        $this->admin = User::where('role', 'admin')->first();
        $this->kaprodi = User::where('role', 'kaprodi')->first();

        $this->updatePengaturan();
        $dosens = $this->seedDosen();
        $mahasiswas = $this->seedMahasiswa();
        $this->seedPengajuan($mahasiswas, $dosens);

        $this->command->newLine();
        $this->command->info('✓ Demo seeder selesai!');
        $this->command->table(
            ['Entitas', 'Jumlah'],
            [
                ['Dosen',           Dosen::count()],
                ['Mahasiswa',       Mahasiswa::count()],
                ['Pengajuan Judul', PengajuanJudul::count()],
                ['Pengajuan Surat', PengajuanSurat::count()],
            ]
        );
        $this->command->newLine();
        $this->command->line('Login mahasiswa: NIM sebagai email & password.');
        $this->command->line('Contoh: <info>2020001</info> / <info>2020001</info>');
    }

    // ─── Pengaturan ───────────────────────────────────────────────────────────

    private function updatePengaturan(): void
    {
        $updates = [
            'nama_universitas' => 'Universitas Contoh Indonesia',
            'nama_fakultas' => 'Fakultas Ilmu Komputer',
            'nama_prodi' => 'Program Studi Teknik Informatika',
            'alamat_prodi' => 'Jl. Pendidikan No. 1, Kota Contoh, 12345',
            'telepon_prodi' => '(021) 1234-5678',
            'email_prodi' => 'prodi.ti@contoh.ac.id',
            'kota_prodi' => 'Kota Contoh',
            'nama_kaprodi' => 'Dr. Budi Santoso, M.Kom.',
            'nip_kaprodi' => '196501011990011001',
            'nama_dekan' => 'Prof. Dr. Agus Wijaya, M.T.',
            'nip_dekan' => '196001011985011001',
            'kode_institusi' => 'UCI.F9',
            'kode_prodi' => 'PK.01.06',
            'semester_aktif' => 'Ganjil',
            'tahun_akademik' => '2026/2027',
        ];

        foreach ($updates as $key => $value) {
            Pengaturan::updateOrCreate(['key' => $key], ['value' => $value, 'label' => $key, 'grup' => 'demo']);
        }
        $this->command->line('  ✓ Pengaturan diperbarui');
    }

    // ─── Dosen ────────────────────────────────────────────────────────────────

    /** @return Dosen[] */
    private function seedDosen(): array
    {
        $rows = [
            ['nama' => 'Dr. Ahmad Fauzi, M.Kom.',    'nip' => '197001012000031001', 'kapasitas_maksimal' => 5],
            ['nama' => 'Prof. Budi Raharjo, Ph.D.',  'nip' => '196805152001121002', 'kapasitas_maksimal' => null],
            ['nama' => 'Dr. Citra Dewi, M.T.',       'nip' => '198203202010012003', 'kapasitas_maksimal' => 4],
            ['nama' => 'Drs. Eko Prasetyo, M.Si.',   'nip' => '197512102005011004', 'kapasitas_maksimal' => null],
            ['nama' => 'Dr. Fitri Handayani, M.Cs.', 'nip' => '198907252015042005', 'kapasitas_maksimal' => 6],
            ['nama' => 'Dr. Gilang Permana, M.T.',   'nip' => '198001012008011006', 'kapasitas_maksimal' => 5],
            ['nama' => 'Hendra Kusuma, M.Kom.',       'nip' => '199002152018021007', 'kapasitas_maksimal' => null],
        ];

        $result = [];
        foreach ($rows as $d) {
            $result[] = Dosen::firstOrCreate(['nip' => $d['nip']], $d);
        }
        $this->command->line('  ✓ '.count($result).' dosen');

        return $result;
    }

    // ─── Mahasiswa ────────────────────────────────────────────────────────────

    /** @return Mahasiswa[] */
    private function seedMahasiswa(): array
    {
        $rows = [
            ['nim' => '2020001', 'nama' => 'Andi Pratama',    'email' => 'andi@mhs.contoh.ac.id',  'angkatan' => 2020, 'alamat' => 'Jl. Mawar No. 1'],
            ['nim' => '2020002', 'nama' => 'Siti Rahayu',     'email' => 'siti@mhs.contoh.ac.id',  'angkatan' => 2020, 'alamat' => 'Jl. Melati No. 5'],
            ['nim' => '2020003', 'nama' => 'Budi Cahyono',    'email' => 'budi@mhs.contoh.ac.id',  'angkatan' => 2020, 'alamat' => 'Jl. Kenanga No. 3'],
            ['nim' => '2021001', 'nama' => 'Dewi Lestari',    'email' => 'dewi@mhs.contoh.ac.id',  'angkatan' => 2021, 'alamat' => 'Jl. Anggrek No. 7'],
            ['nim' => '2021002', 'nama' => 'Eko Setiawan',    'email' => 'eko@mhs.contoh.ac.id',   'angkatan' => 2021, 'alamat' => 'Jl. Dahlia No. 2'],
            ['nim' => '2021003', 'nama' => 'Fajar Nugroho',   'email' => 'fajar@mhs.contoh.ac.id', 'angkatan' => 2021, 'alamat' => 'Jl. Tulip No. 4'],
            ['nim' => '2022001', 'nama' => 'Galih Wicaksono', 'email' => 'galih@mhs.contoh.ac.id', 'angkatan' => 2022, 'alamat' => 'Jl. Flamboyan No. 9'],
            ['nim' => '2022002', 'nama' => 'Hana Pertiwi',    'email' => 'hana@mhs.contoh.ac.id',  'angkatan' => 2022, 'alamat' => 'Jl. Seruni No. 6'],
            ['nim' => '2022003', 'nama' => 'Irfan Maulana',   'email' => 'irfan@mhs.contoh.ac.id', 'angkatan' => 2022, 'alamat' => 'Jl. Teratai No. 8'],
            ['nim' => '2023001', 'nama' => 'Joko Susilo',     'email' => 'joko@mhs.contoh.ac.id',  'angkatan' => 2023, 'alamat' => 'Jl. Kamboja No. 10'],
        ];

        $result = [];
        foreach ($rows as $d) {
            $user = User::firstOrCreate(
                ['email' => $d['email']],
                ['name' => $d['nama'], 'password' => Hash::make($d['nim']), 'role' => 'mahasiswa', 'is_active' => true]
            );
            $mhs = Mahasiswa::firstOrCreate(
                ['nim' => $d['nim']],
                ['user_id' => $user->id, 'angkatan' => $d['angkatan'], 'alamat' => $d['alamat']]
            );
            $result[] = $mhs->load('user');
        }
        $this->command->line('  ✓ '.count($result).' mahasiswa');

        return $result;
    }

    // ─── Pengajuan ────────────────────────────────────────────────────────────

    private function seedPengajuan(array $mhs, array $dosen): void
    {
        // ── ANDI (mhs[0]): alur lengkap sidang ─────────────────────────────────
        $judulAndi = $this->buatJudul($mhs[0], [
            'judul' => 'Sistem Deteksi Plagiarisme Berbasis NLP untuk Tugas Akhir Mahasiswa',
            'bidang_kajian' => 'Kecerdasan Buatan',
            'ringkasan' => 'Penelitian mengembangkan sistem deteksi plagiarisme menggunakan Natural Language Processing untuk membantu penilaian tugas akhir mahasiswa secara otomatis dengan akurasi tinggi.',
            'pendekatan_penelitian' => 'Kuantitatif — Eksperimental',
            'nama_dosen_wali' => $dosen[3]->nama,
            'dosen_pembimbing_id' => $dosen[0]->id,
            'status' => 'disetujui',
        ], [
            ['dari' => 'diajukan', 'ke' => 'diverifikasi_admin', 'actor' => $this->admin,   'catatan' => 'Berkas lengkap, diteruskan ke Kaprodi.'],
            ['dari' => 'diverifikasi_admin', 'ke' => 'disetujui', 'actor' => $this->kaprodi, 'catatan' => 'Judul disetujui, Dr. Ahmad Fauzi ditetapkan sebagai pembimbing.'],
        ]);

        $seminarAndi = $this->buatSurat($mhs[0], 'seminar_proposal', [
            'pengajuan_judul_id' => $judulAndi->id,
            'data_form' => [],
            'dosen_penguji_id' => $dosen[1]->id,
            'dosen_penguji_2_id' => $dosen[2]->id,
            'tanggal_jadwal' => '2026-06-14',
            'waktu_jadwal' => '09.00 s/d Selesai',
            'tempat_jadwal' => 'Ruang Seminar A Lt. 2',
            'status' => 'sudah_ditandatangani',
            'file_absensi_seminar' => 'absensi/2020001/absensi_demo.pdf',
        ], [
            ['dari' => 'diajukan', 'ke' => 'diverifikasi_admin', 'actor' => $this->admin,   'catatan' => 'Berkas seminar lengkap.'],
            ['dari' => 'diverifikasi_admin', 'ke' => 'disetujui', 'actor' => $this->kaprodi, 'catatan' => 'Penguji ditetapkan.'],
            ['dari' => 'disetujui', 'ke' => 'menunggu_ttd',       'actor' => $this->admin,   'catatan' => 'Surat undangan digenerate.'],
            ['dari' => 'menunggu_ttd', 'ke' => 'sudah_ditandatangani', 'actor' => $this->admin, 'catatan' => 'Surat undangan sudah TTD Kaprodi.'],
        ]);

        $sidangAndi = $this->buatSurat($mhs[0], 'sidang_skripsi', [
            'pengajuan_judul_id' => $judulAndi->id,
            'data_form' => ['tanggal_rencana' => '2026-08-20'],
            'dosen_penguji_id' => $dosen[1]->id,
            'dosen_penguji_2_id' => $dosen[2]->id,
            'berkas_diverifikasi' => true,
            'tanggal_jadwal' => '2026-08-22',
            'waktu_jadwal' => '09.00 s/d Selesai',
            'tempat_jadwal' => 'Ruang Sidang Lt. 3',
            'status' => 'disetujui',
        ], [
            ['dari' => 'diajukan', 'ke' => 'diajukan', 'actor' => $this->admin, 'catatan' => 'Berkas 16 syarat sidang diperiksa dan lengkap.'],
            ['dari' => 'diajukan', 'ke' => 'disetujui', 'actor' => $this->kaprodi, 'catatan' => 'Sidang disetujui, penguji ditetapkan.'],
        ]);
        // Tambah beberapa berkas dummy untuk sidang Andi
        $this->tambahBerkasSurat($sidangAndi, [
            'Cover ACC Dosen Pembimbing' => '2020001_cover_acc.pdf',
            'Lembar Persetujuan Skripsi' => '2020001_lembar_persetujuan.pdf',
            'Transkip Nilai (disahkan WD1)' => '2020001_transkip.pdf',
            'Naskah Skripsi (4 eksemplar)' => '2020001_naskah.pdf',
            'Abstrak Skripsi' => '2020001_abstrak.pdf',
        ]);

        // ── SITI (mhs[1]): judul disetujui, seminar menunggu kaprodi ───────────
        $judulSiti = $this->buatJudul($mhs[1], [
            'judul' => 'Aplikasi Monitoring Kehadiran Mahasiswa Berbasis QR Code dan Geolokasi',
            'bidang_kajian' => 'Rekayasa Perangkat Lunak',
            'ringkasan' => 'Pengembangan aplikasi mobile untuk monitoring kehadiran perkuliahan secara real-time menggunakan QR Code dan validasi geolokasi kampus.',
            'pendekatan_penelitian' => 'Kuantitatif — Pengembangan Sistem',
            'dosen_pembimbing_id' => $dosen[2]->id,
            'status' => 'disetujui',
        ], [
            ['dari' => 'diajukan', 'ke' => 'diverifikasi_admin', 'actor' => $this->admin, 'catatan' => 'Berkas lengkap.'],
            ['dari' => 'diverifikasi_admin', 'ke' => 'disetujui', 'actor' => $this->kaprodi, 'catatan' => 'Judul disetujui.'],
        ]);

        $seminarSiti = $this->buatSurat($mhs[1], 'seminar_proposal', [
            'pengajuan_judul_id' => $judulSiti->id,
            'data_form' => [],
            'status' => 'diverifikasi_admin',  // sudah verif admin, menunggu Kaprodi
        ], [
            ['dari' => 'diajukan', 'ke' => 'diverifikasi_admin', 'actor' => $this->admin, 'catatan' => 'Berkas seminar lengkap, diteruskan ke Kaprodi.'],
        ]);
        $this->tambahBerkasSurat($seminarSiti, [
            'Cover ACC Dosen Pembimbing' => '2020002_cover_acc.pdf',
            'Riwayat Bimbingan (TTD Pembimbing)' => '2020002_riwayat_bimbingan.pdf',
        ]);

        // ── BUDI (mhs[2]): judul diverifikasi admin, menunggu Kaprodi ──────────
        $this->buatJudul($mhs[2], [
            'judul' => 'Analisis Sentimen Review Produk E-commerce Menggunakan Deep Learning',
            'bidang_kajian' => 'Kecerdasan Buatan',
            'ringkasan' => 'Membangun model analisis sentimen review produk e-commerce Indonesia menggunakan LSTM dan BERT untuk klasifikasi positif/negatif.',
            'status' => 'diverifikasi_admin',
        ], [
            ['dari' => 'diajukan', 'ke' => 'diverifikasi_admin', 'actor' => $this->admin, 'catatan' => 'Berkas SPUP lengkap, diteruskan ke Kaprodi.'],
        ]);

        // ── DEWI (mhs[3]): judul baru diajukan (menunggu admin verifikasi) ──────
        $this->buatJudul($mhs[3], [
            'judul' => 'Sistem Rekomendasi Buku Perpustakaan Digital Berbasis Collaborative Filtering',
            'bidang_kajian' => 'Sistem Informasi',
            'ringkasan' => 'Membangun sistem rekomendasi buku perpustakaan digital menggunakan metode collaborative filtering untuk meningkatkan kepuasan pengguna.',
            'status' => 'diajukan',
        ]);

        // ── EKO (mhs[4]): judul ditolak Kaprodi ────────────────────────────────
        $this->buatJudul($mhs[4], [
            'judul' => 'Website Toko Online Sederhana Berbasis PHP',
            'bidang_kajian' => 'Rekayasa Perangkat Lunak',
            'ringkasan' => 'Membuat website toko online menggunakan PHP dan MySQL dengan fitur keranjang belanja.',
            'status' => 'ditolak',
            'catatan_penolakan' => 'Judul terlalu umum dan tidak ada kontribusi ilmiah yang jelas. Tambahkan metode/pendekatan spesifik dan rumusan masalah yang lebih kuat.',
        ], [
            ['dari' => 'diajukan', 'ke' => 'diverifikasi_admin', 'actor' => $this->admin, 'catatan' => 'Berkas lengkap.'],
            ['dari' => 'diverifikasi_admin', 'ke' => 'ditolak', 'actor' => $this->kaprodi, 'catatan' => 'Judul terlalu umum.'],
        ]);

        // ── FAJAR (mhs[5]): surat aktif kuliah selesai + satu baru diajukan ─────
        $this->buatSurat($mhs[5], 'aktif_kuliah', [
            'data_form' => ['keperluan' => 'Melamar Beasiswa', 'tujuan_instansi' => 'Direktorat Kemahasiswaan'],
            'nomor_surat' => '003',
            'status' => 'selesai',
            'file_docx' => 'surat/2021003/aktif_kuliah/aktif_demo.docx',
        ], [
            ['dari' => 'diajukan', 'ke' => 'menunggu_ttd', 'actor' => $this->admin, 'catatan' => 'Surat digenerate.'],
            ['dari' => 'menunggu_ttd', 'ke' => 'sudah_ditandatangani', 'actor' => $this->admin, 'catatan' => 'Scan TTD diupload.'],
            ['dari' => 'sudah_ditandatangani', 'ke' => 'selesai', 'actor' => $this->admin, 'catatan' => 'Surat selesai.'],
        ]);

        $this->buatSurat($mhs[5], 'aktif_kuliah', [
            'data_form' => ['keperluan' => 'Magang / Praktik Kerja Lapangan (PKL)', 'tujuan_instansi' => 'PT. Teknologi Maju Indonesia'],
            'status' => 'diajukan',
        ]);

        // ── GALIH (mhs[6]): surat aktif kuliah generate, menunggu TTD ──────────
        $this->buatSurat($mhs[6], 'aktif_kuliah', [
            'data_form' => ['keperluan' => 'Keperluan Akademik', 'tujuan_instansi' => 'Panitia Gemastik 2026'],
            'nomor_surat' => '004',
            'status' => 'menunggu_ttd',
            'file_docx' => 'surat/2022001/aktif_kuliah/aktif_demo.docx',
        ], [
            ['dari' => 'diajukan', 'ke' => 'menunggu_ttd', 'actor' => $this->admin, 'catatan' => 'Surat digenerate.'],
        ]);

        // ── HANA (mhs[7]): izin magang baru diajukan ────────────────────────────
        $this->buatSurat($mhs[7], 'izin_magang', [
            'data_form' => [
                'nama_instansi' => 'PT. Garuda Digital Nusantara',
                'alamat_instansi' => 'Jl. Sudirman No. 100, Jakarta Pusat',
                'tanggal_mulai' => '2026-07-01',
                'tanggal_selesai' => '2026-09-30',
            ],
            'status' => 'diajukan',
        ]);

        // ── IRFAN (mhs[8]): sidang dengan 16 berkas, menunggu Kaprodi ───────────
        $judulIrfan = $this->buatJudul($mhs[8], [
            'judul' => 'Optimasi Algoritma Pathfinding A* untuk Game Mobile Berbasis Unity',
            'bidang_kajian' => 'Rekayasa Perangkat Lunak',
            'ringkasan' => 'Penelitian mengoptimasi algoritma A* untuk game mobile dengan pendekatan hierarchical pathfinding dan caching path.',
            'dosen_pembimbing_id' => $dosen[4]->id,
            'status' => 'disetujui',
        ], [
            ['dari' => 'diajukan', 'ke' => 'diverifikasi_admin', 'actor' => $this->admin, 'catatan' => 'Berkas lengkap.'],
            ['dari' => 'diverifikasi_admin', 'ke' => 'disetujui', 'actor' => $this->kaprodi, 'catatan' => 'Judul disetujui.'],
        ]);

        $sidangIrfan = $this->buatSurat($mhs[8], 'sidang_skripsi', [
            'pengajuan_judul_id' => $judulIrfan->id,
            'data_form' => ['tanggal_rencana' => '2026-09-15'],
            'berkas_diverifikasi' => true,
            'catatan_admin' => null,
            'status' => 'diajukan',
        ], [
            ['dari' => 'diajukan', 'ke' => 'diajukan', 'actor' => $this->admin, 'catatan' => 'Berkas 16 syarat sidang diperiksa dan dinyatakan lengkap.'],
        ]);
        // Tambah 16 berkas sidang Irfan
        $berkasIrfan = [
            'surat_permohonan' => '2022003_surat_permohonan.pdf',
            'biodata_mahasiswa' => '2022003_biodata.pdf',
            'lembar_persetujuan' => '2022003_lembar_persetujuan.pdf',
            'kwitansi_spp' => '2022003_kwitansi_spp.pdf',
            'transkip_nilai' => '2022003_transkip.pdf',
            'khs' => '2022003_khs.pdf',
            'naskah_skripsi' => '2022003_naskah.pdf',
            'ket_hadir_seminar' => '2022003_ket_hadir.pdf',
            'buku_bimbingan' => '2022003_buku_bimbingan.pdf',
            'krs_terakhir' => '2022003_krs.pdf',
            'abstrak_skripsi' => '2022003_abstrak.pdf',
            'nilai_toefl' => '2022003_toefl.pdf',
            'sk_pembimbing' => '2022003_sk_pembimbing.pdf',
            'jurnal_ilmiah' => '2022003_jurnal.pdf',
            'map_berwarna' => '2022003_map.jpg',
            'bebas_turnitin' => '2022003_turnitin.pdf',
        ];
        foreach ($berkasIrfan as $key => $namaFile) {
            $label = match ($key) {
                'surat_permohonan' => 'Surat Permohonan',
                'biodata_mahasiswa' => 'Biodata Mahasiswa',
                'lembar_persetujuan' => 'Lembar Persetujuan Skripsi',
                'kwitansi_spp' => 'Kwitansi SPP Terakhir',
                'transkip_nilai' => 'Transkip Nilai (disahkan WD1)',
                'khs' => 'Kartu Hasil Studi (KHS) Semester 1 s/d Akhir',
                'naskah_skripsi' => 'Naskah Skripsi (4 eksemplar — scan/PDF)',
                'ket_hadir_seminar' => 'Keterangan Hadir Seminar Minimal 10 Kali',
                'buku_bimbingan' => 'Buku Bimbingan Skripsi',
                'krs_terakhir' => 'Kartu Rencana Studi (KRS) Terakhir',
                'abstrak_skripsi' => 'Abstrak Skripsi',
                'nilai_toefl' => 'Nilai TOEFL',
                'sk_pembimbing' => 'SK Pembimbing Mahasiswa',
                'jurnal_ilmiah' => 'Jurnal Ilmiah Mahasiswa',
                'map_berwarna' => 'Map Berwarna Merah',
                'bebas_turnitin' => 'Surat Keterangan Bebas Turnitin ≤30%',
                default => $key,
            };
            BerkasPengajuan::firstOrCreate(
                ['pengajuan_type' => PengajuanSurat::class, 'pengajuan_id' => $sidangIrfan->id, 'label' => $label],
                ['path_file' => "berkas/2022003/sidang_skripsi/{$key}_demo.pdf", 'nama_asli' => $namaFile]
            );
        }

        $this->command->line('  ✓ Pengajuan demo dalam berbagai status');
    }

    // ─── Helper: buat pengajuan judul ─────────────────────────────────────────

    private function buatJudul(Mahasiswa $mhs, array $data, array $histories = []): PengajuanJudul
    {
        $judul = PengajuanJudul::firstOrCreate(
            ['mahasiswa_id' => $mhs->id, 'judul' => $data['judul']],
            array_merge(['mahasiswa_id' => $mhs->id], $data)
        );

        foreach ($histories as $h) {
            $this->history($judul, PengajuanJudul::class, $h['dari'], $h['ke'], $h['actor'] ?? null, $h['catatan'] ?? null);
        }

        return $judul;
    }

    // ─── Helper: buat pengajuan surat ─────────────────────────────────────────

    private function buatSurat(Mahasiswa $mhs, string $jenis, array $data, array $histories = []): PengajuanSurat
    {
        $surat = PengajuanSurat::firstOrCreate(
            ['mahasiswa_id' => $mhs->id, 'jenis_surat' => $jenis],
            array_merge(['mahasiswa_id' => $mhs->id, 'jenis_surat' => $jenis], $data)
        );

        foreach ($histories as $h) {
            $this->history($surat, PengajuanSurat::class, $h['dari'], $h['ke'], $h['actor'] ?? null, $h['catatan'] ?? null);
        }

        return $surat;
    }

    // ─── Helper: tambah berkas ke surat ───────────────────────────────────────

    private function tambahBerkasSurat(PengajuanSurat $surat, array $berkas): void
    {
        foreach ($berkas as $label => $namaFile) {
            BerkasPengajuan::firstOrCreate(
                ['pengajuan_type' => PengajuanSurat::class, 'pengajuan_id' => $surat->id, 'label' => $label],
                ['path_file' => "berkas/{$surat->mahasiswa->nim}/demo/{$namaFile}", 'nama_asli' => $namaFile]
            );
        }
    }

    // ─── Helper: catat status history ─────────────────────────────────────────

    private function history(mixed $model, string $class, string $dari, string $ke, ?User $actor, ?string $catatan): void
    {
        if (! $model->id || ! $actor) {
            return;
        }

        StatusHistory::firstOrCreate(
            ['model_type' => $class, 'model_id' => $model->id, 'status_baru' => $ke],
            [
                'status_lama' => $dari,
                'catatan' => $catatan,
                'changed_by' => $actor->id,
                'created_at' => now()->subDays(rand(1, 30)),
            ]
        );
    }
}
