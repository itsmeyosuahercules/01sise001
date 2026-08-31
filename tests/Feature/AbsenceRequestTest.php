<?php

namespace Tests\Feature;

use App\Enums\AbsenceStatus;
use App\Enums\AbsenceType;
use App\Models\AbsenceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AbsenceRequestTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_can_submit_an_absence_request(): void
    {
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->post(route('absence-requests.store'), [
                'type' => AbsenceType::Sakit->value,
                'starts_on' => '2026-09-01',
                'ends_on' => '2026-09-02',
                'reason' => 'Demam.',
            ])
            ->assertRedirect(route('absence-requests.index'));

        $this->assertDatabaseHas('absence_requests', [
            'user_id' => $member->id,
            'reason' => 'Demam.',
            'status' => AbsenceStatus::Pending->value,
        ]);
    }

    public function test_members_cannot_review_absence_requests(): void
    {
        $member = User::factory()->anggota()->create();
        $absence = AbsenceRequest::factory()->create([
            'user_id' => $member->id,
            'reason' => 'Izin anggota',
        ]);

        $this->actingAs($member)
            ->get(route('absence-requests.index'))
            ->assertOk()
            ->assertSee('Izin anggota')
            ->assertDontSee('Setujui')
            ->assertDontSee('Tolak');

        $this->actingAs($member)
            ->put(route('absence-requests.update', $absence), [
                'status' => AbsenceStatus::Disetujui->value,
            ])
            ->assertForbidden();
    }

    public function test_the_class_rep_sees_review_actions_on_pending_requests(): void
    {
        $km = User::factory()->km()->create();
        AbsenceRequest::factory()->create([
            'user_id' => $km->id,
            'reason' => 'Izin KM',
        ]);

        $this->actingAs($km)
            ->get(route('absence-requests.index'))
            ->assertOk()
            ->assertSee('Izin KM')
            ->assertSee('Setujui')
            ->assertSee('Tolak');
    }

    public function test_the_class_rep_can_approve_an_absence_request(): void
    {
        $km = User::factory()->km()->create();
        $absence = AbsenceRequest::factory()->create();

        $this->actingAs($km)
            ->put(route('absence-requests.update', $absence), [
                'status' => AbsenceStatus::Disetujui->value,
            ])
            ->assertRedirect();

        $absence->refresh();

        $this->assertSame(AbsenceStatus::Disetujui, $absence->status);
        $this->assertTrue($km->is($absence->reviewer));
    }

    public function test_reviewing_an_absence_can_return_json_without_a_redirect(): void
    {
        $km = User::factory()->km()->create();
        $absence = AbsenceRequest::factory()->create();

        $this->actingAs($km)
            ->putJson(route('absence-requests.update', $absence), [
                'status' => AbsenceStatus::Ditolak->value,
            ])
            ->assertOk()
            ->assertJsonPath('status', AbsenceStatus::Ditolak->value);
    }

    public function test_members_only_see_their_own_absence_requests(): void
    {
        $member = User::factory()->anggota()->create();
        $other = AbsenceRequest::factory()->create([
            'reason' => 'Izin orang lain',
        ]);
        AbsenceRequest::factory()->create([
            'user_id' => $member->id,
            'reason' => 'Izin saya',
        ]);

        $this->actingAs($member)
            ->get(route('absence-requests.index'))
            ->assertOk()
            ->assertSee('Izin saya')
            ->assertDontSee('Izin orang lain');

        $this->assertNotNull($other->id);
    }

    public function test_members_cannot_export_absence_recap(): void
    {
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->get(route('absence-requests.export'))
            ->assertForbidden();
    }

    public function test_officers_can_export_absence_recap(): void
    {
        $km = User::factory()->km()->create();
        AbsenceRequest::factory()->create([
            'reason' => 'Sakit demam',
        ]);

        $response = $this->actingAs($km)->get(route('absence-requests.export'));

        $response->assertOk();
        $this->assertStringContainsString('Sakit demam', $response->streamedContent());
    }
}
