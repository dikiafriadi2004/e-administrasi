<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Models\StatusHistory;
use App\Models\User;
use Database\Seeders\PengaturanSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Tes alur verifikasi sesuai alur bisnis baru:
 *
 * PENGAJUAN AKADEMIK (judul/seminar/sidang) → langsung ke Kaprodi
 *   diajukan → disetujui (atau ditolak) — tidak ada diverifikasi
 *
 * SURAT (aktif kuliah, dll) → Admin
 *   diajukan → (admin generate) → menunggu_ttd → sudah_ditandatangani → selesai
 */
class VerifikasiAdminTest extends TestCase
{
    use RefreshDatabase;

    private function buatAdmin(): User
    {
        return User::factory()->admin()->create();
    }

    private function buatKaprodi(): User
    {
        return User::factory()->kaprodi()->create();
    }

    private function buatPengajuanSurat(string $status = 'diajukan'): PengajuanSurat
    {
        $user = User::factory()->mahasiswa()->create();
        $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id]);

        return PengajuanSurat::factory()->aktifKuliah()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'status' => $status,
        ]);
    }

    private function buatPengajuanJudul(string $status = 'diajukan'): PengajuanJudul
    {
        $user = User::factory()->mahasiswa()->create();
        $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id]);

        return PengajuanJudul::factory()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'status' => $status,
        ]);
    }

    // ─── Admin: Antrian Surat ──────────────────────────────────────────────

    public function test_admin_can_view_antrian_surat(): void
    {
        $this->actingAs($this->buatAdmin())
            ->get(route('admin.surat.index'))
            ->assertOk();
    }

    public function test_admin_can_view_detail_surat(): void
    {
        $surat = $this->buatPengajuanSurat();

        $this->actingAs($this->buatAdmin())
            ->get(route('admin.surat.show', $surat))
            ->assertOk();
    }

    public function test_admin_can_tolak_surat_with_catatan(): void
    {
        $admin = $this->buatAdmin();
        $surat = $this->buatPengajuanSurat('diajukan');

        $this->actingAs($admin)
            ->post(route('admin.surat.tolak', $surat), [
                'catatan_penolakan' => 'Keperluan surat tidak lengkap.',
            ])
            ->assertRedirect(route('admin.surat.index'));

        $this->assertDatabaseHas('pengajuan_surat', [
            'id' => $surat->id,
            'status' => 'ditolak',
            'catatan_penolakan' => 'Keperluan surat tidak lengkap.',
        ]);
    }

    public function test_admin_tolak_surat_without_catatan_fails(): void
    {
        $surat = $this->buatPengajuanSurat('diajukan');

        $this->actingAs($this->buatAdmin())
            ->post(route('admin.surat.tolak', $surat), ['catatan_penolakan' => ''])
            ->assertSessionHasErrors('catatan_penolakan');

        $this->assertDatabaseHas('pengajuan_surat', ['id' => $surat->id, 'status' => 'diajukan']);
    }

    public function test_admin_can_selesaikan_surat_sudah_ditandatangani(): void
    {
        $admin = $this->buatAdmin();
        $surat = $this->buatPengajuanSurat('sudah_ditandatangani');

        $this->actingAs($admin)
            ->post(route('admin.surat.selesaikan', $surat))
            ->assertRedirect();

        $this->assertDatabaseHas('pengajuan_surat', ['id' => $surat->id, 'status' => 'selesai']);
    }

    public function test_admin_cannot_selesaikan_surat_belum_ditandatangani(): void
    {
        $surat = $this->buatPengajuanSurat('menunggu_ttd');

        $this->actingAs($this->buatAdmin())
            ->post(route('admin.surat.selesaikan', $surat))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('pengajuan_surat', ['id' => $surat->id, 'status' => 'menunggu_ttd']);
    }

    // ─── Kaprodi: Antrian Akademik ────────────────────────────────────────

    public function test_kaprodi_can_view_antrian_akademik(): void
    {
        $this->actingAs($this->buatKaprodi())
            ->get(route('kaprodi.akademik.index'))
            ->assertOk();
    }

    public function test_kaprodi_can_view_detail_judul(): void
    {
        $judul = $this->buatPengajuanJudul();

        $this->actingAs($this->buatKaprodi())
            ->get(route('kaprodi.akademik.judul.show', $judul))
            ->assertOk();
    }

    public function test_kaprodi_can_setujui_judul_with_pembimbing(): void
    {
        $kaprodi = $this->buatKaprodi();
        $dosen = Dosen::factory()->create();
        $judul = $this->buatPengajuanJudul('diajukan');

        $this->actingAs($kaprodi)
            ->post(route('kaprodi.akademik.judul.setujui', $judul), [
                'dosen_pembimbing_id' => $dosen->id,
            ])
            ->assertRedirect(route('kaprodi.akademik.index'));

        $this->assertDatabaseHas('pengajuan_judul', [
            'id' => $judul->id,
            'status' => 'disetujui',
            'dosen_pembimbing_id' => $dosen->id,
        ]);
    }

    public function test_kaprodi_cannot_setujui_judul_without_pembimbing(): void
    {
        $judul = $this->buatPengajuanJudul('diajukan');

        $this->actingAs($this->buatKaprodi())
            ->post(route('kaprodi.akademik.judul.setujui', $judul), [
                'dosen_pembimbing_id' => '',
            ])
            ->assertSessionHasErrors('dosen_pembimbing_id');

        $this->assertDatabaseHas('pengajuan_judul', ['id' => $judul->id, 'status' => 'diajukan']);
    }

    public function test_kaprodi_can_tolak_judul(): void
    {
        $judul = $this->buatPengajuanJudul('diajukan');

        $this->actingAs($this->buatKaprodi())
            ->post(route('kaprodi.akademik.judul.tolak', $judul), [
                'catatan_penolakan' => 'Judul terlalu umum, perlu metode yang lebih spesifik.',
            ])
            ->assertRedirect(route('kaprodi.akademik.index'));

        $this->assertDatabaseHas('pengajuan_judul', ['id' => $judul->id, 'status' => 'ditolak']);
    }

    public function test_kaprodi_can_setujui_seminar(): void
    {
        $user = User::factory()->mahasiswa()->create();
        $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id]);

        $surat = PengajuanSurat::factory()->seminarProposal()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'status' => 'diajukan',
        ]);

        $this->actingAs($this->buatKaprodi())
            ->post(route('kaprodi.akademik.seminar.setujui', $surat), [
                'tanggal_jadwal' => now()->addWeekdays(8)->format('Y-m-d'),
                'waktu_jadwal' => '10.00 s/d selesai',
                'tempat_jadwal' => 'Ruang 01.03',
            ])
            ->assertRedirect(route('kaprodi.akademik.index'));

        $this->assertDatabaseHas('pengajuan_surat', ['id' => $surat->id, 'status' => 'disetujui']);
    }

    public function test_kaprodi_can_setujui_sidang_with_penguji(): void
    {
        $user = User::factory()->mahasiswa()->create();
        $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id]);
        $dosen = Dosen::factory()->create();

        $surat = PengajuanSurat::factory()->sidangSkripsi()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'status' => 'diajukan',
        ]);

        $this->actingAs($this->buatKaprodi())
            ->post(route('kaprodi.akademik.sidang.setujui', $surat), [
                'dosen_penguji_id' => $dosen->id,
                'tanggal_jadwal' => now()->addWeekdays(8)->format('Y-m-d'),
                'waktu_jadwal' => '09.00 WIB',
                'tempat_jadwal' => 'Ruang Sidang A',
            ])
            ->assertRedirect(route('kaprodi.akademik.index'));

        $this->assertDatabaseHas('pengajuan_surat', [
            'id' => $surat->id,
            'status' => 'disetujui',
            'dosen_penguji_id' => $dosen->id,
        ]);
    }

    // ─── Status history tercatat ──────────────────────────────────────────

    public function test_status_history_recorded_on_state_transition(): void
    {
        $kaprodi = $this->buatKaprodi();
        $dosen = Dosen::factory()->create();
        $judul = $this->buatPengajuanJudul('diajukan');

        $this->actingAs($kaprodi)
            ->post(route('kaprodi.akademik.judul.setujui', $judul), [
                'dosen_pembimbing_id' => $dosen->id,
            ]);

        $this->assertEquals(
            1,
            StatusHistory::where('model_id', $judul->id)
                ->where('model_type', PengajuanJudul::class)
                ->where('status_baru', 'disetujui')
                ->count()
        );
    }

    // ─── Dashboard & Arsip ───────────────────────────────────────────────

    public function test_admin_can_view_dashboard(): void
    {
        $this->actingAs($this->buatAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_admin_can_view_dashboard_rasio(): void
    {
        $this->actingAs($this->buatAdmin())
            ->get(route('admin.dashboard.rasio'))
            ->assertOk();
    }

    public function test_kaprodi_can_view_dashboard_rasio(): void
    {
        $this->actingAs($this->buatKaprodi())
            ->get(route('kaprodi.dashboard.rasio'))
            ->assertOk();
    }

    public function test_admin_can_view_arsip(): void
    {
        $this->actingAs($this->buatAdmin())
            ->get(route('admin.arsip.index'))
            ->assertOk();
    }

    public function test_admin_can_view_pengaturan(): void
    {
        $this->actingAs($this->buatAdmin())
            ->get(route('admin.pengaturan.index'))
            ->assertOk();
    }

    public function test_admin_can_update_pengaturan(): void
    {
        (new PengaturanSeeder)->run();
        $admin = $this->buatAdmin();

        $this->actingAs($admin)
            ->put(route('admin.pengaturan.update'), [
                'nama_kaprodi' => 'Dr. Test Kaprodi, M.Kom.',
                'nip_kaprodi' => '197001011990011001',
            ])
            ->assertRedirect(route('admin.pengaturan.index'));

        $this->assertDatabaseHas('pengaturan', [
            'key' => 'nama_kaprodi',
            'value' => 'Dr. Test Kaprodi, M.Kom.',
        ]);
    }
}
