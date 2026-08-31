<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RosterTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_can_view_the_roster(): void
    {
        $member = User::factory()->anggota()->create([
            'name' => 'Anggota Terlihat',
        ]);

        $this->actingAs($member)
            ->get(route('roster.index'))
            ->assertOk()
            ->assertSee('Anggota Terlihat')
            ->assertDontSee('Impor NIM');
    }

    public function test_km_can_promote_a_member_to_km(): void
    {
        $km = User::factory()->km()->create();
        $member = User::factory()->anggota()->create();

        $this->actingAs($km)
            ->patch(route('roster.update', $member), [
                'role' => UserRole::Km->value,
            ])
            ->assertRedirect();

        $this->assertSame(UserRole::Km, $member->fresh()->role);
    }

    public function test_changing_a_role_can_return_json_without_a_redirect(): void
    {
        $km = User::factory()->km()->create();
        $member = User::factory()->anggota()->create();

        $this->actingAs($km)
            ->patchJson(route('roster.update', $member), [
                'role' => UserRole::Km->value,
            ])
            ->assertOk()
            ->assertJsonPath('role', UserRole::Km->value);

        $this->assertSame(UserRole::Km, $member->fresh()->role);
    }

    public function test_unknown_roles_cannot_be_assigned(): void
    {
        $km = User::factory()->km()->create();
        $member = User::factory()->anggota()->create();

        $this->actingAs($km)
            ->from(route('roster.index'))
            ->patch(route('roster.update', $member), [
                'role' => 'sekretaris',
            ])
            ->assertRedirect(route('roster.index'))
            ->assertSessionHasErrors('role');

        $this->assertSame(UserRole::Anggota, $member->fresh()->role);
    }

    public function test_members_cannot_change_roles(): void
    {
        $member = User::factory()->anggota()->create();
        $other = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->patch(route('roster.update', $other), [
                'role' => UserRole::Km->value,
            ])
            ->assertForbidden();
    }

    public function test_the_last_km_cannot_be_demoted(): void
    {
        $km = User::factory()->km()->create();

        $this->actingAs($km)
            ->from(route('roster.index'))
            ->patch(route('roster.update', $km), [
                'role' => UserRole::Anggota->value,
            ])
            ->assertRedirect(route('roster.index'))
            ->assertSessionHasErrors('role');

        $this->assertSame(UserRole::Km, $km->fresh()->role);
    }

    public function test_officers_can_export_the_roster(): void
    {
        $km = User::factory()->km()->create([
            'nim' => '2410112888',
            'name' => 'Export Target',
        ]);

        $response = $this->actingAs($km)->get(route('roster.export'));

        $response->assertOk();
        $this->assertStringContainsString('2410112888', $response->streamedContent());
        $this->assertStringContainsString('Export Target', $response->streamedContent());
    }
}
