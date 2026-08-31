<?php

namespace Tests\Feature;

use App\Enums\LecturerQuestionKind;
use App\Enums\LecturerQuestionStatus;
use App\Models\LecturerQuestion;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LecturerQuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_members_can_submit_a_question_for_the_class_rep(): void
    {
        $member = User::factory()->anggota()->create();

        $this->actingAs($member)
            ->post(route('lecturer-questions.store'), [
                'kind' => LecturerQuestionKind::Pertanyaan->value,
                'topic' => 'Pak Andi, Basis Data',
                'body' => 'Apakah UTS open book?',
                'hide_name' => '1',
            ])
            ->assertRedirect(route('lecturer-questions.index'));

        $this->assertDatabaseHas('lecturer_questions', [
            'user_id' => $member->id,
            'topic' => 'Pak Andi, Basis Data',
            'body' => 'Apakah UTS open book?',
            'hide_name' => 1,
            'status' => LecturerQuestionStatus::Baru->value,
        ]);
    }

    public function test_members_only_see_their_own_questions(): void
    {
        $member = User::factory()->anggota()->create();
        LecturerQuestion::factory()->create([
            'body' => 'Pertanyaan orang lain',
        ]);
        LecturerQuestion::factory()->create([
            'user_id' => $member->id,
            'body' => 'Pertanyaan saya',
        ]);

        $this->actingAs($member)
            ->get(route('lecturer-questions.index'))
            ->assertOk()
            ->assertSee('Pertanyaan saya')
            ->assertDontSee('Pertanyaan orang lain')
            ->assertDontSee('Paket siap tempel WA');
    }

    public function test_the_class_rep_can_see_every_question(): void
    {
        $km = User::factory()->km()->create();
        LecturerQuestion::factory()->create([
            'body' => 'Pertanyaan dari anggota',
        ]);

        $this->actingAs($km)
            ->get(route('lecturer-questions.index'))
            ->assertOk()
            ->assertSee('Pertanyaan dari anggota')
            ->assertSee('Paket siap tempel WA');
    }

    public function test_members_cannot_curate_questions(): void
    {
        $member = User::factory()->anggota()->create();
        $question = LecturerQuestion::factory()->create();

        $this->actingAs($member)
            ->patch(route('lecturer-questions.update', $question), [
                'status' => LecturerQuestionStatus::Dipilih->value,
            ])
            ->assertForbidden();

        $this->actingAs($member)
            ->post(route('lecturer-questions.package'))
            ->assertForbidden();
    }

    public function test_the_class_rep_can_add_a_question_to_the_package(): void
    {
        $km = User::factory()->km()->create();
        $question = LecturerQuestion::factory()->create([
            'topic' => 'Basis Data',
            'body' => 'Jam berapa UTS dimulai?',
        ]);

        $this->actingAs($km)
            ->patch(route('lecturer-questions.update', $question), [
                'status' => LecturerQuestionStatus::Dipilih->value,
            ])
            ->assertRedirect();

        $this->assertSame(LecturerQuestionStatus::Dipilih, $question->fresh()->status);
        $this->assertTrue($km->is($question->fresh()->curator));
    }

    public function test_curating_a_question_can_return_json_without_a_redirect(): void
    {
        $km = User::factory()->km()->create();
        $question = LecturerQuestion::factory()->create();

        $this->actingAs($km)
            ->patchJson(route('lecturer-questions.update', $question), [
                'status' => LecturerQuestionStatus::Ditahan->value,
            ])
            ->assertOk()
            ->assertJsonPath('status', LecturerQuestionStatus::Ditahan->value)
            ->assertJsonPath('selected_count', 0);
    }

    public function test_the_whatsapp_package_hides_names_when_asked(): void
    {
        $member = User::factory()->anggota()->create([
            'name' => 'Siti Aminah',
            'nim' => '2410112001',
        ]);
        $visible = LecturerQuestion::factory()->selected()->create([
            'user_id' => $member->id,
            'kind' => LecturerQuestionKind::Pertanyaan,
            'topic' => 'Basis Data',
            'body' => 'Apakah UTS open book?',
            'hide_name' => false,
        ]);
        $hidden = LecturerQuestion::factory()->selected()->anonymous()->create([
            'user_id' => $member->id,
            'kind' => LecturerQuestionKind::Keluhan,
            'topic' => 'Lab',
            'body' => 'AC lab mati.',
        ]);

        $package = LecturerQuestion::whatsappPackage([$visible, $hidden]);

        $this->assertStringContainsString('Siti Aminah', $package);
        $this->assertStringContainsString('2410112001', $package);
        $this->assertStringContainsString('Apakah UTS open book?', $package);
        $this->assertStringContainsString('Mahasiswa (nama disembunyikan)', $package);
        $this->assertStringContainsString('AC lab mati.', $package);
    }

    public function test_the_class_rep_can_mark_the_package_as_sent(): void
    {
        $km = User::factory()->km()->create();
        $selected = LecturerQuestion::factory()->selected()->create();
        $fresh = LecturerQuestion::factory()->create();

        $this->actingAs($km)
            ->post(route('lecturer-questions.package'))
            ->assertRedirect(route('lecturer-questions.index'));

        $this->assertSame(LecturerQuestionStatus::Terkirim, $selected->fresh()->status);
        $this->assertSame(LecturerQuestionStatus::Baru, $fresh->fresh()->status);
        $this->assertNotNull($selected->fresh()->sent_at);
    }

    public function test_a_sent_question_cannot_be_curated_again(): void
    {
        $km = User::factory()->km()->create();
        $question = LecturerQuestion::factory()->sent()->create();

        $this->actingAs($km)
            ->patch(route('lecturer-questions.update', $question), [
                'status' => LecturerQuestionStatus::Dipilih->value,
            ])
            ->assertForbidden();
    }
}
