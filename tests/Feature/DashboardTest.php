<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_view_the_dashboard(): void
    {
        $user = User::factory()->km()->create();

        $this->actingAs($user)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertSee('Hari ini')
            ->assertDontSee('Akun 990000000x')
            ->assertDontSee('Anggota nyata / uji');
    }
}
