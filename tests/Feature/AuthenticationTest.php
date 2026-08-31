<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_login_page_is_displayed(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk()
            ->assertDontSee('9900000001')
            ->assertDontSee('Akun uji');
    }

    public function test_users_can_authenticate_with_nim(): void
    {
        $user = User::factory()->create([
            'nim' => '2410112999',
        ]);

        $response = $this->post(route('login'), [
            'nim' => '2410112999',
            'password' => config('kelas.default_password'),
        ]);

        $this->assertAuthenticatedAs($user);
        $response->assertRedirect(route('dashboard'));
    }

    public function test_users_cannot_authenticate_with_an_invalid_password(): void
    {
        User::factory()->create([
            'nim' => '2410112999',
        ]);

        $this->post(route('login'), [
            'nim' => '2410112999',
            'password' => 'salah',
        ]);

        $this->assertGuest();
    }

    public function test_users_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('logout'));

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_guests_are_redirected_from_the_dashboard(): void
    {
        $this->get(route('dashboard'))->assertRedirect(route('login'));
    }
}
