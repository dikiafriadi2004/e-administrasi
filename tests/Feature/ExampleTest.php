<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * Root URL redirect ke login untuk guest.
     */
    public function test_root_redirects_guest_to_login(): void
    {
        $this->get('/')
            ->assertRedirect(route('login'));
    }

    /**
     * Halaman login tersedia.
     */
    public function test_login_page_is_accessible(): void
    {
        $this->get(route('login'))
            ->assertOk();
    }
}
