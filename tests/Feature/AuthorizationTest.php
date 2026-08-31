<?php

namespace Tests\Feature;

use App\Enums\AbsenceStatus;
use App\Enums\LecturerQuestionStatus;
use App\Enums\UserRole;
use App\Models\AbsenceRequest;
use App\Models\LecturerQuestion;
use App\Models\MessageTemplate;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_cannot_open_risky_management_pages(): void
    {
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)->get(route('announcements.create'))->assertForbidden();
        $this->actingAs($member)->get(route('mahasiswa-imports.create'))->assertForbidden();
        $this->actingAs($member)->get(route('message-templates.create'))->assertForbidden();
        $this->actingAs($member)->get(route('roster.export'))->assertForbidden();
    }

    public function test_members_cannot_change_roles_or_review_absences(): void
    {
        $member = User::factory()->anggota()->create();
        $other = User::factory()->anggota()->create();
        $absence = AbsenceRequest::factory()->create();

        $this->actingAs($member)
            ->patch(route('roster.update', $other), [
                'role' => UserRole::Km->value,
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->put(route('absence-requests.update', $absence), [
                'status' => AbsenceStatus::Disetujui->value,
            ])
            ->assertForbidden();

        $question = LecturerQuestion::factory()->create();

        $this->actingAs($member)
            ->patch(route('lecturer-questions.update', $question), [
                'status' => LecturerQuestionStatus::Dipilih->value,
            ])
            ->assertForbidden();
    }

    public function test_the_class_rep_can_review_their_own_absence_request(): void
    {
        $km = User::factory()->km()->create();
        $absence = AbsenceRequest::factory()->create([
            'user_id' => $km->id,
        ]);

        $this->actingAs($km)
            ->put(route('absence-requests.update', $absence), [
                'status' => AbsenceStatus::Disetujui->value,
            ])
            ->assertRedirect();

        $this->assertSame(AbsenceStatus::Disetujui, $absence->fresh()->status);
    }

    public function test_members_cannot_delete_message_templates(): void
    {
        $member = User::factory()->anggota()->create();
        $template = MessageTemplate::factory()->create();

        $this->actingAs($member)
            ->delete(route('message-templates.destroy', $template))
            ->assertForbidden();
    }
}
