<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\PengajuanSurat;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ProfilEksporTest extends TestCase
{
    use RefreshDatabase;

    // ─── Profil ──────────────────────────────────────────────────────────────

    public function test_admin_can_view_profil(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.profil.show'))
            ->assertOk()
            ->assertSee($admin->name)
            ->assertSee($admin->email);
    }

    public function test_kaprodi_can_view_profil(): void
    {
        $kaprodi = User::factory()->kaprodi()->create();

        $this->actingAs($kaprodi)
            ->get(route('kaprodi.profil.show'))
            ->assertOk()
            ->assertSee($kaprodi->name);
    }

    public function test_mahasiswa_can_view_profil_with_nim(): void
    {
        $user = User::factory()->mahasiswa()->create();
        $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id, 'nim' => '2021001']);

        $this->actingAs($user)
            ->get(route('mahasiswa.profil.show'))
            ->assertOk()
            ->assertSee('2021001');
    }

    public function test_user_can_change_password_from_profil(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'new-secure-pass',
                'password_confirmation' => 'new-secure-pass',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('new-secure-pass', $admin->fresh()->password));
    }

    public function test_profil_requires_authentication(): void
    {
        $this->get(route('admin.profil.show'))
            ->assertRedirect(route('login'));
    }

    // ─── Export arsip ─────────────────────────────────────────────────────────

    public function test_admin_can_export_arsip_excel(): void
    {
        $user = User::factory()->mahasiswa()->create();
        $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id]);

        PengajuanSurat::factory()->aktifKuliah()->create(['mahasiswa_id' => $mahasiswa->id]);
        PengajuanSurat::factory()->seminarProposal()->selesai()->create(['mahasiswa_id' => $mahasiswa->id]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.arsip.export'))
            ->assertOk()
            ->assertHeader(
                'content-type',
                'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
            );
    }

    public function test_export_respects_jenis_filter(): void
    {
        $user = User::factory()->mahasiswa()->create();
        $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id]);

        PengajuanSurat::factory()->aktifKuliah()->create(['mahasiswa_id' => $mahasiswa->id]);
        PengajuanSurat::factory()->seminarProposal()->create(['mahasiswa_id' => $mahasiswa->id]);

        $this->actingAs(User::factory()->admin()->create())
            ->get(route('admin.arsip.export', ['jenis' => 'aktif_kuliah']))
            ->assertOk();
    }

    public function test_non_admin_cannot_export_arsip(): void
    {
        $user = User::factory()->mahasiswa()->create();
        Mahasiswa::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('admin.arsip.export'))
            ->assertStatus(403);
    }
}
