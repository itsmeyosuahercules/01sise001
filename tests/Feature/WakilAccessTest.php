<?php

namespace Tests\Feature;

use App\Enums\AbsenceStatus;
use App\Enums\AnnouncementCategory;
use App\Enums\LecturerQuestionStatus;
use App\Enums\UserRole;
use App\Models\AbsenceRequest;
use App\Models\Announcement;
use App\Models\LecturerQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WakilAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_the_class_vice_can_use_officer_tools_except_user_management(): void
    {
        $wakil = User::factory()->wakil()->create();
        $member = User::factory()->anggota()->create();
        $absence = AbsenceRequest::factory()->create();
        $question = LecturerQuestion::factory()->create();

        $this->actingAs($wakil)->get(route('announcements.create'))->assertOk();
        $this->actingAs($wakil)->get(route('message-templates.create'))->assertOk();
        $this->actingAs($wakil)->get(route('absence-requests.export'))->assertOk();
        $this->actingAs($wakil)->get(route('roster.export'))->assertOk();

        $this->actingAs($wakil)
            ->post(route('announcements.store'), [
                'title' => 'Dari wakil',
                'body' => 'Info kelas.',
                'category' => AnnouncementCategory::Umum->value,
            ])
            ->assertRedirect();

        $this->actingAs($wakil)
            ->put(route('absence-requests.update', $absence), [
                'status' => AbsenceStatus::Disetujui->value,
            ])
            ->assertRedirect();

        $this->actingAs($wakil)
            ->patch(route('lecturer-questions.update', $question), [
                'status' => LecturerQuestionStatus::Dipilih->value,
            ])
            ->assertRedirect();

        $this->assertSame(AbsenceStatus::Disetujui, $absence->fresh()->status);
        $this->assertSame(LecturerQuestionStatus::Dipilih, $question->fresh()->status);
        $this->assertDatabaseHas('announcements', [
            'title' => 'Dari wakil',
            'user_id' => $wakil->id,
        ]);

        $this->actingAs($wakil)
            ->get(route('announcements.index'))
            ->assertOk()
            ->assertSee('Tulis pengumuman');

        $this->actingAs($wakil)
            ->get(route('roster.index'))
            ->assertOk()
            ->assertDontSee('Impor NIM')
            ->assertDontSee('Setel sandi');

        $this->actingAs($wakil)->get(route('mahasiswa-imports.create'))->assertForbidden();

        $this->actingAs($wakil)
            ->post(route('mahasiswa-imports.store'), [
                'paste' => "2410112999,Orang Baru\n",
            ])
            ->assertForbidden();

        $this->actingAs($wakil)
            ->patch(route('roster.update', $member), [
                'role' => UserRole::Km->value,
            ])
            ->assertForbidden();

        $this->actingAs($wakil)
            ->patch(route('profiles.password.reset', $member), [
                'password' => 'resetkm88',
                'password_confirmation' => 'resetkm88',
            ])
            ->assertForbidden();

        $this->assertSame(UserRole::Anggota, $member->fresh()->role);
        $this->assertTrue(Hash::check((string) config('kelas.default_password'), $member->fresh()->password));
        $this->assertDatabaseMissing('users', ['nim' => '2410112999']);
    }

    public function test_the_class_rep_can_assign_the_vice_role(): void
    {
        $km = User::factory()->km()->create();
        $member = User::factory()->anggota()->create();

        $this->actingAs($km)
            ->patch(route('roster.update', $member), [
                'role' => UserRole::Wakil->value,
            ])
            ->assertRedirect();

        $this->assertSame(UserRole::Wakil, $member->fresh()->role);
    }

    public function test_the_class_vice_can_delete_any_comment(): void
    {
        $wakil = User::factory()->wakil()->create();
        $comment = Announcement::factory()->create()->comments()->create([
            'user_id' => User::factory()->anggota()->create()->id,
            'body' => 'Komentar anggota.',
        ]);

        $this->actingAs($wakil)
            ->delete(route('announcements.comments.destroy', [$comment->announcement, $comment]))
            ->assertRedirect();

        $this->assertModelMissing($comment);
    }
}
