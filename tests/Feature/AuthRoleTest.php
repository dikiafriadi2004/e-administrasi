<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\PengajuanJudul;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthRoleTest extends TestCase
{
    use RefreshDatabase;

    // ─── Login ───────────────────────────────────────────────────────────────

    public function test_mahasiswa_can_login_with_email(): void
    {
        $user = User::factory()->mahasiswa()->create();
        Mahasiswa::factory()->create(['user_id' => $user->id]);

        $this->post('/login', ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('mahasiswa.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_mahasiswa_can_login_with_nim(): void
    {
        $user = User::factory()->mahasiswa()->create();
        $mahasiswa = Mahasiswa::factory()->create(['user_id' => $user->id, 'nim' => '2021999']);

        $this->post('/login', ['email' => $mahasiswa->nim, 'password' => 'password'])
            ->assertRedirect(route('mahasiswa.dashboard'));

        $this->assertAuthenticated();
    }

    public function test_inactive_account_cannot_login(): void
    {
        $user = User::factory()->mahasiswa()->nonaktif()->create();
        Mahasiswa::factory()->create(['user_id' => $user->id]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    public function test_wrong_nim_returns_error(): void
    {
        $this->post('/login', ['email' => '9999999', 'password' => 'password']);

        $this->assertGuest();
    }

    // ─── Redirect setelah login ───────────────────────────────────────────────

    public function test_authenticated_user_visiting_root_is_redirected_to_dashboard(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get('/')
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_guest_visiting_root_is_redirected_to_login(): void
    {
        $this->get('/')->assertRedirect(route('login'));
    }

    // ─── Proteksi route per role ─────────────────────────────────────────────

    public function test_mahasiswa_cannot_access_admin_routes(): void
    {
        $user = User::factory()->mahasiswa()->create();
        Mahasiswa::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('admin.dashboard'))
            ->assertStatus(403);
    }

    public function test_mahasiswa_cannot_access_kaprodi_routes(): void
    {
        $user = User::factory()->mahasiswa()->create();
        Mahasiswa::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)->get(route('kaprodi.dashboard'))
            ->assertStatus(403);
    }

    public function test_admin_cannot_access_kaprodi_routes(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)->get(route('kaprodi.dashboard'))
            ->assertStatus(403);
    }

    public function test_kaprodi_cannot_access_admin_routes(): void
    {
        $kaprodi = User::factory()->kaprodi()->create();

        $this->actingAs($kaprodi)->get(route('admin.dashboard'))
            ->assertStatus(403);
    }

    public function test_guest_accessing_protected_route_is_redirected_to_login(): void
    {
        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));
    }

    // ─── Scoping data mahasiswa ───────────────────────────────────────────────

    public function test_mahasiswa_cannot_view_other_mahasiswa_pengajuan_judul(): void
    {
        $userA = User::factory()->mahasiswa()->create();
        Mahasiswa::factory()->create(['user_id' => $userA->id]);

        $userB = User::factory()->mahasiswa()->create();
        $mhsB = Mahasiswa::factory()->create(['user_id' => $userB->id]);

        $judul = PengajuanJudul::factory()->create([
            'mahasiswa_id' => $mhsB->id,
        ]);

        $this->actingAs($userA)
            ->get(route('mahasiswa.pengajuan.judul.show', $judul))
            ->assertStatus(403);
    }
}
