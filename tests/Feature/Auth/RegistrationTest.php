<?php

namespace Tests\Feature\Auth;

use Tests\TestCase;

/**
 * Self-register dinonaktifkan di aplikasi ini.
 * Akun mahasiswa dibuat oleh Admin secara manual atau via import Excel.
 * Route /register tidak tersedia.
 */
class RegistrationTest extends TestCase
{
    public function test_registration_route_does_not_exist(): void
    {
        $response = $this->get('/register');

        // 404 karena route memang dihapus
        $response->assertStatus(404);
    }
}
