<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class PasswordTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_member_can_change_their_own_password(): void
    {
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->from(route('profiles.edit'))
            ->patch(route('profiles.password'), [
                'current_password' => config('kelas.default_password'),
                'password' => 'sandibaru1',
                'password_confirmation' => 'sandibaru1',
            ])
            ->assertRedirect(route('profiles.edit'))
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('sandibaru1', $member->fresh()->password));
    }

    public function test_changing_own_password_requires_the_current_password(): void
    {
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->from(route('profiles.edit'))
            ->patch(route('profiles.password'), [
                'current_password' => 'salahsekali',
                'password' => 'sandibaru1',
                'password_confirmation' => 'sandibaru1',
            ])
            ->assertRedirect(route('profiles.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(Hash::check((string) config('kelas.default_password'), $member->fresh()->password));
    }

    public function test_the_class_rep_can_set_a_members_password(): void
    {
        $km = User::factory()->km()->create();
        $member = User::factory()->anggota()->create([
            'name' => 'Siti Aminah',
        ]);

        $this->actingAs($km)
            ->get(route('profiles.show', $member))
            ->assertOk()
            ->assertSee('Setel kata sandi');

        $this->actingAs($km)
            ->from(route('profiles.show', $member))
            ->patch(route('profiles.password.reset', $member), [
                'password' => 'resetkm88',
                'password_confirmation' => 'resetkm88',
            ])
            ->assertRedirect()
            ->assertSessionHas('status');

        $this->assertTrue(Hash::check('resetkm88', $member->fresh()->password));
    }

    public function test_a_member_cannot_set_someone_elses_password(): void
    {
        $member = User::factory()->anggota()->create();
        $other = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->get(route('profiles.show', $other))
            ->assertOk()
            ->assertDontSee('Setel kata sandi');

        $this->actingAs($member)
            ->patch(route('profiles.password.reset', $other), [
                'password' => 'resetkm88',
                'password_confirmation' => 'resetkm88',
            ])
            ->assertForbidden();

        $this->assertTrue(Hash::check((string) config('kelas.default_password'), $other->fresh()->password));
    }

    public function test_the_roster_shows_password_links_only_to_the_class_rep(): void
    {
        $km = User::factory()->km()->create();
        $member = User::factory()->anggota()->create([
            'name' => 'Aida Kamila',
        ]);

        $this->actingAs($km)
            ->get(route('roster.index'))
            ->assertOk()
            ->assertSee('Setel sandi')
            ->assertSee(route('profiles.show', $member).'#sandi', false);

        $this->actingAs($member)
            ->get(route('roster.index'))
            ->assertOk()
            ->assertDontSee('Setel sandi');
    }
}
