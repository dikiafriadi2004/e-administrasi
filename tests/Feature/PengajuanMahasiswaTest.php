<?php

namespace Tests\Feature;

use App\Models\Dosen;
use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PengajuanMahasiswaTest extends TestCase
{
    use RefreshDatabase;

    // ─── Helpers ─────────────────────────────────────────────────────────────

    private function buatMahasiswa(): array
    {
        $user = User::factory()->mahasiswa()->create();
        $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id]);

        return [$user, $mahasiswa];
    }

    // ─── Dashboard mahasiswa ──────────────────────────────────────────────────

    public function test_mahasiswa_dashboard_loads(): void
    {
        [$user] = $this->buatMahasiswa();

        $this->actingAs($user)
            ->get(route('mahasiswa.dashboard'))
            ->assertOk();
    }

    // ─── Riwayat ─────────────────────────────────────────────────────────────

    public function test_mahasiswa_can_view_riwayat(): void
    {
        [$user, $mahasiswa] = $this->buatMahasiswa();

        PengajuanJudul::factory()->create(['mahasiswa_id' => $mahasiswa->id]);
        PengajuanSurat::factory()->aktifKuliah()->create(['mahasiswa_id' => $mahasiswa->id]);

        $this->actingAs($user)
            ->get(route('mahasiswa.riwayat.index'))
            ->assertOk();
    }

    // ─── Pengajuan Judul ─────────────────────────────────────────────────────

    public function test_mahasiswa_can_view_form_pengajuan_judul(): void
    {
        [$user] = $this->buatMahasiswa();

        $this->actingAs($user)
            ->get(route('mahasiswa.pengajuan.judul.create'))
            ->assertOk();
    }

    public function test_mahasiswa_with_active_judul_sees_locked_page(): void
    {
        [$user, $mahasiswa] = $this->buatMahasiswa();
        PengajuanJudul::factory()->create(['mahasiswa_id' => $mahasiswa->id, 'status' => 'diajukan']);

        // Dengan pengajuan aktif, controller return view terkunci bukan redirect
        $this->actingAs($user)
            ->get(route('mahasiswa.pengajuan.judul.create'))
            ->assertOk()
            ->assertSee('Tahap Ini Belum Terbuka');
    }

    public function test_mahasiswa_can_view_detail_pengajuan_judul(): void
    {
        [$user, $mahasiswa] = $this->buatMahasiswa();
        $judul = PengajuanJudul::factory()->create(['mahasiswa_id' => $mahasiswa->id]);

        $this->actingAs($user)
            ->get(route('mahasiswa.pengajuan.judul.show', $judul))
            ->assertOk();
    }

    public function test_mahasiswa_cannot_view_other_mahasiswa_judul(): void
    {
        [$userA] = $this->buatMahasiswa();
        [, $mahasiswaB] = $this->buatMahasiswa();

        $judulB = PengajuanJudul::factory()->create(['mahasiswa_id' => $mahasiswaB->id]);

        $this->actingAs($userA)
            ->get(route('mahasiswa.pengajuan.judul.show', $judulB))
            ->assertStatus(403);
    }

    // ─── Pengajuan Aktif Kuliah ───────────────────────────────────────────────

    public function test_mahasiswa_can_view_form_aktif_kuliah(): void
    {
        [$user] = $this->buatMahasiswa();

        $this->actingAs($user)
            ->get(route('mahasiswa.pengajuan.aktif-kuliah.create'))
            ->assertOk();
    }

    // ─── Guard Seminar Proposal ───────────────────────────────────────────────

    public function test_seminar_form_is_locked_without_approved_judul(): void
    {
        [$user] = $this->buatMahasiswa();

        $this->actingAs($user)
            ->get(route('mahasiswa.pengajuan.seminar.create'))
            ->assertOk()
            ->assertSee('Tahap Ini Belum Terbuka');
    }

    public function test_seminar_form_accessible_after_judul_approved(): void
    {
        [$user, $mahasiswa] = $this->buatMahasiswa();
        $dosen = Dosen::factory()->create();

        PengajuanJudul::factory()->disetujui()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'dosen_pembimbing_id' => $dosen->id,
        ]);

        $this->actingAs($user)
            ->get(route('mahasiswa.pengajuan.seminar.create'))
            ->assertOk()
            ->assertDontSee('Tahap Ini Belum Terbuka');
    }

    // ─── Guard Sidang Skripsi ─────────────────────────────────────────────────

    public function test_sidang_form_is_locked_without_seminar_selesai(): void
    {
        [$user, $mahasiswa] = $this->buatMahasiswa();
        $dosen = Dosen::factory()->create();

        // Judul disetujui tapi seminar belum selesai
        PengajuanJudul::factory()->disetujui()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'dosen_pembimbing_id' => $dosen->id,
        ]);

        $this->actingAs($user)
            ->get(route('mahasiswa.pengajuan.sidang.create'))
            ->assertOk()
            ->assertSee('Tahap Ini Belum Terbuka');
    }

    public function test_sidang_form_accessible_after_seminar_selesai(): void
    {
        [$user, $mahasiswa] = $this->buatMahasiswa();
        $dosen = Dosen::factory()->create();

        PengajuanJudul::factory()->disetujui()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'dosen_pembimbing_id' => $dosen->id,
        ]);

        PengajuanSurat::factory()->seminarProposal()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'status' => 'disetujui',
        ]);

        $this->actingAs($user)
            ->get(route('mahasiswa.pengajuan.sidang.create'))
            ->assertOk()
            ->assertDontSee('Tahap Ini Belum Terbuka');
    }

    // ─── Download ────────────────────────────────────────────────────────────

    public function test_mahasiswa_cannot_download_surat_milik_orang_lain(): void
    {
        [$userA] = $this->buatMahasiswa();
        [, $mahasiswaB] = $this->buatMahasiswa();

        $surat = PengajuanSurat::factory()->create([
            'mahasiswa_id' => $mahasiswaB->id,
            'file_docx' => 'surat/99/test.docx',
        ]);

        $this->actingAs($userA)
            ->get(route('mahasiswa.surat.download', [$surat, 'docx']))
            ->assertStatus(403);
    }

    public function test_mahasiswa_cannot_download_docx_after_ditolak(): void
    {
        [$user, $mahasiswa] = $this->buatMahasiswa();

        $surat = PengajuanSurat::factory()->ditolak()->create([
            'mahasiswa_id' => $mahasiswa->id,
            'file_docx' => 'surat/99/test.docx',
            'file_pdf' => 'surat/99/test.pdf',
        ]);

        $this->actingAs($user)
            ->get(route('mahasiswa.surat.download', [$surat, 'docx']))
            ->assertStatus(403);
    }

    // ─── Detail surat ────────────────────────────────────────────────────────

    public function test_mahasiswa_can_view_detail_surat(): void
    {
        [$user, $mahasiswa] = $this->buatMahasiswa();

        $surat = PengajuanSurat::factory()->aktifKuliah()->create([
            'mahasiswa_id' => $mahasiswa->id,
        ]);

        $this->actingAs($user)
            ->get(route('mahasiswa.surat.show', $surat))
            ->assertOk();
    }

    public function test_mahasiswa_cannot_view_detail_surat_milik_orang_lain(): void
    {
        [$userA] = $this->buatMahasiswa();
        [, $mahasiswaB] = $this->buatMahasiswa();

        $surat = PengajuanSurat::factory()->create(['mahasiswa_id' => $mahasiswaB->id]);

        $this->actingAs($userA)
            ->get(route('mahasiswa.surat.show', $surat))
            ->assertStatus(403);
    }
}
