<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\GameTestHelpers;
use Tests\TestCase;

class AdminAccessTest extends TestCase
{
    use RefreshDatabase;
    use GameTestHelpers;

    public function test_guests_are_redirected_from_admin(): void
    {
        $this->get('/admin')->assertRedirect('/login');
    }

    public function test_regular_players_cannot_reach_admin(): void
    {
        $user = $this->makeUser();

        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get('/admin/cards')->assertForbidden();
    }

    public function test_admins_can_reach_admin(): void
    {
        $admin = $this->makeAdmin();

        $this->actingAs($admin)->get('/admin')->assertOk();
    }

    public function test_banned_users_are_logged_out(): void
    {
        $user = $this->makeUser(['banned_at' => now(), 'ban_reason' => 'triche']);

        // Le middleware de bannissement deconnecte et renvoie au login.
        $this->actingAs($user)->get('/dashboard')->assertRedirect('/login');
        $this->assertGuest();
    }
}
