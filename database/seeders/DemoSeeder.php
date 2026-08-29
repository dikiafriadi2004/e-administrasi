<?php

namespace Database\Seeders;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Models\Pengaturan;
use App\Models\StatusHistory;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * DemoSeeder — data realistis untuk demo / testing manual.
 *
 * Alur bisnis yang berlaku:
 *   - Pengajuan Judul/Seminar/Sidang → Kaprodi (diajukan → disetujui)
 *   - Surat Aktif Kuliah → Admin (diajukan → menunggu_ttd → sudah_ditandatangani → selesai)
 *
 * Jalankan: php artisan db:seed --class=DemoSeeder
 */
class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->updatePengaturan();
        $dosens = $this->seedDosen();
        $mahasiswas = $this->seedMahasiswa();
        $this->seedPengajuan($mahasiswas, $dosens);

        $this->command->info('Demo seeder selesai.');
        $this->command->table(
            ['Entitas', 'Jumlah'],
            [
                ['Dosen',           Dosen::count()],
                ['Mahasiswa',       Mahasiswa::count()],
                ['Pengajuan Judul', PengajuanJudul::count()],
                ['Pengajuan Surat', PengajuanSurat::count()],
            ]
        );
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
            'kode_institusi' => 'UCI',
            'kode_fakultas' => 'FIK',
            'kode_prodi' => 'TI',
        ];

        foreach ($updates as $key => $value) {
            Pengaturan::set($key, $value);
        }

        $this->command->line('  ✓ Pengaturan diperbarui');
    }

    // ─── Dosen ────────────────────────────────────────────────────────────────

    /** @return array<int, Dosen> */
    private function seedDosen(): array
    {
        $dosenData = [
            ['nama' => 'Dr. Ahmad Fauzi, M.Kom.',    'nip' => '197001012000031001', 'kapasitas_maksimal' => 5],
            ['nama' => 'Prof. Budi Raharjo, Ph.D.',  'nip' => '196805152001121002', 'kapasitas_maksimal' => null],
            ['nama' => 'Dr. Citra Dewi, M.T.',       'nip' => '198203202010012003', 'kapasitas_maksimal' => 4],
            ['nama' => 'Drs. Eko Prasetyo, M.Si.',   'nip' => '197512102005011004', 'kapasitas_maksimal' => null],
            ['nama' => 'Dr. Fitri Handayani, M.Cs.', 'nip' => '198907252015042005', 'kapasitas_maksimal' => 6],
            ['nama' => 'Dr. Gilang Permana, M.T.',   'nip' => '198001012008011006', 'kapasitas_maksimal' => 5],
            ['nama' => 'Hendra Kusuma, M.Kom.',       'nip' => '199002152018021007', 'kapasitas_maksimal' => null],
        ];

        $result = [];
        foreach ($dosenData as $d) {
            $result[] = Dosen::firstOrCreate(['nip' => $d['nip']], $d);
        }

        $this->command->line('  ✓ '.count($result).' dosen tersedia');

        return $result;
    }

    // ─── Mahasiswa ────────────────────────────────────────────────────────────

    /** @return array<int, Mahasiswa> */
    private function seedMahasiswa(): array
    {
        $data = [
            ['nim' => '2020001', 'nama' => 'Andi Pratama',    'email' => 'andi@mhs.contoh.ac.id',  'angkatan' => 2020],
            ['nim' => '2020002', 'nama' => 'Siti Rahayu',     'email' => 'siti@mhs.contoh.ac.id',  'angkatan' => 2020],
            ['nim' => '2020003', 'nama' => 'Budi Cahyono',    'email' => 'budi@mhs.contoh.ac.id',  'angkatan' => 2020],
            ['nim' => '2021001', 'nama' => 'Dewi Lestari',    'email' => 'dewi@mhs.contoh.ac.id',  'angkatan' => 2021],
            ['nim' => '2021002', 'nama' => 'Eko Setiawan',    'email' => 'eko@mhs.contoh.ac.id',   'angkatan' => 2021],
            ['nim' => '2021003', 'nama' => 'Fajar Nugroho',   'email' => 'fajar@mhs.contoh.ac.id', 'angkatan' => 2021],
            ['nim' => '2022001', 'nama' => 'Galih Wicaksono', 'email' => 'galih@mhs.contoh.ac.id', 'angkatan' => 2022],
            ['nim' => '2022002', 'nama' => 'Hana Pertiwi',    'email' => 'hana@mhs.contoh.ac.id',  'angkatan' => 2022],
            ['nim' => '2022003', 'nama' => 'Irfan Maulana',   'email' => 'irfan@mhs.contoh.ac.id', 'angkatan' => 2022],
            ['nim' => '2023001', 'nama' => 'Joko Susilo',     'email' => 'joko@mhs.contoh.ac.id',  'angkatan' => 2023],
        ];

        $result = [];
        foreach ($data as $d) {
            $user = User::firstOrCreate(
                ['email' => $d['email']],
                [
                    'name' => $d['nama'],
                    'password' => Hash::make($d['nim']),
                    'role' => 'mahasiswa',
                    'is_active' => true,
                ]
            );
            $mhs = Mahasiswa::firstOrCreate(
                ['nim' => $d['nim']],
                ['user_id' => $user->id, 'angkatan' => $d['angkatan']]
            );
            $result[] = $mhs->load('user');
        }

        $this->command->line('  ✓ '.count($result).' mahasiswa tersedia');

        return $result;
    }

    // ─── Pengajuan ────────────────────────────────────────────────────────────

    private function seedPengajuan(array $mahasiswas, array $dosens): void
    {
        $kaprodi = User::where('role', 'kaprodi')->first();
        $admin = User::where('role', 'admin')->first();

        // ── 1. ANDI: Alur akademik lengkap — judul ✓, seminar ✓, sidang ✓ ─────
        // (Kaprodi yang setujui, bukan admin)
        $judulAndi = PengajuanJudul::firstOrCreate(
            ['mahasiswa_id' => $mahasiswas[0]->id, 'judul' => 'Sistem Deteksi Plagiarisme Berbasis NLP untuk Tugas Akhir Mahasiswa'],
            [
                'bidang_kajian' => 'Kecerdasan Buatan',
                'ringkasan' => 'Penelitian mengembangkan sistem deteksi plagiarisme menggunakan Natural Language Processing untuk membantu penilaian tugas akhir mahasiswa secara otomatis.',
                'dosen_pembimbing_id' => $dosens[0]->id,
                'status' => 'disetujui',
            ]
        );
        $this->catatHistory($judulAndi, PengajuanJudul::class, 'diajukan', 'disetujui', $kaprodi, 'Judul disetujui dan pembimbing ditetapkan.');

        // Seminar disetujui Kaprodi (tidak ada surat — alur baru)
        $seminarAndi = PengajuanSurat::firstOrCreate(
            ['mahasiswa_id' => $mahasiswas[0]->id, 'jenis_surat' => 'seminar_proposal'],
            [
                'pengajuan_judul_id' => $judulAndi->id,
                'data_form' => ['tanggal_rencana' => '2026-03-15'],
                'status' => 'disetujui',
            ]
        );
        $this->catatHistory($seminarAndi, PengajuanSurat::class, 'diajukan', 'disetujui', $kaprodi, 'Seminar proposal disetujui.');

        // Sidang disetujui Kaprodi, penguji ditetapkan
        $sidangAndi = PengajuanSurat::firstOrCreate(
            ['mahasiswa_id' => $mahasiswas[0]->id, 'jenis_surat' => 'sidang_skripsi'],
            [
                'pengajuan_judul_id' => $judulAndi->id,
                'data_form' => [
                    'tanggal_rencana' => '2026-08-20',
                    'waktu_rencana' => '09.00 WIB',
                    'tempat' => 'Ruang Sidang A Lt. 3',
                ],
                'dosen_penguji_id' => $dosens[1]->id,
                'status' => 'disetujui',
            ]
        );
        $this->catatHistory($sidangAndi, PengajuanSurat::class, 'diajukan', 'disetujui', $kaprodi, 'Sidang disetujui dan penguji ditetapkan.');

        // ── 2. SITI: Judul disetujui, seminar baru diajukan ke Kaprodi ────────
        $judulSiti = PengajuanJudul::firstOrCreate(
            ['mahasiswa_id' => $mahasiswas[1]->id, 'judul' => 'Aplikasi Monitoring Kehadiran Mahasiswa Berbasis QR Code dan Geolokasi'],
            [
                'bidang_kajian' => 'Rekayasa Perangkat Lunak',
                'ringkasan' => 'Pengembangan aplikasi mobile untuk monitoring kehadiran perkuliahan secara real-time menggunakan QR Code dan validasi geolokasi kampus.',
                'dosen_pembimbing_id' => $dosens[2]->id,
                'status' => 'disetujui',
            ]
        );

        PengajuanSurat::firstOrCreate(
            ['mahasiswa_id' => $mahasiswas[1]->id, 'jenis_surat' => 'seminar_proposal'],
            [
                'pengajuan_judul_id' => $judulSiti->id,
                'data_form' => ['tanggal_rencana' => '2026-09-10'],
                'status' => 'diajukan',  // menunggu keputusan Kaprodi
            ]
        );

        // ── 3. BUDI: Judul baru diajukan ke Kaprodi ───────────────────────────
        PengajuanJudul::firstOrCreate(
            ['mahasiswa_id' => $mahasiswas[2]->id, 'judul' => 'Analisis Sentimen Review Produk E-commerce Menggunakan Deep Learning'],
            [
                'bidang_kajian' => 'Kecerdasan Buatan',
                'ringkasan' => 'Membangun model analisis sentimen review produk e-commerce Indonesia menggunakan LSTM dan BERT.',
                'status' => 'diajukan',
            ]
        );

        // ── 4. DEWI: Judul baru diajukan ke Kaprodi ───────────────────────────
        PengajuanJudul::firstOrCreate(
            ['mahasiswa_id' => $mahasiswas[3]->id, 'judul' => 'Sistem Rekomendasi Buku Perpustakaan Digital Berbasis Collaborative Filtering'],
            [
                'bidang_kajian' => 'Sistem Informasi',
                'ringkasan' => 'Membangun sistem rekomendasi buku perpustakaan digital menggunakan collaborative filtering.',
                'status' => 'diajukan',
            ]
        );

        // ── 5. EKO: Judul ditolak Kaprodi ─────────────────────────────────────
        $judulEko = PengajuanJudul::firstOrCreate(
            ['mahasiswa_id' => $mahasiswas[4]->id, 'judul' => 'Website Toko Online Sederhana'],
            [
                'bidang_kajian' => 'Rekayasa Perangkat Lunak',
                'ringkasan' => 'Membuat website toko online menggunakan PHP dan MySQL.',
                'status' => 'ditolak',
                'catatan_penolakan' => 'Judul terlalu umum, tidak ada kontribusi ilmiah yang jelas. Tambahkan metode/pendekatan spesifik.',
            ]
        );
        $this->catatHistory($judulEko, PengajuanJudul::class, 'diajukan', 'ditolak', $kaprodi, 'Judul terlalu umum.');

        // ── 6. FAJAR: Surat Aktif Kuliah — satu selesai, satu baru diajukan ───
        // Surat selesai (sudah dapat download scan)
        PengajuanSurat::firstOrCreate(
            ['mahasiswa_id' => $mahasiswas[5]->id, 'jenis_surat' => 'aktif_kuliah', 'nomor_surat' => '003/UCI/FIK/TI/VII/2026'],
            [
                'data_form' => ['keperluan' => 'pengajuan beasiswa prestasi', 'tujuan_instansi' => 'Dikti Kemdikbud'],
                'status' => 'selesai',
                'file_scan' => 'surat/demo/aktif_kuliah_fajar.pdf',
                'generated_at' => now()->subDays(10),
            ]
        );

        // Surat baru diajukan (masuk antrian admin)
        PengajuanSurat::firstOrCreate(
            ['mahasiswa_id' => $mahasiswas[5]->id, 'jenis_surat' => 'aktif_kuliah', 'nomor_surat' => null],
            [
                'data_form' => ['keperluan' => 'keperluan magang industri', 'tujuan_instansi' => 'PT. Teknologi Maju Indonesia'],
                'status' => 'diajukan',
            ]
        );

        // ── 7. GALIH: Surat Aktif Kuliah — sudah generate, menunggu scan TTD ──
        PengajuanSurat::firstOrCreate(
            ['mahasiswa_id' => $mahasiswas[6]->id, 'jenis_surat' => 'aktif_kuliah'],
            [
                'data_form' => ['keperluan' => 'pendaftaran lomba nasional', 'tujuan_instansi' => 'Panitia Gemastik 2026'],
                'nomor_surat' => '004/UCI/FIK/TI/VIII/2026',
                'status' => 'menunggu_ttd',
                'file_docx' => 'surat/demo/aktif_galih.docx',
                'file_pdf' => 'surat/demo/aktif_galih.pdf',
                'generated_at' => now()->subDays(2),
            ]
        );

        // ── 8–10. HANA, IRFAN, JOKO: belum ada pengajuan apapun ──────────────

        $this->command->line('  ✓ Pengajuan demo dibuat dalam berbagai status');
    }

    // ─── Helper ───────────────────────────────────────────────────────────────

    private function catatHistory(
        mixed $model,
        string $modelClass,
        string $dari,
        string $ke,
        ?User $actor,
        ?string $catatan = null
    ): void {
        if (! $actor || ! $model->id) {
            return;
        }

        $sudahAda = StatusHistory::where('model_type', $modelClass)
            ->where('model_id', $model->id)
            ->where('status_baru', $ke)
            ->exists();

        if (! $sudahAda) {
            StatusHistory::create([
                'model_type' => $modelClass,
                'model_id' => $model->id,
                'status_lama' => $dari,
                'status_baru' => $ke,
                'catatan' => $catatan,
                'changed_by' => $actor->id,
                'created_at' => now()->subDays(rand(1, 30)),
            ]);
        }
    }
}
