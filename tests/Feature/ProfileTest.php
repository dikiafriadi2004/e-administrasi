<?php

namespace Tests\Feature;

use App\Models\Mahasiswa;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/**
 * ProfileTest — Disesuaikan untuk aplikasi ini.
 * Route /profile bawaan Breeze dihapus karena tidak dipakai.
 * Test ini memverifikasi bahwa user dapat ganti password via route yang ada.
 */
class ProfileTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_is_accessible(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->get(route('admin.dashboard'))
            ->assertOk();
    }

    public function test_kaprodi_dashboard_is_accessible(): void
    {
        $kaprodi = User::factory()->kaprodi()->create();

        $this->actingAs($kaprodi)
            ->get(route('kaprodi.dashboard'))
            ->assertOk();
    }

    public function test_mahasiswa_dashboard_is_accessible(): void
    {
        $user = User::factory()->mahasiswa()->create();
        Mahasiswa::factory()->create(['user_id' => $user->id]);

        $this->actingAs($user)
            ->get(route('mahasiswa.dashboard'))
            ->assertOk();
    }

    public function test_password_can_be_updated(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasNoErrors();

        $this->assertTrue(
            Hash::check('new-password-123', $user->fresh()->password)
        );
    }

    public function test_wrong_current_password_fails_update(): void
    {
        $user = User::factory()->admin()->create();

        $this->actingAs($user)
            ->put(route('password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password-123',
                'password_confirmation' => 'new-password-123',
            ])
            ->assertSessionHasErrorsIn('updatePassword', ['current_password']);
    }
}
